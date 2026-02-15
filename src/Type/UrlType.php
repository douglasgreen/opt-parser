<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * URL type for uniform resource locator validation.
 *
 * Validates URLs using PHP's filter_var with FILTER_VALIDATE_URL, which
 * accepts URLs with various schemes (http, https, ftp, etc.) and validates
 * syntax according to RFC 2396.
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
 * $type = new UrlType();
 *
 * $valid = $type->validate('https://example.com/path?query=1');  // Returns the URL
 * $valid = $type->validate('ftp://ftp.example.com/files');       // Returns the URL
 * $valid = $type->validate('http://localhost:8080');             // Returns the URL
 * $invalid = $type->validate('not-a-url');                       // Throws ValidationException
 * $invalid = $type->validate('example.com');                     // Throws ValidationException (no scheme)
 * ```
 */
final readonly class UrlType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'URL'
     */
    public function getName(): string
    {
        return 'URL';
    }

    /**
     * Validates a URL string.
     *
     * Uses PHP's FILTER_VALIDATE_URL which validates URL syntax according
     * to RFC 2396. Requires a scheme (e.g., http://, https://) and a host.
     * Note that this validates syntax only, not URL accessibility.
     *
     * @param string $value The string to validate as a URL
     *
     * @return string The validated URL unchanged
     *
     * @throws ValidationException When the URL format is invalid
     */
    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid URL: ' . $value);
        }

        return $value;
    }
}
