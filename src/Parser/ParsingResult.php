<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Intermediate parsing state before validation.
 *
 * @package OptParser
 * @since 1.0.0
 * @internal
 */
final class ParsingResult
{
    /** @var array<string, mixed> */
    public array $mappedOptions = [];

    /**
     * The resolved command name if a command was matched.
     *
     * @var string|null
     */
    public ?string $command = null;

    /** @var array<int, string> */
    public array $operands = [];

    /** @var array<string, string> */
    public array $rawValues = [];
}
