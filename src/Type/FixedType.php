<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class FixedType implements TypeInterface
{
    public function getName(): string
    {
        return 'FIXED';
    }

    public function validate(string $value): string
    {
        // Remove commas for validation
        $normalized = str_replace(',', '', $value);

        if (!is_numeric($normalized)) {
            throw new ValidationException("Invalid fixed-point number: {$value}");
        }

        return $value;
    }
}
