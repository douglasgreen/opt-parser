<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Domain name type for hostname validation.
 *
 * Validates domain names using PHP's filter_var with FILTER_VALIDATE_DOMAIN
 * and FILTER_FLAG_HOSTNAME, ensuring RFC 952/1123 hostname compliance.
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
 * $type = new DomainType();
 *
 * $valid = $type->validate('example.com');       // Returns 'example.com'
 * $valid = $type->validate('sub.domain.org');    // Returns 'sub.domain.org'
 * $invalid = $type->validate('not a domain');    // Throws ValidationException
 * $invalid = $type->validate('-invalid.com');    // Throws ValidationException
 * ```
 */
final readonly class DomainType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'DOMAIN'
     */
    public function getName(): string
    {
        return 'DOMAIN';
    }

    /**
     * Validates a domain name string.
     *
     * Uses PHP's FILTER_VALIDATE_DOMAIN with FILTER_FLAG_HOSTNAME to
     * ensure the domain follows RFC 952/1123 hostname requirements.
     * Valid domains contain alphanumeric characters, hyphens (not at
     * start/end), and dots separating labels.
     *
     * @param string $value The string to validate as a domain name
     *
     * @return string The validated domain name unchanged
     *
     * @throws ValidationException When the domain format is invalid
     */
    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new ValidationException('Invalid domain: ' . $value);
        }

        return $value;
    }
}
