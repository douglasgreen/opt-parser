<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Fixed-point number type for financial and precision-critical values.
 *
 * Validates strings representing fixed-point decimal numbers, supporting
 * optional comma separators for thousands. Returns the original string
 * representation to preserve formatting and precision.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new FixedType();
 *
 * $valid = $type->validate('1234.56');   // Returns '1234.56'
 * $valid = $type->validate('1,234.56');  // Returns '1,234.56'
 * $invalid = $type->validate('abc');     // Throws ValidationException
 * ```
 */
final readonly class FixedType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'FIXED'
     */
    public function getName(): string
    {
        return 'FIXED';
    }

    /**
     * Validates a fixed-point number string.
     *
     * Accepts decimal numbers with optional comma separators for thousands.
     * The original string is returned to preserve the exact representation.
     *
     * @param string $value The string to validate as a fixed-point number
     * @return string The validated value unchanged
     * @throws ValidationException When the value is not a valid fixed-point number
     */
    public function validate(string $value): string
    {
        // Remove commas for validation
        $normalized = str_replace(',', '', $value);

        if (!is_numeric($normalized)) {
            throw new ValidationException('Invalid fixed-point number: ' . $value);
        }

        return $value;
    }
}
