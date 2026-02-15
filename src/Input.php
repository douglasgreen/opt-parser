<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

/**
 * Provides an immutable container for parsed command-line input.
 *
 * This class encapsulates the results of argument parsing, including the
 * resolved command, validated options, and any non-option arguments.
 * All values are read-only after construction.
 *
 * @package OptParser
 *
 * @api
 *
 * @since 1.0.0
 * @see OptParser For the parser that produces Input instances
 *
 * @example
 * ```php
 * $input = $parser->parse();
 *
 * if ($input->has('verbose') && $input->get('verbose')) {
 *     echo "Verbose mode enabled\n";
 * }
 *
 * $command = $input->getCommand();
 * if ($command !== null) {
 *     echo "Running command: $command\n";
 * }
 * ```
 */
final readonly class Input
{
    /**
     * Constructs an Input instance with parsed values.
     *
     * @param string|null $command The resolved command name, or null if none
     * @param array<string, mixed> $options Validated option values keyed by name
     * @param array<int, string> $nonOptions Positional arguments not consumed as options
     */
    public function __construct(
        private ?string $command,
        private array $options,
        private array $nonOptions,
    ) {}

    /**
     * Returns the resolved command name.
     *
     * @return string|null The command name if one was provided, null otherwise
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Retrieves an option value by name.
     *
     * @param string $name The option name (primary name as registered)
     *
     * @return mixed The option value, or null if not present
     */
    public function get(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Checks whether an option was provided.
     *
     * This returns true even if the option value is null, distinguishing
     * between "not provided" and "provided with null value".
     *
     * @param string $name The option name to check
     *
     * @return bool True if the option exists in the parsed input
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Returns all positional (non-option) arguments.
     *
     * @return array<int, string> Ordered list of positional arguments
     */
    public function getNonoptions(): array
    {
        return $this->nonOptions;
    }

    /**
     * Returns all parsed options as an associative array.
     *
     * @return array<string, mixed> All option values keyed by name
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
