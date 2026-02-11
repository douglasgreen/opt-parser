<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class DirType implements TypeInterface
{
    public function getName(): string
    {
        return 'DIR';
    }

    public function validate(string $value): string
    {
        if (!is_dir($value) || !is_readable($value)) {
            throw new ValidationException('Directory not found or not readable: ' . $value, 1);
        }

        return $value;
    }
}
