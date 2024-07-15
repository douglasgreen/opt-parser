<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser;

use DouglasGreen\OptParser\OptHandler;
use DouglasGreen\OptParser\Usage;
use DouglasGreen\Utility\Data\ValueException;
use PHPUnit\Framework\TestCase;

class UsageTest extends TestCase
{
    protected OptHandler $optHandler;

    public function testCreateUsage(): void
    {
        $usage = new Usage($this->optHandler, ['add', 'username', 'password', 'verbose']);
        $this->assertInstanceOf(Usage::class, $usage);
    }

    public function testDuplicateOptionsEliminated(): void
    {
        $usage = new Usage($this->optHandler, [
            'add',
            'username',
            'username',
            'password',
            'verbose',
            'verbose',
        ]);

        $terms = $usage->getOptions('term');
        $this->assertCount(1, $terms); // 'username' should only appear once

        $flags = $usage->getOptions('flag');
        $this->assertCount(1, $flags); // 'verbose' should only appear once
    }

    public function testGetOptions(): void
    {
        $usage = new Usage($this->optHandler, ['add', 'username', 'password', 'verbose']);

        $commands = $usage->getOptions('command');
        $terms = $usage->getOptions('term');
        $params = $usage->getOptions('param');
        $flags = $usage->getOptions('flag');

        $this->assertSame(['add'], $commands);
        $this->assertSame(['username'], $terms);
        $this->assertSame(['password'], $params);
        $this->assertSame(['verbose'], $flags);
    }

    public function testGetOptionsInvalidType(): void
    {
        $this->expectException(ValueException::class);

        $usage = new Usage($this->optHandler, ['add', 'username', 'password', 'verbose']);
        $usage->getOptions('invalid_type');
    }

    public function testMultipleCommandsException(): void
    {
        $this->expectException(ValueException::class);

        new Usage($this->optHandler, ['add', 'delete']);
    }

    public function testWriteUsage(): void
    {
        $usage = new Usage($this->optHandler, ['add', 'username', 'password', 'verbose']);
        $programName = 'test_program';

        $output = $usage->write($programName);

        $this->assertStringContainsString('test_program', $output);
        $this->assertStringContainsString('add', $output);
        $this->assertStringContainsString('username:STRING', $output);
        $this->assertStringContainsString('--password=STRING', $output);
        $this->assertStringContainsString('--verbose', $output);
    }

    protected function setUp(): void
    {
        $this->optHandler = new OptHandler();
        $this->optHandler->addCommand(['add', 'a'], 'Add a new user');
        $this->optHandler->addTerm('username', 'STRING', 'Username of the user');
        $this->optHandler->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $this->optHandler->addFlag(['verbose', 'v'], 'Enable verbose output');
    }
}
