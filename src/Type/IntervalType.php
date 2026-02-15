<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DateInterval;
use DouglasGreen\OptParser\Exception\ValidationException;
use Exception;

/**
 * Date interval type for duration/time period validation.
 *
 * Validates date interval strings using PHP's DateInterval constructor.
 * Accepts the standard interval specification format (ISO 8601 duration
 * notation) used for representing time periods.
 *
 * @package DouglasGreen\OptParser\Type
 *
 * @api
 *
 * @since 1.0.0
 * @see TypeInterface For the type contract
 * @see https://www.php.net/manual/en/dateinterval.construct.php For valid format specifications
 *
 * @example
 * ```php
 * $type = new IntervalType();
 *
 * $valid = $type->validate('P1D');           // Returns 'P1D' (1 day)
 * $valid = $type->validate('P2W');           // Returns 'P2W' (2 weeks)
 * $valid = $type->validate('PT30M');         // Returns 'PT30M' (30 minutes)
 * $valid = $type->validate('P1Y2M3D');       // Returns 'P1Y2M3D' (1 year, 2 months, 3 days)
 * $valid = $type->validate('PT1H30M');       // Returns 'PT1H30M' (1 hour, 30 minutes)
 * $invalid = $type->validate('not-an-interval'); // Throws ValidationException
 * ```
 */
final readonly class IntervalType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'INTERVAL'
     */
    public function getName(): string
    {
        return 'INTERVAL';
    }

    /**
     * Validates a date interval string in ISO 8601 duration format.
     *
     * Uses PHP's DateInterval constructor to validate the format. The format
     * starts with 'P' (period) followed by date components (Y, M, D, W) and
     * optionally 'T' followed by time components (H, M, S).
     *
     * @param string $value The interval string to validate (e.g., 'P1D', 'PT30M')
     *
     * @return string The validated interval string unchanged
     *
     * @throws ValidationException When the interval format is invalid
     */
    public function validate(string $value): string
    {
        try {
            new DateInterval($value);
            return $value;
        } catch (Exception) {
            throw new ValidationException('Invalid interval: ' . $value);
        }
    }
}
