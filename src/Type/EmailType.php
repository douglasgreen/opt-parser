<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class EmailType implements TypeInterface
{
    public function getName(): string
    {
        return 'EMAIL';
    }

    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email: {$value}");
        }

        return $value;
    }
}
