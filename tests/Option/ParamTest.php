<?php

declare(strict_types=1);

namespace Tests\Unit\Option;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Option\Param;
use DouglasGreen\OptParser\Type\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Param::class)]
#[Small]
final class ParamTest extends TestCase
{
    public function test_it_stores_basic_properties(): void
    {
        // Arrange
        $param = new Param(['output', 'o'], 'Output file', 'STRING');

        // Act
        $names = $param->getNames();
        $primary = $param->getPrimaryName();
        $description = $param->getDescription();

        // Assert
        $this->assertSame(['output', 'o'], $names);
        $this->assertSame('output', $primary);
        $this->assertSame('Output file', $description);
    }

    public function test_it_accepts_values(): void
    {
        // Arrange
        $param = new Param(['test'], 'Test', 'STRING');

        // Act
        $acceptsValue = $param->acceptsValue();

        // Assert
        $this->assertTrue($acceptsValue);
    }

    public function test_required_defaults_to_false(): void
    {
        // Arrange
        $param = new Param(['test'], 'Test', 'STRING');

        // Act
        $isRequired = $param->isRequired();
        $default = $param->getDefault();

        // Assert
        $this->assertFalse($isRequired);
        $this->assertNull($default);
    }

    public function test_it_allows_required_configuration(): void
    {
        // Arrange
        $param = new Param(['test'], 'Test', 'STRING', true);

        // Act
        $isRequired = $param->isRequired();

        // Assert
        $this->assertTrue($isRequired);
    }

    public function test_it_allows_default_value(): void
    {
        // Arrange
        $default = 'default_value';
        $param = new Param(['test'], 'Test', 'STRING', false, $default);

        // Act
        $result = $param->getDefault();

        // Assert
        $this->assertSame($default, $result);
    }

    public function test_it_validates_using_type_registry(): void
    {
        // Arrange
        $param = new Param(['count'], 'Count', 'INT');
        $registry = new TypeRegistry();

        // Act
        $result = $param->validateValue('42', $registry);

        // Assert
        $this->assertSame(42, $result);
    }

    public function test_it_throws_on_invalid_type_validation(): void
    {
        // Arrange
        $param = new Param(['count'], 'Count', 'INT');
        $registry = new TypeRegistry();

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $param->validateValue('not-an-int', $registry);
    }

    public function test_it_applies_filter_closure(): void
    {
        // Arrange
        $filter = function (string $value): string {
            return strtoupper($value);
        };
        $param = new Param(['name'], 'Name', 'STRING', false, null, $filter);
        $registry = new TypeRegistry();

        // Act
        $result = $param->validateValue('hello', $registry);

        // Assert
        $this->assertSame('HELLO', $result);
    }

    public function test_it_throws_when_filter_rejects_value(): void
    {
        // Arrange
        $filter = function (string $value): string {
            if (strlen($value) < 5) {
                throw new \Exception('Too short');
            }
            return $value;
        };
        $param = new Param(['name'], 'Name', 'STRING', false, null, $filter);
        $registry = new TypeRegistry();

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Filter rejected value for 'name': Too short");

        // Act
        $param->validateValue('hi', $registry);
    }
}
