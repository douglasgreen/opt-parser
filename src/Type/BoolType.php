<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Boolean type for flexible boolean string validation.
 *
 * Validates and converts common boolean string representations to actual
 * boolean values. Accepts multiple truthy/falsy conventions commonly
 * used in command-line arguments and configuration files.
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
 * $type = new BoolType();
 *
 * // Truthy values return true
 * $type->validate('true');  // Returns true
 * $type->validate('1');     // Returns true
 * $type->validate('yes');   // Returns true
 * $type->validate('on');    // Returns true
 * $type->validate('TRUE');  // Returns true (case-insensitive)
 *
 * // Falsy values return false
 * $type->validate('false'); // Returns false
 * $type->validate('0');     // Returns false
 * $type->validate('no');    // Returns false
 * $type->validate('off');   // Returns false
 * $type->validate('');      // Returns false
 *
 * // Invalid values throw exception
 * $type->validate('maybe'); // Throws ValidationException
 * ```
 */
final readonly class BoolType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'BOOL'
     */
    public function getName(): string
    {
        return 'BOOL';
    }

    /**
     * Validates and converts a boolean string to a native boolean.
     *
     * Accepts the following values (case-insensitive):
     * - Truthy: 'true', '1', 'yes', 'on'
     * - Falsy: 'false', '0', 'no', 'off', '' (empty string)
     *
     * @param string $value The string to validate and convert
     *
     * @return bool The converted boolean value
     *
     * @throws ValidationException When the value is not a recognized boolean representation
     */
    public function validate(string $value): bool
    {
        $truthy = ['true', '1', 'yes', 'on'];
        $falsy = ['false', '0', 'no', 'off', ''];

        $normalized = strtolower($value);

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        throw new ValidationException('Invalid boolean: ' . $value);
    }
}
