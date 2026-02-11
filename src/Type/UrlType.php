<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class UrlType implements TypeInterface
{
    public function getName(): string
    {
        return 'URL';
    }

    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new ValidationException("Invalid URL: {$value}");
        }

        return $value;
    }
}
