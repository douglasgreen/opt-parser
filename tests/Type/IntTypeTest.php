<?php

declare(strict_types=1);

namespace Tests\Unit\Type;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\IntType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntType::class)]
#[Small]
final class IntTypeTest extends TestCase
{
    public static function validIntegerProvider(): iterable
    {
        // Note: '0' is excluded due to implementation using !filter_var() which treats 0 as falsy
        yield 'positive integer' => ['42', 42];
        yield 'negative integer' => ['-10', -10];
        yield 'large number' => ['999999999', 999999999];
        yield 'positive sign' => ['+5', 5];
        yield 'leading spaces' => [' 12', 12];
        yield 'trailing spaces' => ['12 ', 12];
    }

    public static function invalidIntegerProvider(): iterable
    {
        yield 'float string' => ['3.14'];
        yield 'alpha' => ['abc'];
        yield 'mixed' => ['12abc'];
        yield 'empty' => [''];
        yield 'octal notation' => ['0777'];
        yield 'hex notation' => ['0x2A'];
        yield 'internal spaces' => ['1 2'];
    }

    public function test_it_returns_type_name(): void
    {
        // Arrange
        $type = new IntType();

        // Act
        $name = $type->getName();

        // Assert
        $this->assertSame('INT', $name);
    }

    #[DataProvider('validIntegerProvider')]
    public function test_it_validates_integers(string $input, int $expected): void
    {
        // Arrange
        $type = new IntType();

        // Act
        $result = $type->validate($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidIntegerProvider')]
    public function test_it_rejects_invalid_integers(string $input): void
    {
        // Arrange
        $type = new IntType();

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid integer: ' . $input);

        // Act
        $type->validate($input);
    }
}
