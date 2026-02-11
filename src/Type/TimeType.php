<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class TimeType implements TypeInterface
{
    public function getName(): string
    {
        return 'TIME';
    }

    public function validate(string $value): string
    {
        $pattern = '/^\d{2}:\d{2}(:\d{2})?$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException("Invalid time format (HH:MM or HH:MM:SS): {$value}");
        }

        if (strtotime($value) === false) {
            throw new ValidationException("Invalid time: {$value}");
        }

        return $value;
    }
}
