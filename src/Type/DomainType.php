<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class DomainType implements TypeInterface
{
    public function getName(): string
    {
        return 'DOMAIN';
    }

    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new ValidationException("Invalid domain: {$value}");
        }

        return $value;
    }
}
