<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Datetime type for flexible date/time string validation.
 *
 * Validates datetime strings that PHP's strtotime can parse. This provides
 * maximum flexibility accepting various formats like ISO 8601, relative
 * dates, and common date/time string patterns.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new DateTimeType();
 *
 * $valid = $type->validate('2024-01-15 14:30:00');  // Returns '2024-01-15 14:30:00'
 * $valid = $type->validate('2024-01-15T14:30:00Z'); // Returns '2024-01-15T14:30:00Z'
 * $valid = $type->validate('next monday');          // Returns 'next monday'
 * $valid = $type->validate('+1 week');              // Returns '+1 week'
 * $invalid = $type->validate('not-a-date');         // Throws ValidationException
 * ```
 */
final readonly class DateTimeType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'DATETIME'
     */
    public function getName(): string
    {
        return 'DATETIME';
    }

    /**
     * Validates a datetime string using PHP's strtotime.
     *
     * Accepts any format that strtotime can parse, including ISO 8601,
     * natural language dates, relative dates, and various localized
     * formats. The original string is returned unchanged.
     *
     * @param string $value The string to validate as a datetime
     * @return string The validated datetime string unchanged
     * @throws ValidationException When the string cannot be parsed as a datetime
     */
    public function validate(string $value): string
    {
        if (strtotime($value) === false) {
            throw new ValidationException('Invalid datetime: ' . $value);
        }

        return $value;
    }
}
