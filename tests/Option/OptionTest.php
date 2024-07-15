<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser\Option;

use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\Flag;
use DouglasGreen\OptParser\Option\Param;
use DouglasGreen\OptParser\Option\Term;
use DouglasGreen\Utility\Data\ArgumentException;
use DouglasGreen\Utility\Data\ValueException;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testCommandCreation(): void
    {
        $command = new Command('add', 'Add a new user', ['a']);
        $this->assertSame('add', $command->getName());
        $this->assertSame('Add a new user', $command->getDesc());
        $this->assertSame(['a'], $command->getAliases());
        $this->assertSame('add', $command->write());
    }

    public function testFlagCreation(): void
    {
        $flag = new Flag('help', 'Display program help', ['h']);
        $this->assertSame('help', $flag->getName());
        $this->assertSame('Display program help', $flag->getDesc());
        $this->assertSame(['h'], $flag->getAliases());
        $this->assertSame('--help', $flag->write());
    }

    public function testInvalidAlias(): void
    {
        $this->expectException(ValueException::class);
        new Command('invalid_command', 'Invalid Command', ['bad_alias']);
    }

    public function testOptionMatchName(): void
    {
        $flag = new Flag('help', 'Display program help', ['h']);
        $this->assertTrue($flag->matchName('help'));
        $this->assertTrue($flag->matchName('h'));
        $this->assertFalse($flag->matchName('x'));
    }

    public function testOptionMatchValue(): void
    {
        $param = new Param('email', 'User email', ['e'], 'EMAIL');
        $this->assertSame('test@example.com', $param->matchValue('test@example.com'));

        $this->expectException(ArgumentException::class);
        $param->matchValue('invalid-email');
    }

    public function testParamCreation(): void
    {
        $param = new Param('password', 'Password for the user', ['p'], 'STRING');
        $this->assertSame('password', $param->getName());
        $this->assertSame('Password for the user', $param->getDesc());
        $this->assertSame(['p'], $param->getAliases());
        $this->assertSame('STRING', $param->getArgType());
        $this->assertSame('--password=STRING', $param->write());
    }

    public function testTermCreation(): void
    {
        $term = new Term('username', 'Username of the user', 'STRING');
        $this->assertSame('username', $term->getName());
        $this->assertSame('Username of the user', $term->getDesc());
        $this->assertSame('STRING', $term->getArgType());
        $this->assertSame('username:STRING', $term->write());
    }
}
