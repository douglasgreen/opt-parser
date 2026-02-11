<?php

declare(strict_types=1);

namespace Tests\Unit\Parser;

use DouglasGreen\OptParser\Parser\Token;
use DouglasGreen\OptParser\Parser\TokenType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Token::class)]
#[Small]
final class TokenTest extends TestCase
{
    public function test_it_stores_type_and_value(): void
    {
        // Arrange
        $token = new Token(TokenType::SHORT_OPTION, 'a');

        // Act
        $type = $token->type;
        $value = $token->value;

        // Assert
        $this->assertSame(TokenType::SHORT_OPTION, $type);
        $this->assertSame('a', $value);
    }

    public function test_it_allows_attached_value(): void
    {
        // Arrange
        $token = new Token(TokenType::LONG_OPTION, 'output', 'file.txt');

        // Act
        $attached = $token->attachedValue;

        // Assert
        $this->assertSame('file.txt', $attached);
    }

    public function test_attached_value_defaults_to_null(): void
    {
        // Arrange
        $token = new Token(TokenType::OPERAND, 'filename');

        // Act
        $attached = $token->attachedValue;

        // Assert
        $this->assertNull($attached);
    }
}
