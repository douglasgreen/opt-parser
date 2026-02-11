<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Contract for data type validators.
 */
interface TypeInterface
{
    public function getName(): string;

    /**
     * Validates and transforms input string to typed value.
     *
     * @throws ValidationException on validation failure
     */
    public function validate(string $value): mixed;
}
