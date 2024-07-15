<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\Utility\Data\ArgumentException;
use DouglasGreen\Utility\Data\ValueException;

/**
 * Define a program with a series of usage options.
 * @see \DouglasGreen\OptParser\Tests\OptParserTest
 */
class OptParser
{
    public const DEBUG_MODE = 1;

    public const SKIP_RESULT_CHECK = 1;

    protected ArgParser $argParser;

    protected OptHandler $optHandler;

    /**
     * @var list<Usage>
     */
    protected array $usages = [];

    /**
     * @var bool All non-help usages have commands. If allCommands is false,
     * that means there are no commands because a program with only one usage
     * that has a command would also be allCommands = true.
     */
    protected bool $allCommands = true;

    public function __construct(
        protected string $name,
        protected string $desc,
        protected int $flags = 0,
    ) {
        $this->optHandler = new OptHandler();

        // Add a default help usage.
        $this->usages[] = new Usage($this->optHandler, ['help']);
    }

    /**
     * A command is a predefined list of command words.
     *
     * @param list<string> $aliases
     *
     * @throws ValueException
     */
    public function addCommand(array $aliases, string $desc): self
    {
        if (count($this->usages) > 1) {
            throw new ValueException('Cannot add commands after usages');
        }

        $this->optHandler->addCommand($aliases, $desc);

        return $this;
    }

    /**
     * A flag has no arguments.
     *
     * @param list<string> $aliases
     *
     * @throws ValueException
     */
    public function addFlag(array $aliases, string $desc): self
    {
        if (count($this->usages) > 1) {
            throw new ValueException('Cannot add flags after usages');
        }

        $this->optHandler->addFlag($aliases, $desc);

        return $this;
    }

    /**
     * A parameter has a required argument.
     *
     * @param list<string> $aliases
     *
     * @throws ValueException
     */
    public function addParam(
        array $aliases,
        string $type,
        string $desc,
        ?callable $callback = null,
    ): self {
        if (count($this->usages) > 1) {
            throw new ValueException('Cannot add params after usages');
        }

        $this->optHandler->addParam($aliases, $type, $desc, $callback);

        return $this;
    }

    /**
     * A term is a positional argument.
     *
     * @throws ValueException
     */
    public function addTerm(
        string $name,
        string $type,
        string $desc,
        ?callable $callback = null,
    ): self {
        if (count($this->usages) > 1) {
            throw new ValueException('Cannot add terms after usages');
        }

        $this->optHandler->addTerm($name, $type, $desc, $callback);

        return $this;
    }

    /**
     * Add a usage to the command by name.
     *
     * @param list<string> $optionNames
     *
     * @throws ValueException
     */
    public function addUsage(string $command, array $optionNames): self
    {
        if ($this->optHandler->getOptionType($command) !== 'command') {
            throw new ValueException('Usage argument not a command: ' . $command);
        }

        // Multiple usages besides help must define a command.
        if (count($this->usages) > 2 && ! $this->allCommands) {
            throw new ValueException('Must define command for each usage');
        }

        $this->usages[] = new Usage($this->optHandler, $optionNames, $command);

        return $this;
    }

    /**
     * Add all options to a single usage except "help".
     */
    public function addUsageAll(): self
    {
        $optionNames = $this->optHandler->getAllNames();

        $hasCommand = false;
        foreach ($optionNames as $optionName) {
            if ($this->optHandler->getOptionType($optionName) === 'command') {
                $hasCommand = true;
                break;
            }
        }

        if (! $hasCommand) {
            $this->allCommands = false;
        }

        $filteredOptions = array_filter(
            $optionNames,
            static fn($option): bool => $option !== 'help',
        );
        $this->usages[] = new Usage($this->optHandler, $filteredOptions);

        return $this;
    }

    public function getArgParser(): ArgParser
    {
        return $this->argParser;
    }

    public function getOptHandler(): OptHandler
    {
        return $this->optHandler;
    }

