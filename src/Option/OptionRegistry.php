<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Exception\UsageException;
use InvalidArgumentException;

/**
 * Registry for storing and retrieving option definitions.
 */
final class OptionRegistry
{
    /** @var array<string, OptionInterface> */
    private array $options = [];

    /** @var array<string, string> Map of alias to primary name */
    private array $aliases = [];

    /** @var list<Command> */
    private array $commands = [];

    /** @var list<Term> */
    private array $terms = [];

    public function register(OptionInterface $option): void
    {
        $names = $option->getNames();

        if ($names === []) {
            throw new InvalidArgumentException('Option must have at least one name');
        }

        $primary = $names[0];

        foreach ($names as $name) {
            $key = $this->normalizeName($name);

            if (isset($this->options[$key])) {
                throw new InvalidArgumentException('Option name conflict: ' . $name);
            }

            $this->options[$key] = $option;
            $this->aliases[$key] = $primary;
        }

        if ($option instanceof Command) {
            $this->commands[] = $option;
        }

        if ($option instanceof Term) {
            $this->terms[] = $option;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->options[$this->normalizeName($name)]);
    }

    /**
     * @throws UsageException if option not found
     */
    public function get(string $name): OptionInterface
    {
        $key = $this->normalizeName($name);

        if (!isset($this->options[$key])) {
            throw new UsageException('Unknown option: ' . $name);
        }

        return $this->options[$key];
    }

    /**
     * @return list<OptionInterface>
     */
    public function getAll(): array
    {
        $seen = [];
        $result = [];

        foreach ($this->options as $name => $option) {
            $primary = $this->aliases[$name];
            if (!isset($seen[$primary])) {
                $seen[$primary] = true;
                $result[] = $option;
            }
        }

        return $result;
    }

    /**
     * @return list<Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return list<Term>
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    private function normalizeName(string $name): string
    {
        return strtolower($name);
    }
}
