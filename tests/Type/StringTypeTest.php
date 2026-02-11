<?php

declare(strict_types=1);

namespace Tests\Unit\Type;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\StringType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringType::class)]
#[Small]
final class StringTypeTest extends TestCase
{
    public function test_it_returns_type_name(): void
    {
        // Arrange
        $type = new StringType();

        // Act
        $name = $type->getName();

        // Assert
        $this->assertSame('STRING', $name);
    }

    public function test_it_returns_value_unchanged(): void
    {
        // Arrange
        $type = new StringType();
        $input = 'any string value including special chars: @#$%^&*()';

        // Act
        $result = $type->validate($input);

        // Assert
        $this->assertSame($input, $result);
    }

    public function test_it_handles_empty_string(): void
    {
        // Arrange
        $type = new StringType();

        // Act
        $result = $type->validate('');

        // Assert
        $this->assertSame('', $result);
    }

    public function test_it_handles_unicode(): void
    {
        // Arrange
        $type = new StringType();
        $unicode = 'Hello 世界 🌍';

        // Act
        $result = $type->validate($unicode);

        // Assert
        $this->assertSame($unicode, $result);
    }
}
