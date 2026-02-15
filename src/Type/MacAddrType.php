<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * MAC address type for network hardware address validation.
 *
 * Validates MAC (Media Access Control) addresses in common colon or
 * hyphen-separated hexadecimal notation. Accepts both uppercase and
 * lowercase hex digits.
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
 * $type = new MacAddrType();
 *
 * // Colon-separated format
 * $valid = $type->validate('00:1A:2B:3C:4D:5E');  // Returns '00:1A:2B:3C:4D:5E'
 *
 * // Hyphen-separated format
 * $valid = $type->validate('00-1A-2B-3C-4D-5E');  // Returns '00-1A-2B-3C-4D-5E'
 *
 * // Mixed case
 * $valid = $type->validate('00:1a:2b:3c:4d:5e');  // Returns '00:1a:2b:3c:4d:5e'
 *
 * // Invalid formats
 * $invalid = $type->validate('not-a-mac');        // Throws ValidationException
 * $invalid = $type->validate('00:1A:2B');         // Throws ValidationException (incomplete)
 * ```
 */
final readonly class MacAddrType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'MAC_ADDR'
     */
    public function getName(): string
    {
        return 'MAC_ADDR';
    }

    /**
     * Validates a MAC address string in colon or hyphen notation.
     *
     * Expects exactly 6 pairs of hexadecimal digits separated by colons (:)
     * or hyphens (-). The separator must be consistent throughout the address.
     * Case-insensitive for hex digits (A-F or a-f).
     *
     * @param string $value The string to validate as a MAC address
     *
     * @return string The validated MAC address unchanged
     *
     * @throws ValidationException When the MAC address format is invalid
     */
    public function validate(string $value): string
    {
        $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid MAC address: ' . $value);
        }

        return $value;
    }
}
