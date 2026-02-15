<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Input file type for readable file path validation.
 *
 * Validates that a path points to an existing, readable file. This is useful
 * for input file arguments that must be accessible before processing can
 * proceed, such as configuration files, data files, or source files.
 *
 * @package DouglasGreen\OptParser\Type
 * @api
 * @since 1.0.0
 * @see TypeInterface For the type contract
 * @see OutfileType For output file validation
 *
 * @example
 * ```php
 * $type = new InfileType();
 *
 * $valid = $type->validate('/etc/passwd');        // Returns '/etc/passwd' if readable
 * $valid = $type->validate('./data/input.csv');   // Returns './data/input.csv' if exists and readable
 * $invalid = $type->validate('/nonexistent.txt'); // Throws ValidationException
 * $invalid = $type->validate('/root/.ssh/id_rsa'); // Throws ValidationException if not readable
 * $invalid = $type->validate('/tmp');             // Throws ValidationException (is a directory)
 * ```
 */
final readonly class InfileType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'INFILE'
     */
    public function getName(): string
    {
        return 'INFILE';
    }

    /**
     * Validates that a path is an existing, readable file.
     *
     * Performs filesystem checks to ensure the path exists, is a regular file
     * (not a directory), and has read permissions for the current user.
     * Returns the path unchanged on success.
     *
     * @param string $value The file path to validate
     * @return string The validated file path unchanged
     * @throws ValidationException When the file does not exist, is not a file, or is not readable
     */
    public function validate(string $value): string
    {
        if (!is_readable($value) || !is_file($value)) {
            throw new ValidationException('File not found or not readable: ' . $value, 1);
        }

        return $value;
    }
}
