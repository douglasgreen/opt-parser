<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Directory path type for filesystem directory validation.
 *
 * Validates that a path points to an existing, readable directory.
 * This is useful for input paths that must be accessible before
 * processing can proceed.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 *
 * @example
 * ```php
 * $type = new DirType();
 *
 * $valid = $type->validate('/var/log');      // Returns '/var/log' if exists and readable
 * $valid = $type->validate('./config');      // Returns './config' if exists and readable
 * $invalid = $type->validate('/nonexistent'); // Throws ValidationException
 * $invalid = $type->validate('/root');        // Throws ValidationException if not readable
 * ```
 */
final readonly class DirType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'DIR'
     */
    public function getName(): string
    {
        return 'DIR';
    }

    /**
     * Validates that a path is an existing, readable directory.
     *
     * Performs filesystem checks to ensure the path exists, is a directory,
     * and has read permissions for the current user.
     *
     * @param string $value The directory path to validate
     * @return string The validated directory path unchanged
     * @throws ValidationException When the directory does not exist or is not readable
     */
    public function validate(string $value): string
    {
        if (!is_dir($value) || !is_readable($value)) {
            throw new ValidationException('Directory not found or not readable: ' . $value, 1);
        }

        return $value;
    }
}
