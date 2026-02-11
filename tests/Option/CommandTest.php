<?php

declare(strict_types=1);

namespace Tests\Unit\Option;

use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Type\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Command::class)]
#[Small]
final class CommandTest extends TestCase
{
    public function test_it_stores_names_and_description(): void
    {
        // Arrange
        $command = new Command(['add', 'a'], 'Add item');

        // Act
        $names = $command->getNames();
        $primary = $command->getPrimaryName();
        $description = $command->getDescription();

        // Assert
        $this->assertSame(['add', 'a'], $names);
        $this->assertSame('add', $primary);
        $this->assertSame('Add item', $description);
    }

    public function test_it_does_not_accept_values(): void
    {
        // Arrange
        $command = new Command(['test'], 'Test command');

        // Act
        $acceptsValue = $command->acceptsValue();

        // Assert
        $this->assertFalse($acceptsValue);
    }

    public function test_it_is_not_required(): void
    {
        // Arrange
        $command = new Command(['test'], 'Test');

        // Act
        $isRequired = $command->isRequired();

        // Assert
        $this->assertFalse($isRequired);
    }

    public function test_default_is_null(): void
    {
        // Arrange
        $command = new Command(['test'], 'Test');

        // Act
        $default = $command->getDefault();

        // Assert
        $this->assertNull($default);
    }

    public function test_it_validates_value_as_identity(): void
    {
        // Arrange
        $command = new Command(['test'], 'Test');
        $registry = new TypeRegistry();

        // Act
        $result = $command->validateValue('anything', $registry);

        // Assert
        $this->assertSame('anything', $result);
    }
}
