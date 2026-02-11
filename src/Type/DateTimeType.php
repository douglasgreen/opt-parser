<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class DateTimeType implements TypeInterface
{
    public function getName(): string
    {
        return 'DATETIME';
    }

    public function validate(string $value): string
    {
        if (strtotime($value) === false) {
            throw new ValidationException('Invalid datetime: ' . $value);
        }

        return $value;
    }
}
