<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class DateType implements TypeInterface
{
    public function getName(): string
    {
        return 'DATE';
    }

    public function validate(string $value): string
    {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException("Invalid date format (YYYY-MM-DD): {$value}");
        }

        if (strtotime($value) === false) {
            throw new ValidationException("Invalid date: {$value}");
        }

        return $value;
    }
}
