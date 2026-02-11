<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class FloatType implements TypeInterface
{
    public function getName(): string
    {
        return 'FLOAT';
    }

    public function validate(string $value): float
    {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            throw new ValidationException("Invalid float: {$value}");
        }

        return (float) $value;
    }
}
