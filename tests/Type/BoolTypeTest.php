<?php

declare(strict_types=1);

namespace Tests\Unit\Type;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\BoolType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoolType::class)]
#[Small]
final class BoolTypeTest extends TestCase
{
    public static function truthyValueProvider(): iterable
    {
        yield 'lowercase true' => ['true'];
        yield 'numeric one' => ['1'];
        yield 'lowercase yes' => ['yes'];
        yield 'lowercase on' => ['on'];
        yield 'uppercase TRUE' => ['TRUE'];
        yield 'mixed case True' => ['True'];
    }

    public static function falsyValueProvider(): iterable
    {
        yield 'lowercase false' => ['false'];
        yield 'numeric zero' => ['0'];
        yield 'lowercase no' => ['no'];
        yield 'lowercase off' => ['off'];
        yield 'empty string' => [''];
        yield 'uppercase FALSE' => ['FALSE'];
    }

    public function test_it_returns_type_name(): void
    {
        // Arrange
        $type = new BoolType();

        // Act
        $name = $type->getName();

        // Assert
        $this->assertSame('BOOL', $name);
    }

    #[DataProvider('truthyValueProvider')]
    public function test_it_validates_truthy_values(string $value): void
    {
        // Arrange
        $type = new BoolType();

        // Act
        $result = $type->validate($value);

        // Assert
        $this->assertTrue($result);
    }

    #[DataProvider('falsyValueProvider')]
    public function test_it_validates_falsy_values(string $value): void
    {
        // Arrange
        $type = new BoolType();

        // Act
        $result = $type->validate($value);

        // Assert
        $this->assertFalse($result);
    }

    public function test_it_throws_for_invalid_boolean_strings(): void
    {
        // Arrange
        $type = new BoolType();

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid boolean: maybe');

        // Act
        $type->validate('maybe');
    }
}
