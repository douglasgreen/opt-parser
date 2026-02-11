<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class InfileType implements TypeInterface
{
    public function getName(): string
    {
        return 'INFILE';
    }

    public function validate(string $value): string
    {
        if (!is_readable($value) || !is_file($value)) {
            throw new ValidationException('File not found or not readable: ' . $value, 1);
        }

        return $value;
    }
}
