<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Option\Option;
use DouglasGreen\OptParser\Option\Param;
use DouglasGreen\OptParser\Option\Term;
use DouglasGreen\Utility\Data\ValueException;

/**
 * Define and print options.
 * @see \DouglasGreen\OptParser\Tests\OptHandlerTest
 */
class OptHandler
{
    /**
     * @var array<string, bool>
     */
    protected array $allAliases = [];

    /**
     * @var array<string, Command>
     */
    protected array $commands = [];

    /**
     * @var array<string, Flag>
     */
    protected array $flags = [];

    /**
     * @var array<string, Param>
     */
    protected array $params = [];

    /**
     * @var array<string, Term>
     */
    protected array $terms = [];

    public function __construct()
    {
        $this->addFlag(['h', 'help'], 'Display program help');
    }

    /**
     * A command is a predefined list of command words.
     *
     * @param list<string> $aliases
     */
    public function addCommand(array $aliases, string $desc): self
    {
        [$name, $others] = $this->pickName($aliases);
        $this->commands[$name] = new Command($name, $desc, $others);

        return $this;
    }

    /**
     * A flag has no arguments.
     *
     * @param list<string> $aliases
     */
    public function addFlag(array $aliases, string $desc): self
    {
        [$name, $others] = $this->pickName($aliases);
        $this->flags[$name] = new Flag($name, $desc, $others);

        return $this;
    }

    /**
     * A parameter has a required argument.
     *
     * @param list<string> $aliases
     */
    public function addParam(
        array $aliases,
        string $type,
        string $desc,
        ?callable $callback = null,
    ): self {
        [$name, $others] = $this->pickName($aliases);
        $this->params[$name] = new Param($name, $desc, $others, $type, $callback);

        return $this;
    }

    /**
     * A term is a positional argument.
     */
    public function addTerm(
        string $name,
        string $type,
        string $desc,
        ?callable $callback = null,
    ): self {
        $this->checkAlias($name);
        $this->terms[$name] = new Term($name, $desc, $type, $callback);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllNames(): array
    {
        return array_merge(
            array_keys($this->commands),
            array_keys($this->terms),
            array_keys($this->params),
            array_keys($this->flags),
        );
    }

    /**
     * Get an option by name.
     *
     * @throws ValueException
     */
    public function getOption(string $name): Option
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        if (isset($this->terms[$name])) {
            return $this->terms[$name];
        }

        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        if (isset($this->flags[$name])) {
            return $this->flags[$name];
        }

        throw new ValueException('Name not found');
    }

    /**
     * Get the type of an option.
     *
     * @throws ValueException
     */
    public function getOptionType(string $name): string
    {
        if (isset($this->commands[$name])) {
            return 'command';
        }

        if (isset($this->terms[$name])) {
            return 'term';
        }

        if (isset($this->params[$name])) {
            return 'param';
        }

        if (isset($this->flags[$name])) {
            return 'flag';
        }

        throw new ValueException('Name not found');
    }

    /**
     * Check if it has the option type.
     *
     * @throws ValueException
     */
    public function hasOptionType(string $type): bool
    {
        return match ($type) {
            'command' => $this->commands !== [],
            'term' => $this->terms !== [],
            'param' => $this->params !== [],
            'flag' => $this->flags !== [],
            default => throw new ValueException('Type not found'),
        };
    }

    /**
     * Check if the name is a command.
     */
    public function isCommand(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Write an option by name.
     */
    public function writeOption(string $name): string
    {
        $option = $this->getOption($name);

        return $option->write();
    }

    /**
     * Get a string representation of options, arguments, and descriptions.
     */
    public function writeOptionBlock(): string
    {
        $output = '';

        if ($this->commands !== []) {
            $output .= $this->writeCommandBlock();
        }

        if ($this->terms !== []) {
            $output .= $this->writeTermBlock();
        }

        if ($this->params !== []) {
            $output .= $this->writeParamBlock();
        }

        if ($this->flags !== []) {
            $output .= $this->writeFlagBlock();
        }

        return $output;
    }

    /**
     * Check alias for uniqueness.
     *
     * @throws ValueException
     */
    protected function checkAlias(string $alias): void
    {
        if (isset($this->allAliases[$alias])) {
            throw new ValueException('Duplicate alias: ' . $alias);
        }

        $this->allAliases[$alias] = true;
    }

    /**
     * @param list<string> $aliases
     *
     * @return array{string, list<string>}
     *
     * @throws ValueException
     */
    protected function pickName(array $aliases): array
    {
        $name = null;
        $others = [];
        foreach ($aliases as $alias) {
            $this->checkAlias($alias);
            if ($name === null && strlen($alias) > 1) {
                $name = $alias;
            } else {
                $others[] = $alias;
            }
        }

        if ($name === null) {
            throw new ValueException('Missing required long name');
        }

        return [$name, $others];
    }

    protected function writeCommandBlock(): string
    {
        $output = 'Commands:' . PHP_EOL;
        foreach ($this->commands as $name => $command) {
            $output .= '  ' . $name;
            $aliases = $command->getAliases();
            if ($aliases) {
                foreach ($aliases as $alias) {
                    $output .= ' | ' . $alias;
                }
            }

            $output .= '  ' . $command->getDesc() . PHP_EOL;
        }

        return $output . PHP_EOL;
    }

    protected function writeFlagBlock(): string
    {
        $output = 'Flags:' . PHP_EOL;
        foreach ($this->flags as $name => $flag) {
            $output .= '  ';
            $output .= $flag->hyphenate($name);
            $aliases = $flag->getAliases();
            if ($aliases) {
                foreach ($aliases as $alias) {
                    $output .= ' | ';
                    $output .= $flag->hyphenate($alias);
                }
            }

            $output .= '  ' . $flag->getDesc() . PHP_EOL;
        }

        return $output . PHP_EOL;
    }

    protected function writeParamBlock(): string
    {
        $output = 'Parameters:' . PHP_EOL;
        foreach ($this->params as $name => $param) {
            $output .= '  ';
            $output .= $param->hyphenate($name);
            $aliases = $param->getAliases();
            if ($aliases) {
                foreach ($aliases as $alias) {
                    $output .= ' | ';
                    $output .= $param->hyphenate($alias);
                }
            }

            $output .= ' = ' . $param->getArgType();
            $output .= '  ' . $param->getDesc() . PHP_EOL;
        }

        return $output . PHP_EOL;
    }

    protected function writeTermBlock(): string
    {
        $output = 'Terms:' . PHP_EOL;
        foreach ($this->terms as $name => $term) {
            $output .=
                sprintf('  %s: ', $name) . $term->getArgType() . '  ' . $term->getDesc() . PHP_EOL;
        }

        return $output . PHP_EOL;
    }
}
