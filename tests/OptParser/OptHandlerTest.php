<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser;

use DouglasGreen\OptParser\OptHandler;
use DouglasGreen\Utility\Data\ValueException;
use PHPUnit\Framework\TestCase;

class OptHandlerTest extends TestCase
{
    public function testAddCommand(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addCommand(['add', 'a'], 'Add a new user');

        $this->assertTrue($optHandler->hasOptionType('command'));
        $option = $optHandler->getOption('add');
        $this->assertSame('add', $option->getName());
        $this->assertSame('Add a new user', $option->getDesc());
        $this->assertSame(['a'], $option->getAliases());
    }

    public function testAddFlag(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addFlag(['verbose', 'v'], 'Enable verbose output');

        $this->assertTrue($optHandler->hasOptionType('flag'));
        $option = $optHandler->getOption('verbose');
        $this->assertSame('verbose', $option->getName());
        $this->assertSame('Enable verbose output', $option->getDesc());
        $this->assertSame(['v'], $option->getAliases());
    }

    public function testAddParam(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addParam(['password', 'p'], 'STRING', 'Password for the user');

        $this->assertTrue($optHandler->hasOptionType('param'));
        $option = $optHandler->getOption('password');
        $this->assertSame('password', $option->getName());
        $this->assertSame('Password for the user', $option->getDesc());
        $this->assertSame(['p'], $option->getAliases());
        $this->assertSame('STRING', $option->getArgType());
    }

    public function testAddTerm(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addTerm('username', 'STRING', 'Username of the user');

        $this->assertTrue($optHandler->hasOptionType('term'));
        $option = $optHandler->getOption('username');
        $this->assertSame('username', $option->getName());
        $this->assertSame('Username of the user', $option->getDesc());
        $this->assertSame('STRING', $option->getArgType());
    }

    public function testDuplicateAliasException(): void
    {
        $this->expectException(ValueException::class);

        $optHandler = new OptHandler();
        $optHandler->addFlag(['verbose', 'v'], 'Enable verbose output');
        $optHandler->addFlag(['verbose', 'v'], 'Another verbose flag');
    }

    public function testGetOptionType(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addCommand(['add', 'a'], 'Add a new user');
        $optHandler->addFlag(['verbose', 'v'], 'Enable verbose output');
        $optHandler->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $optHandler->addTerm('username', 'STRING', 'Username of the user');

        $this->assertSame('command', $optHandler->getOptionType('add'));
        $this->assertSame('flag', $optHandler->getOptionType('verbose'));
        $this->assertSame('param', $optHandler->getOptionType('password'));
        $this->assertSame('term', $optHandler->getOptionType('username'));
    }

    public function testInvalidOptionTypeException(): void
    {
        $this->expectException(ValueException::class);

        $optHandler = new OptHandler();
        $optHandler->getOptionType('nonexistent');
    }

    public function testWriteOptionBlock(): void
    {
        $optHandler = new OptHandler();
        $optHandler->addCommand(['add', 'a'], 'Add a new user');
        $optHandler->addFlag(['verbose', 'v'], 'Enable verbose output');
        $optHandler->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $optHandler->addTerm('username', 'STRING', 'Username of the user');

        $output = $optHandler->writeOptionBlock();

        $this->assertStringContainsString('Commands:', $output);
        $this->assertStringContainsString('add | a', $output);
        $this->assertStringContainsString('Flags:', $output);
        $this->assertStringContainsString('--verbose | -v', $output);
        $this->assertStringContainsString('Parameters:', $output);
        $this->assertStringContainsString('--password | -p = STRING', $output);
        $this->assertStringContainsString('Terms:', $output);
        $this->assertStringContainsString('username: STRING', $output);
    }
}
