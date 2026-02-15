<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Provides base functionality for all option types.
 *
 * This abstract class implements common option behavior including name management,
 * description storage, and default implementations for optional behavior.
 * Concrete classes define value acceptance and validation logic.
 *
 * @package OptParser\Option
 * @api
 * @since 1.0.0
 * @see OptionInterface For the contract this class implements
 * @see Command For subcommand options
 * @see Flag For boolean flag options
 * @see Param For value-accepting parameter options
 * @see Term For positional argument options
 */
abstract readonly class AbstractOption implements OptionInterface
{
    /**
     * List of option names (short and/or long forms).
     *
     * The first element is considered the primary name used for storage
     * and retrieval. Names are stored without prefix characters.
     *
     * @var list<string>
     */
    protected array $names;

    /**
     * Constructs an option with names and description.
     *
     * @param array<string> $names Option names (primary first, aliases follow)
     * @param string $description Human-readable description for help output
     */
    public function __construct(
        array $names,
        protected string $description,
    ) {
        $this->names = array_values($names);
    }

    /**
     * Returns all registered names for this option.
     *
     * @return list<string> List of option names without prefix characters
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * Returns the primary name used for storage and retrieval.
     *
     * @return string The first registered name, or empty string if none
     */
    public function getPrimaryName(): string
    {
        return $this->names[0] ?? '';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string The description for help output
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Determines if this option must be provided.
     *
     * Default implementation returns false. Subclasses may override.
     *
     * @return bool True if required, false otherwise
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * Returns the default value for this option.
     *
     * Default implementation returns null. Subclasses may override.
     *
     * @return mixed The default value (typically null for optional options)
     */
    public function getDefault(): mixed
    {
        return null;
    }

    /**
     * Determines whether this option accepts a value argument.
     *
     * @return bool True if the option requires/expects a value
     */
    abstract public function acceptsValue(): bool;

    /**
     * Validates and transforms a string value according to the option's type.
     *
     * @param string $value The raw string value from command line
     * @param TypeRegistry $registry Type validators for validation
     * @return mixed The validated and possibly transformed value
     * @throws ValidationException When the value fails type validation
     */
    abstract public function validateValue(string $value, TypeRegistry $registry): mixed;
}
