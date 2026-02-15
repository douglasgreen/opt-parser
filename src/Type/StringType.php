<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

/**
 * String type for passthrough string validation.
 *
 * A minimal type validator that accepts any string value without transformation
 * or validation. Serves as the default type for parameters that don't require
 * specific format constraints.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new StringType();
 *
 * $valid = $type->validate('any text');     // Returns 'any text'
 * $valid = $type->validate('123');         // Returns '123'
 * $valid = $type->validate('');            // Returns ''
 * ```
 */
final readonly class StringType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'STRING'
     */
    public function getName(): string
    {
        return 'STRING';
    }

    /**
     * Returns the input string unchanged.
     *
     * This validator performs no validation or transformation, serving as
     * a passthrough for any string value.
     *
     * @param string $value The string to validate
     * @return string The input string unchanged
     */
    public function validate(string $value): string
    {
        return $value;
    }
}
