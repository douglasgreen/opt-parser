<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Exception\UsageException;
use InvalidArgumentException;

/**
 * Provides centralized storage and retrieval for option definitions.
 *
 * This registry maintains all registered options (commands, flags, params, terms)
 * and provides efficient lookup by name or alias. It enforces name uniqueness
 * and categorizes options by type for specialized retrieval.
 *
 * Name lookups are case-insensitive per POSIX conventions, with all names
 * normalized to lowercase internally.
 *
 * @package OptParser\Option
 *
 * @api
 *
 * @since 1.0.0
 * @see OptionInterface For the contract implemented by registered options
 *
 * @example
 * ```php
 * $registry = new OptionRegistry();
 * $registry->register(new Flag(['v', 'verbose'], 'Enable verbose output'));
 * $registry->register(new Param(['f', 'file'], 'Input file', 'path'));
 *
 * if ($registry->has('verbose')) {
 *     $option = $registry->get('v'); // Alias lookup works
 *     echo $option->getDescription();
 * }
 * ```
 */
final class OptionRegistry
{
    /**
     * All registered options keyed by normalized name.
     *
     * Multiple keys may point to the same OptionInterface instance
     * when aliases are registered.
     *
     * @var array<string, OptionInterface>
     */
    private array $options = [];

    /**
     * Map of normalized names to their primary name.
     *
     * Used to identify the canonical name when iterating unique options.
     *
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * Ordered list of registered commands.
     *
     * Commands are stored in registration order for help display.
     *
     * @var list<Command>
     */
    private array $commands = [];

    /**
     * Ordered list of registered positional terms.
     *
     * Terms are stored in registration order for positional parsing.
     *
     * @var list<Term>
     */
    private array $terms = [];

    /**
     * Registers an option with all its names and aliases.
     *
     * The option is indexed by all its names (normalized to lowercase).
     * Commands and terms are additionally tracked in separate collections
     * for type-specific retrieval.
     *
     * @param OptionInterface $option The option to register
     *
     * @throws InvalidArgumentException When option has no names or name conflicts exist
     *
     * @example
     * ```php
     * $registry->register(new Flag(['v', 'verbose'], 'Verbose mode'));
     * $registry->register(new Command(['status', 'st'], 'Show status'));
     * ```
     */
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

    /**
     * Checks whether an option exists with the given name or alias.
     *
     * Name lookup is case-insensitive.
     *
     * @param string $name The option name or alias to check
     *
     * @return bool True if the option exists
     */
    public function has(string $name): bool
    {
        return isset($this->options[$this->normalizeName($name)]);
    }

    /**
     * Retrieves an option by name or alias.
     *
     * Name lookup is case-insensitive. The returned option may have been
     * registered under a different name (alias lookup).
     *
     * @param string $name The option name or alias to look up
     *
     * @return OptionInterface The matching option
     *
     * @throws UsageException When no option exists with the given name
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
     * Returns all unique registered options.
     *
     * Each option appears only once regardless of how many aliases it has.
     * Options are returned in an undefined order.
     *
     * @return list<OptionInterface> List of unique options
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
     * Returns all registered commands in registration order.
     *
     * @return list<Command> Ordered list of command options
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Returns all registered terms in registration order.
     *
     * Term order is significant for positional argument parsing.
     *
     * @return list<Term> Ordered list of term options
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
     * Normalizes an option name to lowercase for case-insensitive lookup.
     *
     * @param string $name The raw option name
     *
     * @return string The normalized (lowercase) name
     */
    private function normalizeName(string $name): string
    {
        return strtolower($name);
    }
}
