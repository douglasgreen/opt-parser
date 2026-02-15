<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use Closure;
use DouglasGreen\OptParser\Exception\OptParserException;
use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Option\OptionRegistry;
use DouglasGreen\OptParser\Option\Param;
use DouglasGreen\OptParser\Option\Term;
use DouglasGreen\OptParser\Parser\ParsingResult;
use DouglasGreen\OptParser\Parser\SyntaxParser;
use DouglasGreen\OptParser\Parser\Tokenizer;
use DouglasGreen\OptParser\Type\TypeRegistry;
use DouglasGreen\OptParser\Util\OutputHandler;
use DouglasGreen\OptParser\Util\SignalHandler;

/**
 * Provides the primary API for POSIX-compliant command-line argument parsing.
 *
 * This class serves as the main entry point for defining and parsing CLI options,
 * commands, flags, and arguments. It follows POSIX conventions while providing
 * a fluent interface for configuration and comprehensive validation capabilities.
 *
 * ## Usage
 * Instantiate with program metadata, then chain option definitions before parsing:
 *
 * @package OptParser
 * @api
 * @since 1.0.0
 * @see Input For the parsed result container
 * @see UsageException For CLI syntax errors
 * @see ValidationException For type validation errors
 *
 * @example
 * ```php
 * $parser = new OptParser('myapp', 'A sample application', '1.0.0');
 * $parser->addCommand(['status'], 'Show status')
 *        ->addFlag(['v', 'verbose'], 'Enable verbose output')
 *        ->addParam(['f', 'file'], 'string', 'Input file', required: true);
 *
 * try {
 *     $input = $parser->parse();
 *     if ($input->get('verbose')) {
 *         echo "Verbose mode enabled\n";
 *     }
 * } catch (OptParserException $e) {
 *     exit($e->getExitCode());
 * }
 * ```
 */
