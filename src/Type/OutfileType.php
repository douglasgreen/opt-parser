<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Output file type for writable file path validation.
 *
 * Validates that the parent directory of a file path exists and is writable.
 * This is useful for output file arguments where the file may not yet exist
 * but must be creatable in the specified location.
 *
 * @package DouglasGreen\OptParser\Type
 *
 * @api
 *
 * @since 1.0.0
 * @see TypeInterface For the type contract
 * @see InfileType For input file validation
 *
 * @example
 * ```php
 * $type = new OutfileType();
 *
 * $valid = $type->validate('/tmp/output.txt');      // Returns '/tmp/output.txt' if /tmp is writable
 * $valid = $type->validate('./results/data.csv');   // Returns './results/data.csv' if ./results exists and writable
 * $invalid = $type->validate('/root/output.txt');   // Throws ValidationException (directory not writable)
 * $invalid = $type->validate('/nonexistent/out.txt'); // Throws ValidationException (directory doesn't exist)
 * ```
 */
final readonly class OutfileType implements TypeInterface
{
    /**
     * Returns the type name identifier.
     *
     * @return non-empty-string The type name 'OUTFILE'
     */
    public function getName(): string
    {
        return 'OUTFILE';
    }

    /**
     * Validates that the parent directory exists and is writable.
     *
     * Extracts the parent directory from the file path and verifies that
     * it exists as a directory and has write permissions. The file itself
     * does not need to exist, only its containing directory.
     *
     * @param string $value The output file path to validate
     *
     * @return string The validated file path unchanged
     *
     * @throws ValidationException When the parent directory does not exist or is not writable
     */
    public function validate(string $value): string
    {
        $dir = dirname($value);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new ValidationException('Directory not writable for output file: ' . $dir, 1);
        }

        return $value;
    }
}
