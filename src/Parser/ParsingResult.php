<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Intermediate parsing state before validation.
 */
final class ParsingResult
{
    /** @var array<string, mixed> */
    public array $mappedOptions = [];

    public ?string $command = null;

    /** @var array<int, string> */
    public array $operands = [];

    /** @var array<string, string> */
    public array $rawValues = [];
}
