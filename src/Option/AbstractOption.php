<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Base implementation for options with common functionality.
 */
abstract class AbstractOption implements OptionInterface
{
    /** @var list<string> */
    protected readonly array $names;

    public function __construct(
        array $names,
        protected readonly string $description,
    ) {
        $this->names = array_values($names);
    }

    public function getNames(): array
    {
        return $this->names;
    }

    public function getPrimaryName(): string
    {
        return $this->names[0] ?? '';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function getDefault(): mixed
    {
        return null;
    }

    abstract public function acceptsValue(): bool;

    abstract public function validateValue(string $value, TypeRegistry $registry): mixed;
}
