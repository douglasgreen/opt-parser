<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class OutfileType implements TypeInterface
{
    public function getName(): string
    {
        return 'OUTFILE';
    }

    public function validate(string $value): string
    {
        $dir = dirname($value);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new ValidationException('Directory not writable for output file: ' . $dir);
        }

        return $value;
    }
}
