<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class UuidType implements TypeInterface
{
    public function getName(): string
    {
        return 'UUID';
    }

    public function validate(string $value): string
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid UUID: ' . $value);
        }

        return $value;
    }
}
