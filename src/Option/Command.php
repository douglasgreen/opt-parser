<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Represents a subcommand selector for organizing CLI functionality.
 *
 * Commands enable grouping related operations under distinct subcommands,
 * similar to tools like `git` with `git clone`, `git push`, `git commit`.
 * Each command may have its own set of valid options defined via usage rules.
 *
 * A command is mutually exclusive with other commands; only one command
 * may be active per invocation.
 *
 * @package OptParser\Option
 * @api
 * @since 1.0.0
 * @see AbstractOption For inherited base functionality
 * @see OptionRegistry::getCommands() For retrieving all registered commands
 *
 * @example
 * ```php
 * // Define commands with aliases
 * $status = new Command(['status', 'st'], 'Show repository status');
 * $commit = new Command(['commit', 'ci'], 'Record changes to repository');
 *
 * // Command parsing result:
 * // Input: myapp status --verbose
 * // Result: $input->getCommand() === 'status'
 * ```
 */
final readonly class Command extends AbstractOption
{
    /**
     * Confirms that commands do not accept value arguments.
     *
     * Commands are identified by their name alone and do not take values.
     *
     * @return bool Always returns false
     */
    public function acceptsValue(): bool
    {
        return false;
    }

    /**
     * Returns the command name unchanged.
     *
     * Commands are identified by their string name; no type validation
     * or transformation is performed.
     *
     * @param string $value The command name from command line
     * @param TypeRegistry $registry Ignored for commands
     * @return string The command name as provided
     */
    public function validateValue(string $value, TypeRegistry $registry): string
    {
        return $value;
    }

    /**
     * Returns the default value when no command is provided.
     *
     * A `null` default indicates no command was specified, allowing
     * callers to distinguish between "no command" and a command value.
     *
     * @return string|null Always returns null
     */
    public function getDefault(): ?string
    {
        return null;
    }
}
