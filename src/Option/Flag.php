<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Boolean flag without argument (e.g., -v, --verbose).
 */
final readonly class Flag extends AbstractOption
{
    public function acceptsValue(): bool
    {
        return false;
    }

    public function validateValue(string $value, TypeRegistry $registry): true
    {
        return true;
    }

    public function getDefault(): bool
    {
        return false;
    }
}
