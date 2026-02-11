<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use Closure;
use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;
use Exception;

/**
 * Positional argument (term).
 */
final readonly class Term extends AbstractOption
{
    public function __construct(
        string $name,
        string $description,
        private readonly string $typeName,
        private readonly bool $required = true,
        private readonly ?Closure $filter = null,
    ) {
        parent::__construct([$name], $description);
    }

    public function acceptsValue(): bool
    {
        return true;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function validateValue(string $value, TypeRegistry $registry): mixed
    {
        $type = $registry->get($this->typeName);
        $typedValue = $type->validate($value);

        if ($this->filter !== null) {
            try {
                $typedValue = ($this->filter)($typedValue);
            } catch (Exception $e) {
                throw new ValidationException(
                    "Filter rejected value for '{$this->getPrimaryName()}': {$e->getMessage()}",
                );
            }
        }

        return $typedValue;
    }
}
