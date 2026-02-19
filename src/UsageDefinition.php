<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\OptParser\Exception\UsageException;

/**
 * Defines valid option combinations for specific commands.
 *
 * Manages command-specific option restrictions, allowing applications to
 * specify which options are valid for each command. This enables validation
 * of user input against expected usage patterns and provides clear error
 * messages when incompatible option combinations are detected.
 *
 * @package DouglasGreen\OptParser
 *
 * @api
 *
 * @since 1.0.0
 *
 * @example
 * ```php
 * $definition = new UsageDefinition();
 *
 * // Define that the 'export' command only accepts 'format' and 'output' options
 * $definition->addUsage('export', ['format', 'output']);
 *
 * // Define that the 'import' command only accepts 'input' and 'verbose' options
 * $definition->addUsage('import', ['input', 'verbose']);
 *
 * // Validate user input
 * try {
 *     $definition->validate('export', ['format' => 'json', 'verbose' => true]);
 *     // Throws UsageException because 'verbose' is not allowed with 'export'
 * } catch (UsageException $e) {
 *     echo $e->getMessage(); // "Option 'verbose' is not allowed with command 'export'"
 * }
 *
 * // Check individual options
 * if ($definition->isAllowed('export', 'format')) {
 *     // Process format option
 * }
 * ```
 */
final class UsageDefinition
{
    /**
     * Maps command names to their allowed option names.
     *
     * @var array<string, list<string>> Command name => list of allowed option names
     */
    private array $usages = [];

    /**
     * Registers allowed options for a specific command or the main program.
     *
     * Multiple calls for the same command append to the existing allowed
     * options rather than replacing them.
     *
     * @param string|null $command The command name to define usage for, or null for main program
     * @param array<int, string> $optionNames List of option names allowed with this command
     *
     * @example
     * ```php
     * $definition->addUsage('build', ['target', 'verbose', 'output']);
     * $definition->addUsage('build', ['clean']); // Appends 'clean' to existing options
     * $definition->addUsage(null, ['help', 'version']); // Main program options
     * ```
     */
    public function addUsage(?string $command, array $optionNames): void
    {
        $commandKey = $command ?? '';
        if (!isset($this->usages[$commandKey])) {
            $this->usages[$commandKey] = [];
        }

        foreach ($optionNames as $name) {
            $this->usages[$commandKey][] = $name;
        }
    }

    /**
     * Validates that the provided options are allowed for the given command.
     *
     * Checks each option against the registered usage definition for the command.
     * The special '_' key (non-option arguments) and the command name itself
     * are always allowed and skipped during validation.
     *
     * @param string|null $command The command name to validate against, or null for main program
     * @param array<string, mixed> $providedOptions Options provided by the user
     *
     * @throws UsageException When an option is not allowed with the specified command
     */
    public function validate(?string $command, array $providedOptions): void
    {
        $commandKey = $command ?? '';
        if (!isset($this->usages[$commandKey])) {
            return; // No usage defined, allow anything
        }

        $allowed = $this->usages[$commandKey];

        foreach (array_keys($providedOptions) as $name) {
            if ($name === '_') {
                continue;
            }

            if ($command !== null && $name === $command) {
                continue;
            }

            if (!in_array($name, $allowed, true)) {
                $commandMsg = $command === null ? 'the main program' : sprintf("command '%s'", $command);
                throw new UsageException(
                    sprintf("Option '%s' is not allowed with %s", $name, $commandMsg),
                );
            }
        }
    }

    /**
     * Checks whether a specific option is allowed for a command.
     *
     * Returns true if no usage restrictions are defined for the command,
     * or if the option is in the allowed list. The command name itself
     * is always considered allowed.
     *
     * @param string|null $command The command name to check against, or null for main program
     * @param string $optionName The option name to validate
     *
     * @return bool True if the option is allowed with the command
     */
    public function isAllowed(?string $command, string $optionName): bool
    {
        $commandKey = $command ?? '';
        if (!isset($this->usages[$commandKey])) {
            return true; // No restriction defined
        }

        // The command name itself is always allowed
        if ($command !== null && $optionName === $command) {
            return true;
        }

        return in_array($optionName, $this->usages[$commandKey], true);
    }
}
