<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Lexical token value object.
 */
final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public ?string $attachedValue = null,
    ) {}
}
