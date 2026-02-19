<?php

declare(strict_types=1);

namespace Tests\Unit;

use DouglasGreen\OptParser\OptParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptParser::class)]
#[Small]
final class OptParserTest extends TestCase
{
    public function test_it_uses_first_long_name_as_key_for_param(): void
    {
        $parser = new OptParser('test', 'Test app');
        $parser->addParam(['p', 'pass'], 'string', 'Password');

        $input = $parser->parse(['-p', 'secret']);

        $this->assertSame('secret', $input->get('pass'));
        $this->assertFalse($input->has('p'));
    }

    public function test_it_uses_first_long_name_as_key_for_flag(): void
    {
        $parser = new OptParser('test', 'Test app');
        $parser->addFlag(['v', 'verbose'], 'Verbose');

        $input = $parser->parse(['-v']);

        $this->assertTrue($input->get('verbose'));
        $this->assertFalse($input->has('v'));
    }

    public function test_it_uses_short_name_as_key_if_no_long_name(): void
    {
        $parser = new OptParser('test', 'Test app');
        $parser->addParam(['p'], 'string', 'Param');

        $input = $parser->parse(['-p', 'value']);

        $this->assertSame('value', $input->get('p'));
    }

    public function test_it_prioritizes_first_long_name_among_multiple(): void
    {
        $parser = new OptParser('test', 'Test app');
        // Names: short 'p', long 'pass', long 'password'
        $parser->addParam(['p', 'pass', 'password'], 'string', 'Password');

        $input = $parser->parse(['--password', 'secret']);

        // 'pass' is the first long name, so it should be the key
        $this->assertSame('secret', $input->get('pass'));
        $this->assertFalse($input->has('password'));
    }
}
