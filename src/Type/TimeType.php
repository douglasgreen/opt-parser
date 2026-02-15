<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Time type for 24-hour time format validation.
 *
 * Validates time strings in HH:MM or HH:MM:SS format using 24-hour notation.
 * Performs both format validation and semantic validation to ensure the
 * time values are valid (e.g., rejects 25:00 or 12:60).
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new TimeType();
 *
 * $valid = $type->validate('14:30');       // Returns '14:30' (2:30 PM)
 * $valid = $type->validate('09:00:00');    // Returns '09:00:00' (with seconds)
 * $valid = $type->validate('23:59');       // Returns '23:59' (11:59 PM)
 * $invalid = $type->validate('25:00');     // Throws ValidationException (invalid hour)
 * $invalid = $type->validate('12:60');     // Throws ValidationException (invalid minute)
 * $invalid = $type->validate('1:30');      // Throws ValidationException (wrong format)
 * ```
 */
final readonly class TimeType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'TIME'
     */
    public function getName(): string
    {
        return 'TIME';
    }

    /**
     * Validates a time string in HH:MM or HH:MM:SS format.
     *
     * Performs two-stage validation: first checks format compliance with
     * a regular expression requiring two-digit hours and minutes with optional
     * seconds, then verifies the time is semantically valid using strtotime.
     *
     * @param string $value The string to validate as a time
     * @return string The validated time string unchanged
     * @throws ValidationException When the format is incorrect or the time is invalid
     */
    public function validate(string $value): string
    {
        $pattern = '/^\d{2}:\d{2}(:\d{2})?$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid time format (HH:MM or HH:MM:SS): ' . $value);
        }

        if (strtotime($value) === false) {
            throw new ValidationException('Invalid time: ' . $value);
        }

        return $value;
    }
}
