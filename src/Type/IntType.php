<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class IntType implements TypeInterface
{
    public function getName(): string
    {
        return 'INT';
    }

    public function validate(string $value): int
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            throw new ValidationException('Invalid integer: ' . $value);
        }

        return (int) $value;
    }
}
