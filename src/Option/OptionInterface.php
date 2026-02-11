<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Contract for CLI options (flags, params, terms, commands).
 */
interface OptionInterface
{
    /**
     * @return list<string>
     */
    public function getNames(): array;

    public function getPrimaryName(): string;

    public function getDescription(): string;

    public function acceptsValue(): bool;

    public function isRequired(): bool;

    /**
     * @throws ValidationException
     */
    public function validateValue(string $value, TypeRegistry $registry): mixed;

    public function getDefault(): mixed;
}
