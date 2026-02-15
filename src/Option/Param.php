<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use Closure;
use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;
use Exception;
use Override;

/**
 * Represents a command-line option that requires a typed value argument.
 *
 * Parameters are options that accept and validate a value, such as `--file path.txt`
 * or `-o output.log`. The value is validated against a registered type and may
 * be transformed through an optional filter closure.
 *
 * Supports short and long option forms:
 * - Short: `-f value` or `-f=value`
 * - Long: `--file value` or `--file=value`
 *
 * @package OptParser\Option
 * @api
 * @since 1.0.0
 * @see AbstractOption For inherited base functionality
 * @see TypeRegistry For available type validators
 *
 * @example
 * ```php
 * // Required file parameter with short and long forms
 * $param = new Param(
 *     ['f', 'file'],
 *     'Input file path',
 *     'path',
 *     required: true
 * );
 *
 * // Optional count parameter with default and filter
 * $param = new Param(
 *     ['n', 'count'],
 *     'Number of items',
 *     'int',
 *     required: false,
 *     default: 10,
 *     filter: fn(int $n): int => max(1, min($n, 100))
 * );
 * ```
 */
final readonly class Param extends AbstractOption
{
    /**
     * Constructs a parameter option with type validation.
     *
     * @param array<string> $names Option names (primary first, aliases follow)
     * @param string $description Human-readable description for help output
     * @param string $typeName Registered type name for validation (e.g., 'int', 'path')
     * @param bool $required Whether the option must be provided (default: false)
     * @param mixed $default Default value when option not provided (default: null)
     * @param Closure(mixed): mixed|null $filter Optional transformation/filter closure
     */
    public function __construct(
        array $names,
        string $description,
        private string $typeName,
        private bool $required = false,
        private mixed $default = null,
        private ?Closure $filter = null,
    ) {
        parent::__construct($names, $description);
    }

    /**
     * Confirms that parameters accept value arguments.
     *
     * @return bool Always returns true
     */
    public function acceptsValue(): bool
    {
        return true;
    }

    /**
     * Returns whether this parameter is required.
     *
     * @return bool True if required, false otherwise
     */
    #[Override]
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Returns the configured default value.
     *
     * @return mixed The default value (may be null if not configured)
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Validates a string value against the registered type and applies optional filter.
     *
     * The validation process:
     * 1. Retrieves the type validator from the registry
     * 2. Validates and converts the string to the typed value
     * 3. Applies the filter closure if configured
     *
     * @param string $value The raw string value from command line
     * @param TypeRegistry $registry Type validators for validation
     * @return mixed The validated and possibly transformed value
     * @throws ValidationException When type validation fails or filter throws
     */
    public function validateValue(string $value, TypeRegistry $registry): mixed
    {
        $type = $registry->get($this->typeName);
        $typedValue = $type->validate($value);

        if ($this->filter instanceof Closure) {
            try {
                $typedValue = ($this->filter)($typedValue);
            } catch (Exception $e) {
                throw new ValidationException(
                    sprintf("Filter rejected value for '%s': %s", $this->getPrimaryName(), $e->getMessage()),
                );
            }
        }

        return $typedValue;
    }
}
