<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * UUID type for universally unique identifier validation.
 *
 * Validates UUID strings in the canonical 8-4-4-4-12 hexadecimal format
 * (e.g., 550e8400-e29b-41d4-a716-446655440000). Accepts both uppercase
 * and lowercase hexadecimal digits. Does not validate UUID version or variant.
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
 * $type = new UuidType();
 *
 * $valid = $type->validate('550e8400-e29b-41d4-a716-446655440000');  // Returns the UUID
 * $valid = $type->validate('6BA7B810-9DAD-11D1-80B4-00C04FD430C8');  // Returns the UUID (uppercase)
 * $invalid = $type->validate('not-a-uuid');                          // Throws ValidationException
 * $invalid = $type->validate('550e8400e29b41d4a716446655440000');    // Throws ValidationException (no hyphens)
 * $invalid = $type->validate('550e8400-e29b-41d4-a716');             // Throws ValidationException (incomplete)
 * ```
 */
final readonly class UuidType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'UUID'
     */
    public function getName(): string
    {
        return 'UUID';
    }

    /**
     * Validates a UUID string in canonical format.
     *
     * Validates that the string matches the 8-4-4-4-12 hexadecimal format
     * with hyphens as separators. The validation is format-only; it does
     * not verify UUID version, variant, or actual uniqueness.
     *
     * @param string $value The string to validate as a UUID
     *
     * @return string The validated UUID string unchanged
     *
     * @throws ValidationException When the UUID format is invalid
     */
    public function validate(string $value): string
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid UUID: ' . $value);
        }

        return $value;
    }
}
