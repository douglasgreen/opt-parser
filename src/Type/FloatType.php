<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Float type for decimal number validation and conversion.
 *
 * Validates string representations of floating-point numbers and converts
 * them to native float type. Uses PHP's filter_var with FILTER_VALIDATE_FLOAT
 * which accepts various decimal formats including scientific notation.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new FloatType();
 *
 * $valid = $type->validate('3.14');      // Returns 3.14 (float)
 * $valid = $type->validate('-0.5');      // Returns -0.5 (float)
 * $valid = $type->validate('1.5e3');     // Returns 1500.0 (float)
 * $valid = $type->validate('42');        // Returns 42.0 (float)
 * $invalid = $type->validate('abc');     // Throws ValidationException
 * $invalid = $type->validate('3.14.15'); // Throws ValidationException
 * ```
 */
final readonly class FloatType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'FLOAT'
     */
    public function getName(): string
    {
        return 'FLOAT';
    }

    /**
     * Validates and converts a float string to native float type.
     *
     * Uses PHP's FILTER_VALIDATE_FLOAT which accepts decimal numbers,
     * negative values, and scientific notation. Returns the converted
     * float value, not the original string.
     *
     * @param string $value The string to validate and convert
     * @return float The validated floating-point value
     * @throws ValidationException When the value is not a valid float representation
     */
    public function validate(string $value): float
    {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            throw new ValidationException('Invalid float: ' . $value);
        }

        return (float) $value;
    }
}
