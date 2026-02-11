<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

final readonly class StringType implements TypeInterface
{
    public function getName(): string
    {
        return 'STRING';
    }

    public function validate(string $value): string
    {
        return $value;
    }
}
