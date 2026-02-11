<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use Override;
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
        private string $typeName,
        private bool $required = true,
        private ?Closure $filter = null,
    ) {
        parent::__construct([$name], $description);
    }

    public function acceptsValue(): bool
    {
        return true;
    }

    #[Override]
    public function isRequired(): bool
    {
        return $this->required;
    }

    public function validateValue(string $value, TypeRegistry $registry): mixed
    {
        $type = $registry->get($this->typeName);
        $typedValue = $type->validate($value);

        if ($this->filter instanceof Closure) {
            try {
                $typedValue = ($this->filter)($typedValue);
            } catch (Exception $e) {
                throw new ValidationException(
                    sprintf("Filter rejected value for '%s': %s", $this->getPrimaryName(), $e->getMessage()),
                );
            }
        }

        return $typedValue;
    }
}
