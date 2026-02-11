<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class MacAddrType implements TypeInterface
{
    public function getName(): string
    {
        return 'MAC_ADDR';
    }

    public function validate(string $value): string
    {
        $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
        if (!preg_match($pattern, $value)) {
            throw new ValidationException("Invalid MAC address: {$value}");
        }

        return $value;
    }
}
