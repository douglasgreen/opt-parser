<?php

declare(strict_types=1);

namespace Tests\Unit\Parser;

use DouglasGreen\OptParser\Parser\Token;
use DouglasGreen\OptParser\Parser\Tokenizer;
use DouglasGreen\OptParser\Parser\TokenType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tokenizer::class)]
#[Small]
final class TokenizerTest extends TestCase
{
    public function test_it_tokenizes_empty_array(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize([]);

        // Assert
        $this->assertSame([], $tokens);
    }

    public function test_it_tokenizes_short_option(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['-a']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertInstanceOf(Token::class, $tokens[0]);
        $this->assertSame(TokenType::SHORT_OPTION, $tokens[0]->type);
        $this->assertSame('a', $tokens[0]->value);
    }

    public function test_it_tokenizes_long_option(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['--verbose']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::LONG_OPTION, $tokens[0]->type);
        $this->assertSame('verbose', $tokens[0]->value);
    }

    public function test_it_tokenizes_long_option_with_equals(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['--output=file.txt']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::LONG_OPTION, $tokens[0]->type);
        $this->assertSame('output', $tokens[0]->value);
        $this->assertSame('file.txt', $tokens[0]->attachedValue);
    }

    public function test_it_tokenizes_operand(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['filename.txt']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::OPERAND, $tokens[0]->type);
        $this->assertSame('filename.txt', $tokens[0]->value);
    }

    public function test_it_tokenizes_terminator(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['--']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::TERMINATOR, $tokens[0]->type);
    }

    public function test_it_treats_args_after_terminator_as_operands(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['--', '-f', '--option']);

        // Assert
        $this->assertCount(3, $tokens);
        $this->assertSame(TokenType::TERMINATOR, $tokens[0]->type);
        $this->assertSame(TokenType::OPERAND, $tokens[1]->type);
        $this->assertSame(TokenType::OPERAND, $tokens[2]->type);
        $this->assertSame('-f', $tokens[1]->value);
    }

    public function test_it_handles_short_option_with_attached_non_numeric_value(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['-ovalue']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::SHORT_OPTION, $tokens[0]->type);
        $this->assertSame('o', $tokens[0]->value);
        $this->assertSame('value', $tokens[0]->attachedValue);
    }

    public function test_it_handles_mixed_arguments(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['-v', '--output=file.txt', 'input.txt']);

        // Assert
        $this->assertCount(3, $tokens);
        $this->assertSame(TokenType::SHORT_OPTION, $tokens[0]->type);
        $this->assertSame(TokenType::LONG_OPTION, $tokens[1]->type);
        $this->assertSame(TokenType::OPERAND, $tokens[2]->type);
    }

    public function test_it_tracks_termination_state(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $this->assertFalse($tokenizer->isTerminated());
        $tokenizer->tokenize(['--', 'arg']);

        // Assert
        $this->assertTrue($tokenizer->isTerminated());
    }

    public function test_it_resets_termination_on_new_tokenize(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();
        $tokenizer->tokenize(['--', 'arg']);

        // Act
        $tokenizer->tokenize(['-a']);

        // Assert
        $this->assertFalse($tokenizer->isTerminated());
    }

    public function test_it_expands_repeated_short_flags(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['-vvv']);

        // Assert
        $this->assertCount(3, $tokens);
        foreach ($tokens as $token) {
            $this->assertSame(TokenType::SHORT_OPTION, $token->type);
            $this->assertSame('v', $token->value);
            $this->assertNull($token->attachedValue);
        }
    }

    public function test_it_handles_short_option_with_numeric_attached_value(): void
    {
        // Arrange
        $tokenizer = new Tokenizer();

        // Act
        $tokens = $tokenizer->tokenize(['-n123']);

        // Assert
        $this->assertCount(1, $tokens);
        $this->assertSame(TokenType::SHORT_OPTION, $tokens[0]->type);
        $this->assertSame('n', $tokens[0]->value);
        $this->assertSame('123', $tokens[0]->attachedValue);
    }
}
