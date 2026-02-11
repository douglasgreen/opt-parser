<?php

declare(strict_types=1);

namespace Tests\Unit;

use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\UsageDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsageDefinition::class)]
#[Small]
final class UsageDefinitionTest extends TestCase
{
    public function test_it_allows_any_options_when_no_usage_defined(): void
    {
        // Arrange
        $definition = new UsageDefinition();

        // Act & Assert (should not throw)
        $definition->validate('unknown', ['any' => 'value']);
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_it_validates_allowed_options(): void
    {
        // Arrange
        $definition = new UsageDefinition();
        $definition->addUsage('add', ['name', 'force']);

        // Act & Assert (should not throw)
        $definition->validate('add', ['name' => 'test', 'force' => true]);
        $this->assertTrue(true);
    }

    public function test_it_throws_on_disallowed_option(): void
    {
        // Arrange
        $definition = new UsageDefinition();
        $definition->addUsage('add', ['name']);

        // Assert
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage("Option 'delete' is not allowed with command 'add'");

        // Act
        $definition->validate('add', ['name' => 'test', 'delete' => true]);
    }

    public function test_it_ignores_non_options_key(): void
    {
        // Arrange
        $definition = new UsageDefinition();
        $definition->addUsage('test', ['verbose']);

        // Act & Assert (should not throw)
        $definition->validate('test', ['verbose' => true, '_' => ['file.txt']]);
        $this->assertTrue(true);
    }

    public function test_it_ignores_command_key_in_options(): void
    {
        // Arrange
        $definition = new UsageDefinition();
        $definition->addUsage('install', ['verbose']);

        // Act & Assert (should not throw)
        $definition->validate('install', ['verbose' => true, 'install' => true]);
        $this->assertTrue(true);
    }

    public function test_it_accumulates_multiple_add_usage_calls(): void
    {
        // Arrange
        $definition = new UsageDefinition();
        $definition->addUsage('cmd', ['opt1']);
        $definition->addUsage('cmd', ['opt2']);

        // Act & Assert
        $definition->validate('cmd', ['opt1' => true, 'opt2' => true]);
        $this->assertTrue(true);
    }
}
