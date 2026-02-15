<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Date type for ISO 8601 date format validation.
 *
 * Validates date strings in the YYYY-MM-DD format, ensuring both the
 * format compliance and actual date validity (e.g., rejects 2024-02-30).
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new DateType();
 *
 * $valid = $type->validate('2024-01-15');    // Returns '2024-01-15'
 * $invalid = $type->validate('01-15-2024');  // Throws ValidationException (wrong format)
 * $invalid = $type->validate('2024-02-30');  // Throws ValidationException (invalid date)
 * ```
 */
final readonly class DateType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'DATE'
     */
    public function getName(): string
    {
        return 'DATE';
    }

    /**
     * Validates a date string in ISO 8601 format (YYYY-MM-DD).
     *
     * Performs two-stage validation: first checks format compliance with
     * a regular expression, then verifies the date is actually valid
     * using PHP's strtotime function.
     *
     * @param string $value The string to validate as a date
     * @return string The validated date string unchanged
     * @throws ValidationException When the format is incorrect or the date is invalid
     */
    public function validate(string $value): string
    {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid date format (YYYY-MM-DD): ' . $value);
        }

        if (strtotime($value) === false) {
            throw new ValidationException('Invalid date: ' . $value);
        }

        return $value;
    }
}
