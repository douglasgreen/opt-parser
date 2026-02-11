<?php

declare(strict_types=1);

namespace Tests\Unit\Option;

use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Option\OptionRegistry;
use DouglasGreen\OptParser\Option\Term;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptionRegistry::class)]
#[Small]
final class OptionRegistryTest extends TestCase
{
    public function test_it_registers_and_retrieves_option(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $flag = new Flag(['verbose', 'v'], 'Verbose');

        // Act
        $registry->register($flag);
        $retrieved = $registry->get('verbose');
        $retrievedByAlias = $registry->get('v');

        // Assert
        $this->assertSame($flag, $retrieved);
        $this->assertSame($flag, $retrievedByAlias);
    }

    public function test_it_checks_option_existence(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $registry->register(new Flag(['test'], 'Test'));

        // Act
        $exists = $registry->has('test');
        $missing = $registry->has('missing');

        // Assert
        $this->assertTrue($exists);
        $this->assertFalse($missing);
    }

    public function test_it_throws_on_unknown_option(): void
    {
        // Arrange
        $registry = new OptionRegistry();

        // Assert
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('Unknown option: unknown');

        // Act
        $registry->get('unknown');
    }

    public function test_it_prevents_duplicate_registration(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $registry->register(new Flag(['test'], 'First'));

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option name conflict: test');

        // Act
        $registry->register(new Flag(['test'], 'Second'));
    }

    public function test_it_prevents_alias_conflict(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $registry->register(new Flag(['verbose', 'v'], 'Verbose'));

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option name conflict: v');

        // Act
        $registry->register(new Flag(['v'], 'Another'));
    }

    public function test_it_rejects_empty_names(): void
    {
        // Arrange
        $registry = new OptionRegistry();

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option must have at least one name');

        // Act
        $registry->register(new Flag([], 'Empty'));
    }

    public function test_it_returns_all_unique_options(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $flag1 = new Flag(['verbose', 'v'], 'Verbose');
        $flag2 = new Flag(['debug', 'd'], 'Debug');

        $registry->register($flag1);
        $registry->register($flag2);

        // Act
        $all = $registry->getAll();

        // Assert
        $this->assertCount(2, $all);
        $this->assertContains($flag1, $all);
        $this->assertContains($flag2, $all);
    }

    public function test_it_gets_all_commands(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $cmd1 = new Command(['add', 'a'], 'Add');
        $cmd2 = new Command(['delete'], 'Delete');
        $flag = new Flag(['verbose'], 'Verbose');

        $registry->register($cmd1);
        $registry->register($cmd2);
        $registry->register($flag);

        // Act
        $commands = $registry->getCommands();

        // Assert
        $this->assertCount(2, $commands);
        $this->assertContains($cmd1, $commands);
        $this->assertContains($cmd2, $commands);
    }

    public function test_it_gets_all_terms(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $term1 = new Term('file', 'File path', 'STRING');
        $term2 = new Term('count', 'Count', 'INT');
        $flag = new Flag(['verbose'], 'Verbose');

        $registry->register($term1);
        $registry->register($term2);
        $registry->register($flag);

        // Act
        $terms = $registry->getTerms();

        // Assert
        $this->assertCount(2, $terms);
        $this->assertContains($term1, $terms);
        $this->assertContains($term2, $terms);
    }

    public function test_it_normalizes_names_to_lowercase(): void
    {
        // Arrange
        $registry = new OptionRegistry();
        $registry->register(new Flag(['Verbose', 'V'], 'Verbose'));

        // Act
        $lower = $registry->has('verbose');
        $upper = $registry->has('VERBOSE');
        $mixed = $registry->has('Verbose');

        // Assert
        $this->assertTrue($lower);
        $this->assertTrue($upper);
        $this->assertTrue($mixed);
    }
}
