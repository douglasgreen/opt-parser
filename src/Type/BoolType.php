<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class BoolType implements TypeInterface
{
    public function getName(): string
    {
        return 'BOOL';
    }

    public function validate(string $value): bool
    {
        $truthy = ['true', '1', 'yes', 'on'];
        $falsy = ['false', '0', 'no', 'off', ''];

        $normalized = strtolower($value);

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        throw new ValidationException("Invalid boolean: {$value}");
    }
}
