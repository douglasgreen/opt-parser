<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Integer type for whole number validation and conversion.
 *
 * Validates string representations of integers and converts them to native
 * int type. Uses PHP's filter_var with FILTER_VALIDATE_INT which accepts
 * signed integers in the platform's integer range.
 *
 * @package DouglasGreen\OptParser\Type
 *
 * @api
 *
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new IntType();
 *
 * $valid = $type->validate('42');     // Returns 42 (int)
 * $valid = $type->validate('-10');    // Returns -10 (int)
 * $valid = $type->validate('0');      // Returns 0 (int)
 * $invalid = $type->validate('3.14'); // Throws ValidationException
 * $invalid = $type->validate('abc');  // Throws ValidationException
 * ```
 */
final readonly class IntType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'INT'
     */
    public function getName(): string
    {
        return 'INT';
    }

    /**
     * Validates and converts an integer string to native int type.
     *
     * Uses PHP's FILTER_VALIDATE_INT which validates signed integers
     * within the platform's integer range (typically 64-bit on modern systems).
     * Returns the converted integer value, not the original string.
     *
     * @param string $value The string to validate and convert
     *
     * @return int The validated integer value
     *
     * @throws ValidationException When the value is not a valid integer representation
     */
    public function validate(string $value): int
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            throw new ValidationException('Invalid integer: ' . $value);
        }

        return (int) $value;
    }
}
