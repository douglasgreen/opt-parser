<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Subcommand selector (e.g., git clone, git push).
 */
final readonly class Command extends AbstractOption
{
    public function acceptsValue(): bool
    {
        return false;
    }

    public function validateValue(string $value, TypeRegistry $registry): string
    {
        return $value;
    }

    public function getDefault(): ?string
    {
        return null;
    }
}
