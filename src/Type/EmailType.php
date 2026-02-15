<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Email address type for RFC-compliant email validation.
 *
 * Validates email addresses using PHP's built-in filter_var with
 * FILTER_VALIDATE_EMAIL, which follows RFC 822 syntax requirements.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new EmailType();
 *
 * $valid = $type->validate('user@example.com');     // Returns 'user@example.com'
 * $valid = $type->validate('user+tag@domain.org');  // Returns 'user+tag@domain.org'
 * $invalid = $type->validate('not-an-email');       // Throws ValidationException
 * ```
 */
final readonly class EmailType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'EMAIL'
     */
    public function getName(): string
    {
        return 'EMAIL';
    }

    /**
     * Validates an email address string.
     *
     * Uses PHP's FILTER_VALIDATE_EMAIL which validates against RFC 822
     * syntax. Note that this validates syntax only, not deliverability.
     *
     * @param string $value The string to validate as an email address
     * @return string The validated email address unchanged
     * @throws ValidationException When the email format is invalid
     */
    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email: ' . $value);
        }

        return $value;
    }
}
