<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * IP address type for IPv4 and IPv6 address validation.
 *
 * Validates IP addresses using PHP's filter_var with FILTER_VALIDATE_IP,
 * accepting both IPv4 (e.g., 192.168.1.1) and IPv6 (e.g., ::1, 2001:db8::1)
 * formats without distinction.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new IpAddrType();
 *
 * // IPv4 addresses
 * $valid = $type->validate('192.168.1.1');    // Returns '192.168.1.1'
 * $valid = $type->validate('127.0.0.1');      // Returns '127.0.0.1'
 *
 * // IPv6 addresses
 * $valid = $type->validate('::1');            // Returns '::1'
 * $valid = $type->validate('2001:db8::1');    // Returns '2001:db8::1'
 *
 * // Invalid addresses
 * $invalid = $type->validate('not-an-ip');    // Throws ValidationException
 * $invalid = $type->validate('256.1.1.1');    // Throws ValidationException
 * ```
 */
final readonly class IpAddrType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'IP_ADDR'
     */
    public function getName(): string
    {
        return 'IP_ADDR';
    }

    /**
     * Validates an IP address string (IPv4 or IPv6).
     *
     * Uses PHP's FILTER_VALIDATE_IP which accepts both IPv4 and IPv6
     * address formats. Does not validate private, reserved, or public
     * address ranges separately.
     *
     * @param string $value The string to validate as an IP address
     * @return string The validated IP address unchanged
     * @throws ValidationException When the IP address format is invalid
     */
    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            throw new ValidationException('Invalid IP address: ' . $value);
        }

        return $value;
    }
}
