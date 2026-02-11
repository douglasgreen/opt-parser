<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DateInterval;
use DouglasGreen\OptParser\Exception\ValidationException;
use Exception;

final readonly class IntervalType implements TypeInterface
{
    public function getName(): string
    {
        return 'INTERVAL';
    }

    public function validate(string $value): string
    {
        try {
            new DateInterval($value);
            return $value;
        } catch (Exception) {
            throw new ValidationException('Invalid interval: ' . $value);
        }
    }
}
