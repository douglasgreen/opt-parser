<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Defines the contract for all command-line option types.
 *
 * This interface establishes the common behavior for options including flags,
 * parameters, terms, and commands. Each option type implements this interface
 * to provide consistent naming, validation, and value handling capabilities.
 *
 * Implementations:
 * - `Command`: Subcommand selectors (e.g., `git clone`)
 * - `Flag`: Boolean options without values (e.g., `--verbose`)
 * - `Param`: Options requiring typed values (e.g., `--file path.txt`)
 * - `Term`: Positional arguments
 *
 * @package OptParser\Option
 * @api
 * @since 1.0.0
 * @see AbstractOption For base implementation
 * @see Command For subcommand options
 * @see Flag For boolean flag options
 * @see Param For value-accepting parameter options
 * @see Term For positional argument options
 */
interface OptionInterface
{
    /**
     * Returns all registered names for this option.
     *
     * Names include the primary name and any aliases. The first element
     * is always the primary name used for storage and retrieval.
     *
     * @return list<string> List of option names without prefix characters
     */
    public function getNames(): array;

    /**
     * Returns the primary name used for storage and retrieval.
     *
     * The primary name is the first registered name and serves as the
     * canonical identifier for the option in parsed results.
     *
     * @return string The primary option name
     */
    public function getPrimaryName(): string;

    /**
     * Returns the human-readable description for help output.
     *
     * @return string The description text
     */
    public function getDescription(): string;

    /**
     * Determines whether this option accepts a value argument.
     *
     * - `true` for Param and Term (require values)
     * - `false` for Flag and Command (no values)
     *
     * @return bool True if the option requires/expects a value
     */
    public function acceptsValue(): bool;

    /**
     * Determines if this option must be provided by the user.
     *
     * @return bool True if the option is required
     */
    public function isRequired(): bool;

    /**
     * Validates and transforms a string value according to the option's type.
     *
     * The value is validated against the option's registered type and
     * any filter closure is applied. Validation failures result in
     * a ValidationException with a descriptive message.
     *
     * @param string $value The raw string value from command line
     * @param TypeRegistry $registry Type validators for validation
     * @return mixed The validated and possibly transformed value
     * @throws ValidationException When the value fails type validation
     */
    public function validateValue(string $value, TypeRegistry $registry): mixed;

    /**
     * Returns the default value for this option when not provided.
     *
     * The default value type varies by implementation:
     * - `Flag`: Returns `false`
     * - `Param`: Returns configured default or `null`
     * - `Command`: Returns `null`
     * - `Term`: Returns `null`
     *
     * @return mixed The default value (type varies by implementation)
     */
    public function getDefault(): mixed;
}
