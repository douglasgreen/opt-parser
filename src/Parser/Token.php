<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Lexical token value object representing a parsed command-line element.
 *
 * Immutable value object that captures the type and content of a single
 * token produced during tokenization. Supports option values attached
 * directly to the option (e.g., --opt=value or -ovalue).
 *
 * @package DouglasGreen\OptParser\Parser
 *
 * @api
 *
 * @since 1.0.0
 * @see TokenType For the token type enumeration
 * @see Tokenizer For token creation from argv arrays
 *
 * @example
 * ```php
 * // Simple token without attached value
 * $token = new Token(TokenType::SHORT_OPTION, 'v');
 *
 * // Token with attached value
 * $token = new Token(TokenType::LONG_OPTION, 'output', 'result.txt');
 *
 * // Operand token
 * $token = new Token(TokenType::OPERAND, 'input.dat');
 *
 * if ($token->attachedValue !== null) {
 *     echo "Option {$token->value} has value: {$token->attachedValue}";
 * }
 * ```
 */
final readonly class Token
{
    /**
     * Constructs a new Token with type, value, and optional attached value.
     *
     * @param TokenType $type The token type classification
     * @param string $value The primary token value (option name or operand content)
     * @param string|null $attachedValue Optional value attached to an option (e.g., 'file.txt' in --opt=file.txt)
     */
    public function __construct(
        public TokenType $type,
        public string $value,
        public ?string $attachedValue = null,
    ) {}
}