    /**
     * @return list<Usage>
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * @param ?string[] $args
     */
    public function parse(?array $args = null): OptResult
    {
        global $argv;
        if ($args === null) {
            $args = $argv;
        }

        $this->argParser = new ArgParser($args);
        $unmarkedOptions = $this->argParser->getUnmarkedOptions();
        $markedOptions = $this->argParser->getMarkedOptions();
        $nonOptions = $this->argParser->getNonOptions();

        // Get options except for help.
        $usages = $this->usages;
        array_shift($usages);

        // Check for help option and handle if found.
        $helpOption = $this->optHandler->getOption('help');

        foreach (array_keys($markedOptions) as $name) {
            if ($helpOption->matchName($name)) {
                $this->printHelp();
            }
        }

        $optResult = new OptResult($nonOptions);

        // Report errors from arg parser.
        $errors = $this->argParser->getErrors();
        if ($errors !== []) {
            foreach ($errors as $error) {
                $optResult->addError($error);
            }

            $this->checkResult($optResult);
        }

        // The first unmarked input must be the command name.
        $inputName = null;
        if ($this->allCommands) {
            $inputName = array_shift($unmarkedOptions);
            if ($inputName === null) {
                $optResult->addError('Command name not provided');
                $this->checkResult($optResult);
            } elseif (! $this->optHandler->isCommand($inputName)) {
                $optResult->addError(sprintf('Command name not recognized: "%s"', $inputName));
                $this->checkResult($optResult);
            }
        }

        $matchFound = false;
        foreach ($usages as $usage) {
            // Match commands
            if ($this->allCommands && $inputName !== null) {
                $commandNames = $usage->getOptions('command');

                // There is only one command per usage.
                $commandName = $commandNames[0];
                $command = $this->optHandler->getOption($commandName);

                if ($command->matchName($inputName)) {
                    $optResult->setCommand($commandName, true);
                } else {
                    continue;
                }
            }

            // Match terms
            $termNames = $usage->getOptions('term');
            foreach ($termNames as $termName) {
                $inputValue = array_shift($unmarkedOptions);
                if ($inputValue === null) {
                    $optResult->addError('Missing term: "' . $termName . '"');
                    continue;
                }

                $term = $this->optHandler->getOption($termName);
                try {
                    $matchedValue = $term->matchValue($inputValue);
                    $optResult->setTerm($termName, $matchedValue);
                } catch (ArgumentException $exception) {
                    $optResult->addError(
                        sprintf(
                            'Term "%s" has invalid argument "%s": %s',
                            $termName,
                            $inputValue,
                            $exception->getMessage(),
                        ),
                    );
                }
            }

            // Command and terms are all that is required to match.
            $matchFound = true;

            // Warn about unused unmarked options
            foreach ($unmarkedOptions as $optionName) {
                $optResult->addError('Unused input: "' . $optionName . '"');
            }

            // Match flags
            $flagNames = $usage->getOptions('flag');
            foreach ($flagNames as $flagName) {
                $flag = $this->optHandler->getOption($flagName);
                $found = false;
                $savedName = null;
                $savedValue = null;
                foreach ($markedOptions as $inputName => $inputValue) {
                    if ($flag->matchName($inputName)) {
                        $savedName = $inputName;
                        $savedValue = $inputValue;
                        $found = true;
                        break;
                    }
                }

                $optResult->setFlag($flagName, $found);

                if ($found) {
                    unset($markedOptions[$savedName]);
                    if ($savedValue !== '') {
                        $optResult->addError(
                            sprintf('Argument passed to flag "%s": "%s"', $flagName, $savedValue),
                        );
                    }
                }
            }

            // Match params
            $paramNames = $usage->getOptions('param');
            foreach ($paramNames as $paramName) {
                $param = $this->optHandler->getOption($paramName);
                $found = false;
                $savedName = null;
                $savedValue = null;
                foreach ($markedOptions as $inputName => $inputValue) {
                    if ($param->matchName($inputName)) {
                        $savedName = $inputName;
                        $savedValue = $inputValue;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    unset($markedOptions[$savedName]);
                    if ($savedValue === null) {
                        $optResult->addError('No value passed to param "' . $paramName . '"');
                    } else {
                        try {
                            $matchedValue = $param->matchValue($savedValue);
                            $optResult->setParam($paramName, $matchedValue);
                        } catch (ArgumentException $exception) {
                            $optResult->addError(
                                sprintf(
                                    'Param "%s" has invalid argument "%s": %s',
                                    $paramName,
                                    $savedValue,
                                    $exception->getMessage(),
                                ),
                            );
                        }
                    }
                }
            }

            // Warn about unused marked options
            foreach ($markedOptions as $optionName => $optionValue) {
                $optResult->addError(
                    sprintf('Unused input for "%s": "%s"', $optionName, $optionValue),
                );
            }
        }

        if (! $matchFound) {
            $optResult->addError('Matching usage not found');
        }

        $this->checkResult($optResult);

        return $optResult;
    }

    /**
     * Check for errors then write them and exit.
     */
    protected function checkResult(OptResult $optResult): void
    {
        if ($this->hasFlag(self::SKIP_RESULT_CHECK)) {
            return;
        }

        $errors = $optResult->getErrors();
        if ($errors === []) {
            return;
        }

        $message = 'Errors found in matching usage';
        $command = $optResult->getCommand();
        if ($command !== null) {
            $message .= ' for command "' . $command . '"';
        }

        $message .= ':' . PHP_EOL;
        foreach ($errors as $error) {
            $message .= sprintf('* %s%s', $error, PHP_EOL);
        }

        $message .= PHP_EOL;
        $message .= 'Program terminating. Run again with --help for help.';
        error_log($message);
        if (! $this->hasFlag(self::DEBUG_MODE)) {
            exit();
        }
    }

    protected function hasFlag(int $flag): bool
    {
        return (bool) ($this->flags & $flag);
    }

    /**
     * Print the program help, including:
     * - name
     * - description
     * - usage
     * - options
     */
    protected function printHelp(): void
    {
        echo $this->name . PHP_EOL . PHP_EOL;
        echo wordwrap($this->desc) . PHP_EOL . PHP_EOL;
        echo 'Usage:' . PHP_EOL;
        $programName = $this->argParser->getProgramName();
        foreach ($this->usages as $usage) {
            echo $usage->write($programName);
        }

        echo PHP_EOL;

        echo $this->optHandler->writeOptionBlock();
        if (! $this->hasFlag(self::DEBUG_MODE)) {
            exit();
        }
    }
}
