<?php

declare(strict_types=1);

namespace Tests\Unit\Option;

use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Type\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Flag::class)]
#[Small]
final class FlagTest extends TestCase
{
    public function test_it_stores_names_and_description(): void
    {
        // Arrange
        $flag = new Flag(['verbose', 'v'], 'Verbose output');

        // Act
        $names = $flag->getNames();
        $primary = $flag->getPrimaryName();

        // Assert
        $this->assertSame(['verbose', 'v'], $names);
        $this->assertSame('verbose', $primary);
    }

    public function test_it_does_not_accept_values(): void
    {
        // Arrange
        $flag = new Flag(['debug'], 'Debug mode');

        // Act
        $acceptsValue = $flag->acceptsValue();

        // Assert
        $this->assertFalse($acceptsValue);
    }

    public function test_default_is_false(): void
    {
        // Arrange
        $flag = new Flag(['test'], 'Test');

        // Act
        $default = $flag->getDefault();

        // Assert
        $this->assertFalse($default);
    }

    public function test_it_validates_to_true(): void
    {
        // Arrange
        $flag = new Flag(['test'], 'Test');
        $registry = new TypeRegistry();

        // Act
        $result = $flag->validateValue('ignored', $registry);

        // Assert
        $this->assertTrue($result);
    }
}
