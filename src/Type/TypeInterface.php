<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Contract for command-line argument type validators.
 *
 * Defines the interface for type-specific validation and transformation
 * of string inputs from command-line arguments. Implementations validate
 * format compliance and may convert strings to native types (e.g., int, float, bool).
 *
 * @package DouglasGreen\OptParser\Type
 *
 * @api
 *
 * @since 1.0.0
 *
 * @example
 * ```php
 * // Implementing a custom type
 * class HexType implements TypeInterface
 * {
 *     public function getName(): string
 *     {
 *         return 'HEX';
 *     }
 *
 *     public function validate(string $value): int
 *     {
 *         if (!ctype_xdigit($value)) {
 *             throw new ValidationException('Invalid hexadecimal: ' . $value);
 *         }
 *         return hexdec($value);
 *     }
 * }
 * ```
 */
interface TypeInterface
{
    /**
     * Returns the type name identifier used in option definitions.
     *
     * The name should be a concise, uppercase identifier (e.g., 'INT', 'EMAIL')
     * that users can specify when defining parameter types.
     *
     * @return non-empty-string The type name identifier
     */
    public function getName(): string;

    /**
     * Validates and transforms a string input to its typed representation.
     *
     * Implementations must validate the input string against type-specific
     * format requirements and return an appropriately typed value. The return
     * type varies by implementation (string, int, float, bool, etc.).
     *
     * @param string $value The string input to validate and transform
     *
     * @return mixed The validated and possibly transformed value
     *
     * @throws ValidationException When the input fails type validation
     */
    public function validate(string $value): mixed;
}
