<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\OptParser\Exception\OptParserException;
use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Option\OptionRegistry;
use DouglasGreen\OptParser\Option\Param;
use DouglasGreen\OptParser\Option\Term;
use DouglasGreen\OptParser\Parser\SyntaxParser;
use DouglasGreen\OptParser\Parser\Tokenizer;
use DouglasGreen\OptParser\Type\TypeRegistry;
use DouglasGreen\OptParser\Util\OutputHandler;
use DouglasGreen\OptParser\Util\SignalHandler;

/**
 * Main API for POSIX-compliant command-line parsing.
 */
final class OptParser
{
    private OptionRegistry $optionRegistry;
    private TypeRegistry $typeRegistry;
    private Tokenizer $tokenizer;
    private UsageDefinition $usageDefinition;
    private ?SignalHandler $signalHandler;
    private OutputHandler $outputHandler;

    public function __construct(
        private readonly string $programName,
        private readonly string $description,
        private readonly string $version = '1.0.0',
    ) {
        $this->optionRegistry = new OptionRegistry();
        $this->typeRegistry = new TypeRegistry();
        $this->tokenizer = new Tokenizer();
        $this->usageDefinition = new UsageDefinition();
        $this->outputHandler = new OutputHandler();
        $this->signalHandler = new SignalHandler($this->outputHandler);
    }

    /**
     * @param array{0: string, 1?: string} $names
     */
    public function addCommand(array $names, string $description): self
    {
        $this->optionRegistry->register(new Command($names, $description));
        return $this;
    }

    /**
     * @param array{0: string, 1?: string} $names
     */
    public function addParam(
        array $names,
        string $type,
        string $description,
        ?\Closure $filter = null,
        bool $required = false,
        mixed $default = null
    ): self {
        $this->optionRegistry->register(
            new Param($names, $description, $type, $required, $default, $filter)
        );
        return $this;
    }

    /**
     * @param array{0: string, 1?: string} $names
     */
    public function addFlag(array $names, string $description): self
    {
        $this->optionRegistry->register(new Flag($names, $description));
        return $this;
    }

    public function addTerm(
        string $name,
        string $type,
        string $description,
        bool $required = true,
        ?\Closure $filter = null
    ): self {
        $this->optionRegistry->register(
            new Term($name, $description, $type, $required, $filter)
        );
        return $this;
    }

    /**
     * @param array<int, string> $optionNames
     */
    public function addUsage(string $command, array $optionNames): self
    {
        $this->usageDefinition->addUsage($command, $optionNames);
        return $this;
    }

    /**
     * Parse command line arguments.
     *
     * @param array<int, string>|null $argv If null, uses global $argv
     * @throws OptParserException on parsing or validation errors
     */
    public function parse(?array $argv = null): Input
    {
        $this->signalHandler?->register();

        if ($argv === null) {
            global $_SERVER;
            $argv = $_SERVER['argv'] ?? [];
        }

        // Remove script name
        if (isset($argv[0]) && str_contains($argv[0], $this->programName)) {
            array_shift($argv);
        }

        // Handle help/version flags
        if ($this->isHelpRequest($argv)) {
            $this->printHelp();
            exit(0);
        }

        if ($this->isVersionRequest($argv)) {
            $this->printVersion();
            exit(0);
        }

        try {
            $tokens = $this->tokenizer->tokenize($argv);
            $syntaxParser = new SyntaxParser($this->optionRegistry);
            $result = $syntaxParser->parse($tokens);

            $validatedValues = $this->validateValues($result);
            $command = $result->command;

            if ($command !== null) {
                $this->usageDefinition->validate($command, $validatedValues, $this->optionRegistry);
            }

            $nonOptions = $result->mappedOptions['_'] ?? [];

            return new Input($command, $validatedValues, $nonOptions);
        } catch (OptParserException $e) {
            $this->outputHandler->stderr('error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateValues(\DouglasGreen\OptParser\Parser\ParsingResult $result): array
    {
        $validated = [];

        foreach ($result->rawValues as $name => $value) {
            $option = $this->optionRegistry->get($name);
            $validated[$name] = $option->validateValue($value, $this->typeRegistry);
        }

        // Apply defaults for flags/params that weren't provided
        foreach ($this->optionRegistry->getAll() as $option) {
            if (!isset($validated[$option->getPrimaryName()])) {
                $validated[$option->getPrimaryName()] = $option->getDefault();
            }
        }

        return $validated;
    }

    private function isHelpRequest(array $argv): bool
    {
        foreach ($argv as $arg) {
            if ($arg === '--help' || $arg === '-h') {
                return true;
            }
        }
        return false;
    }

    private function isVersionRequest(array $argv): bool
    {
        foreach ($argv as $arg) {
            if ($arg === '--version') {
                return true;
            }
        }
        return false;
    }

    private function printHelp(): void
    {
        $this->outputHandler->stdout("Usage: {$this->programName} [options] [command] [args]");
        $this->outputHandler->stdout('');
        $this->outputHandler->stdout($this->description);
        $this->outputHandler->stdout('');

        $commands = $this->optionRegistry->getCommands();
        if ($commands !== []) {
            $this->outputHandler->stdout('Commands:');
            foreach ($commands as $cmd) {
                $names = implode(', ', $cmd->getNames());
                $this->outputHandler->stdout("  {$names}\t{$cmd->getDescription()}");
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
                $names = implode(', ', array_map(function ($n) use ($opt) {
                    return strlen($n) === 1 ? "-{$n}" : "--{$n}";
                }, $opt->getNames()));

                $type = '';
                if ($opt instanceof Param) {
                    $type = ' <value>';
                }

                $this->outputHandler->stdout("  {$names}{$type}\t{$opt->getDescription()}");
            }
            $this->outputHandler->stdout('');
        }

        $this->outputHandler->stdout('Options:');
        $this->outputHandler->stdout('  -h, --help     Display this help message');
        $this->outputHandler->stdout('  --version      Display version information');
    }

    private function printVersion(): void
    {
        $this->outputHandler->stdout("{$this->programName} {$this->version}");
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