final readonly class OptParser
{
    /**
     * Registry containing all defined options, commands, and flags.
     *
     * @var OptionRegistry
     */
    private OptionRegistry $optionRegistry;

    /**
     * Registry for type validators used during value validation.
     *
     * @var TypeRegistry
     */
    private TypeRegistry $typeRegistry;

    /**
     * Tokenizer for converting raw argv into parseable tokens.
     *
     * @var Tokenizer
     */
    private Tokenizer $tokenizer;

    /**
     * Definition of per-command usage constraints.
     *
     * @var UsageDefinition
     */
    private UsageDefinition $usageDefinition;

    /**
     * Handler for POSIX signal management (SIGINT, SIGTERM).
     *
     * @var SignalHandler|null
     */
    private ?SignalHandler $signalHandler;

    /**
     * Handler for formatted stdout/stderr output.
     *
     * @var OutputHandler
     */
    private OutputHandler $outputHandler;

    /**
     * Container for optional help sections (examples, exit codes, etc.).
     *
     * @var HelpSections
     */
    private HelpSections $helpSections;

    /**
     * Constructs a new OptParser with program metadata.
     *
     * @param string $programName The executable name displayed in help output
     * @param string $description A brief description of the program's purpose
     * @param string $version Semantic version string (default: '1.0.0')
     */
    public function __construct(
        private string $programName,
        private string $description,
        private string $version = '1.0.0',
    ) {
        $this->optionRegistry = new OptionRegistry();
        $this->typeRegistry = new TypeRegistry();
        $this->tokenizer = new Tokenizer();
        $this->usageDefinition = new UsageDefinition();
        $this->outputHandler = new OutputHandler();
        $this->signalHandler = new SignalHandler($this->outputHandler);
        $this->helpSections = new HelpSections();
    }

    /**
     * Registers a subcommand with the parser.
     *
     * Commands allow grouping related functionality under distinct subcommands
     * (e.g., `git status`, `git commit`). Each command has its own option set
     * defined via addUsage().
     *
     * @param array{0: string, 1?: string} $names Command names (primary name required, alias optional)
     * @param string $description Human-readable description for help output
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addCommand(['status', 'st'], 'Show repository status');
     * ```
     */
    public function addCommand(array $names, string $description): self
    {
        $this->optionRegistry->register(new Command($names, $description));
        return $this;
    }

    /**
     * Registers a parameter option that accepts a typed value.
     *
     * Parameters are options that require an argument value (e.g., `--file path.txt`).
     * The value is validated against the specified type and optional filter closure.
     *
     * @param array{0: string, 1?: string} $names Option names (short and/or long form)
     * @param string $type Value type (e.g., 'string', 'int', 'float', 'path')
     * @param string $description Human-readable description for help output
     * @param Closure(mixed): mixed|null $filter Optional transformation/filter closure
     * @param bool $required Whether the option must be provided (default: false)
     * @param mixed $default Default value if option not provided (default: null)
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addParam(['f', 'file'], 'path', 'Input file', required: true)
     *        ->addParam(['n', 'count'], 'int', 'Number of items', default: 10);
     * ```
     */
    public function addParam(
        array $names,
        string $type,
        string $description,
        ?Closure $filter = null,
        bool $required = false,
        mixed $default = null,
    ): self {
        $this->optionRegistry->register(
            new Param($names, $description, $type, $required, $default, $filter),
        );
        return $this;
    }

    /**
     * Registers a boolean flag option.
     *
     * Flags are presence-based options that resolve to true when provided
     * and false when omitted (e.g., `--verbose`, `-v`). Multiple flag names
     * can share the same option via short and long forms.
     *
     * @param array{0: string, 1?: string} $names Option names (short and/or long form)
     * @param string $description Human-readable description for help output
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addFlag(['v', 'verbose'], 'Enable verbose output')
     *        ->addFlag(['q', 'quiet'], 'Suppress all output');
     * ```
     */
    public function addFlag(array $names, string $description): self
    {
        $this->optionRegistry->register(new Flag($names, $description));
        return $this;
    }

    /**
     * Registers a positional term (argument).
     *
     * Terms are positional arguments that appear after options and commands.
     * They are parsed in order of definition and validated against the specified type.
     *
     * @param string $name Argument identifier used to retrieve the value
     * @param string $type Value type (e.g., 'string', 'int', 'path')
     * @param string $description Human-readable description for help output
     * @param bool $required Whether the argument must be provided (default: true)
     * @param Closure(mixed): mixed|null $filter Optional transformation/filter closure
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addTerm('source', 'path', 'Source file', required: true)
     *        ->addTerm('destination', 'path', 'Destination file', required: false);
     * ```
     */
    public function addTerm(
        string $name,
        string $type,
        string $description,
        bool $required = true,
        ?Closure $filter = null,
    ): self {
        $this->optionRegistry->register(
            new Term($name, $description, $type, $required, $filter),
        );
        return $this;
    }

    /**
     * Defines which options are valid for a specific subcommand.
     *
     * This restricts the valid option set for a command, enabling different
     * commands to accept different options. Options not listed will cause
     * a usage error when used with that command.
     *
     * @param string $command The command name to configure
     * @param array<int, string> $optionNames List of valid option names for this command
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addUsage('commit', ['message', 'file', 'verbose']);
     * $parser->addUsage('push', ['remote', 'branch', 'force']);
     * ```
     */
    public function addUsage(string $command, array $optionNames): self
    {
        $this->usageDefinition->addUsage($command, $optionNames);
        return $this;
    }

    /**
     * Adds a usage example line to the help output.
     *
     * Examples appear in the 'Examples:' section of the help message,
     * providing users with common invocation patterns.
     *
     * @param string $line A single example command line (without shell prompt)
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addExample('myapp status --verbose')
     *        ->addExample('myapp commit -m "Initial commit"');
     * ```
     */
    public function addExample(string $line): self
    {
        $this->helpSections->examples[] = $line;
        return $this;
    }

    /**
     * Documents a process exit code in the help output.
     *
     * Exit codes appear in the 'Exit Codes:' section, helping users
     * understand the meaning of different return values for scripting.
     *
     * @param string $code The numeric exit code (e.g., '0', '1', '2')
     * @param string $description Human-readable explanation of when this code is returned
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addExitCode('0', 'Success')
     *        ->addExitCode('1', 'General error')
     *        ->addExitCode('2', 'Usage error');
     * ```
     */
    public function addExitCode(string $code, string $description): self
    {
        $this->helpSections->exitCodes[$code] = $description;
        return $this;
    }

    /**
     * Documents an environment variable in the help output.
     *
     * Environment variables appear in the 'Environment:' section,
     * informing users of runtime configuration options.
     *
     * @param string $name The environment variable name (e.g., 'DEBUG', 'LOG_LEVEL')
     * @param string $description Human-readable explanation of the variable's effect
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addEnvironment('DEBUG', 'Enable debug logging')
     *        ->addEnvironment('LOG_LEVEL', 'Set log verbosity (0-3)');
     * ```
     */
    public function addEnvironment(string $name, string $description): self
    {
        $this->helpSections->environment[$name] = $description;
        return $this;
    }

    /**
     * Adds a documentation URL to the help output.
     *
     * Documentation URLs appear in the 'Documentation:' section,
     * providing links to extended help resources.
     *
     * @param string $url Fully qualified URL to documentation
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $parser->addDocumentation('https://example.com/docs/myapp')
     *        ->addDocumentation('https://wiki.example.com/myapp');
     * ```
     */
    public function addDocumentation(string $url): self
    {
        $this->helpSections->documentation[] = $url;
        return $this;
    }

    /**
     * Parses command line arguments and returns the validated input.
     *
     * This method tokenizes, parses, validates, and returns the input. It handles
     * automatic help and version display before parsing. Signal handlers are
     * registered for graceful interruption handling.
     *
     * Preconditions:
     * - Options must be registered before calling parse()
     * - If $argv is null, global $_SERVER['argv'] will be used
     *
     * Postconditions:
     * - Returns Input with validated values and applied defaults
     * - Exits with code 0 if --help or --version was requested
     *
     * @param array<int, string>|null $argv Argument array (null uses global $argv)
     * @return Input Validated input container with options and command
     * @throws OptParserException On parsing or validation errors
     *
     * @example
     * ```php
     * $input = $parser->parse();
     * // Or with explicit arguments:
     * $input = $parser->parse(['--verbose', 'status']);
     * ```
     */
    public function parse(?array $argv = null): Input
    {
        $this->signalHandler?->register();

        $scriptName = $this->programName;

        if ($argv === null) {
            global $_SERVER;
            $argv = $_SERVER['argv'] ?? [];
            // Remove script name which is always index 0 in standard CLI execution
            if (isset($argv[0])) {
                $scriptName = $argv[0];
                array_shift($argv);
            }
        }

        // Handle help/version flags
        if ($this->isHelpRequest($argv)) {
            $this->printHelp($scriptName);
            exit(0);
        }

        if ($this->isVersionRequest($argv)) {
            $this->printVersion($scriptName);
            exit(0);
        }

        try {
            $tokens = $this->tokenizer->tokenize($argv);
            $syntaxParser = new SyntaxParser($this->optionRegistry);
            $result = $syntaxParser->parse($tokens);

            $validatedValues = $this->validateValues($result);
            $command = $result->command;

            if ($command !== null) {
                // Validate ONLY options provided by user against usage rules
                $this->usageDefinition->validate($command, $result->mappedOptions);
            }

            $nonOptions = $result->mappedOptions['_'] ?? [];

            return new Input($command, $validatedValues, $nonOptions);
        } catch (OptParserException $optParserException) {
            $this->outputHandler->stderr('error: ' . $optParserException->getMessage());
            throw $optParserException;
        }
    }

    /**
     * Returns the parser's configured version string.
     *
     * @return string The semantic version string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Validates raw parsed values against registered option types.
     *
     * Applies type validation, filters, default values, and checks required options.
     * Commands may have context-specific requirement rules applied.
     *
     * @param ParsingResult $result Raw parsing result from syntax parser
     * @return array<string, mixed> Validated values with defaults applied
     * @throws UsageException When a required option is missing
     */
    private function validateValues(ParsingResult $result): array
    {
        $validated = [];

        // 1. Validate provided values
        foreach ($result->rawValues as $name => $value) {
            $option = $this->optionRegistry->get($name);
            $validated[$name] = $option->validateValue($value, $this->typeRegistry);
        }

        // 2. Apply defaults and check requirements
        foreach ($this->optionRegistry->getAll() as $option) {
            $name = $option->getPrimaryName();

            if (isset($validated[$name])) {
                continue;
            }

            // Check if option is required
            if ($option->isRequired()) {
                $checkRequired = true;

                // Context check
                if ($result->command !== null) {
                    $checkRequired = $this->usageDefinition->isAllowed($result->command, $name);
                } elseif ($this->optionRegistry->getCommands() !== []) {
                    // Global context with subcommands defined: strict requirements disabled
                    // to allow user script to handle "missing command" error.
                    $checkRequired = false;
                }

                if ($checkRequired) {
                    throw new UsageException(sprintf("Option '%s' is required", $name));
                }
            }

            $validated[$name] = $option->getDefault();
        }

        return $validated;
    }

    /**
     * Determines if the help flag was requested.
     *
     * @param array<int, string> $argv Argument array to check
     * @return bool True if --help or -h is present
     */
    private function isHelpRequest(array $argv): bool
    {
        foreach ($argv as $arg) {
            if ($arg === '--help' || $arg === '-h') {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the version flag was requested.
     *
     * @param array<int, string> $argv Argument array to check
     * @return bool True if --version is present
     */
    private function isVersionRequest(array $argv): bool
    {
        return in_array('--version', $argv, true);
    }

    /**
     * Outputs the formatted help message to stdout.
     *
     * Displays usage, description, commands, options, and any configured
     * additional sections (examples, exit codes, environment, documentation).
     *
     * @param string $scriptName The script name for usage line display
     * @return void
     */
    private function printHelp(string $scriptName): void
    {
        $this->outputHandler->stdout(sprintf('Usage: %s [options] [command] [args]', basename($scriptName)));
        $this->outputHandler->stdout('');
        $this->outputHandler->stdout($this->description);
        $this->outputHandler->stdout('');

        $commands = $this->optionRegistry->getCommands();
        if ($commands !== []) {
            $this->outputHandler->stdout('Commands:');
            foreach ($commands as $cmd) {
                $names = implode(', ', $cmd->getNames());
                $this->outputHandler->stdout(sprintf('  %s	%s', $names, $cmd->getDescription()));
            }

            $this->outputHandler->stdout('');
        }

        $options = $this->optionRegistry->getAll();
        if ($options !== []) {
            $this->outputHandler->stdout('Options:');
            foreach ($options as $opt) {
                if ($opt instanceof Command) {
                    continue;
                }

                $names = implode(', ', array_map(fn (string $n): string => strlen($n) === 1 ? '-' . $n : '--' . $n, $opt->getNames()));

                $type = '';
                if ($opt instanceof Param) {
                    $type = ' <value>';
                }

                $this->outputHandler->stdout(sprintf('  %s%s	%s', $names, $type, $opt->getDescription()));
            }

            $this->outputHandler->stdout('');
        }

        $this->outputHandler->stdout('Options:');
        $this->outputHandler->stdout('  -h, --help     Display this help message');
        $this->outputHandler->stdout('  --version      Display version information');

        $this->printArraySection('Examples', $this->helpSections->examples);
        $this->printMapSection('Exit Codes', $this->helpSections->exitCodes);
        $this->printMapSection('Environment', $this->helpSections->environment);
        $this->printArraySection('Documentation', $this->helpSections->documentation);
    }

    /**
     * Outputs a titled section with a list of lines.
     *
     * @param string $title Section title (will have ':' appended)
     * @param array<int, string> $lines Lines to display under the title
     * @return void
     */
    private function printArraySection(string $title, array $lines): void
    {
        if ($lines === []) {
            return;
        }

        $this->outputHandler->stdout('');
        $this->outputHandler->stdout($title . ':');
        foreach ($lines as $line) {
            $this->outputHandler->stdout('  ' . $line);
        }
    }

    /**
     * Outputs a titled section with key-value pairs.
     *
     * @param string $title Section title (will have ':' appended)
     * @param array<string, string> $items Key-value pairs to display
     * @return void
     */
    private function printMapSection(string $title, array $items): void
    {
        if ($items === []) {
            return;
        }

        $this->outputHandler->stdout('');
        $this->outputHandler->stdout($title . ':');
        foreach ($items as $name => $description) {
            $this->outputHandler->stdout(sprintf('  %s   %s', $name, $description));
        }
    }

    /**
     * Outputs the version string to stdout.
     *
     * @param string $scriptName The script name to display
     * @return void
     */
    private function printVersion(string $scriptName): void
    {
        $this->outputHandler->stdout(sprintf('%s %s', $scriptName, $this->version));
    }
}
