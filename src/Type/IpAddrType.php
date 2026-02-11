<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class IpAddrType implements TypeInterface
{
    public function getName(): string
    {
        return 'IP_ADDR';
    }

    public function validate(string $value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            throw new ValidationException("Invalid IP address: {$value}");
        }

        return $value;
    }
}
