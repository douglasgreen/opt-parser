<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser;

use DouglasGreen\OptParser\OptParser;
use DouglasGreen\Utility\Data\ValueException;
use PHPUnit\Framework\TestCase;

class OptParserTest extends TestCase
{
    protected OptParser $optParser;

    public function testAddCommand(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');

        $this->assertTrue($this->optParser->getOptHandler()->hasOptionType('command'));
    }

    public function testAddFlag(): void
    {
        $this->optParser->addFlag(['verbose', 'v'], 'Enable verbose output');

        $this->assertTrue($this->optParser->getOptHandler()->hasOptionType('flag'));
    }

    public function testAddParam(): void
    {
        $this->optParser->addParam(['password', 'p'], 'STRING', 'Password for the user');

        $this->assertTrue($this->optParser->getOptHandler()->hasOptionType('param'));
    }

    public function testAddTerm(): void
    {
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');

        $this->assertTrue($this->optParser->getOptHandler()->hasOptionType('term'));
    }

    public function testAddUsage(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');
        $this->optParser->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $this->optParser->addUsage('add', ['username', 'password']);

        $this->assertCount(2, $this->optParser->getUsages()); // Includes default help usage
    }

    public function testAddUsageAll(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');
        $this->optParser->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $this->optParser->addFlag(['verbose', 'v'], 'Enable verbose output');
        $this->optParser->addUsageAll();

        $this->assertCount(2, $this->optParser->getUsages()); // Includes default help usage
    }

    public function testAddUsageAllEmpty(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addUsageAll();

        $this->assertCount(2, $this->optParser->getUsages()); // Includes default help usage
    }

    public function testAddUsageMixed(): void
    {
        // Add usage without command.
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');
        $this->optParser->addUsageAll();

        // Add usage with command.
        $this->expectException(ValueException::class);
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
    }

    public function testAddUsageTwoCommands(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addCommand(['delete', 'd'], 'Delete a user');
        $this->expectException(ValueException::class);
        $this->optParser->addUsageAll();
    }

    public function testHelp(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');

        $this->expectOutputRegex('/Usage:/');
        $this->optParser->parse(['test', '--help']);
    }

    public function testParse(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');
        $this->optParser->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $this->optParser->addUsage('add', ['username', 'password']);

        $optResult = $this->optParser->parse(['test', 'add', 'john', '--password=secret']);

        $this->assertSame('add', $optResult->getCommand());
        $this->assertSame('john', $optResult->get('username'));
        $this->assertSame('secret', $optResult->get('password'));
    }

    public function testParseInvalidCommand(): void
    {
        $this->optParser->addCommand(['add', 'a'], 'Add a new user');
        $this->optParser->addTerm('username', 'STRING', 'Username of the user');
        $this->optParser->addParam(['password', 'p'], 'STRING', 'Password for the user');
        $this->optParser->addUsage('add', ['username', 'password']);

        $optResult = $this->optParser->parse(['test', 'remove', 'john']);

        /** @phpstan-ignore method.alreadyNarrowedType */
        $this->assertNotNull($optResult->getErrors());
    }

    protected function setUp(): void
    {
        $this->optParser = new OptParser('test', 'Test program', OptParser::SKIP_RESULT_CHECK);
    }
}
