<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser;

use DouglasGreen\OptParser\ArgParser;
use PHPUnit\Framework\TestCase;

class ArgParserTest extends TestCase
{
    public function testGetMarkedOptions(): void
    {
        $argv = [
            'script.php',
            'file',
            '-1',
            '-a',
            '-b',
            'arg1',
            '--foo=bar',
            '--baz-fez',
            '-2',
            '--',
            'non1',
            'non2',
        ];
        $argParser = new ArgParser($argv);

        $expected = [
            'a' => '',
            'b' => 'arg1',
            'foo' => 'bar',
            'baz-fez' => '-2',
        ];

        $this->assertSame($expected, $argParser->getMarkedOptions());
    }

    public function testGetNonOptions(): void
    {
        $argv = [
            'script.php',
            'file',
            '-1',
            '-a',
            '-b',
            'arg1',
            '--foo=bar',
            '--baz-fez',
            '-2',
            '--',
            'non1',
            'non2',
        ];
        $argParser = new ArgParser($argv);

        $expected = ['non1', 'non2'];

        $this->assertSame($expected, $argParser->getNonOptions());
    }

    public function testGetUnmarkedOptions(): void
    {
        $argv = [
            'script.php',
            'file',
            '-1',
            '-a',
            '-b',
            'arg1',
            '--foo=bar',
            '--baz-fez',
            '-2',
            '--',
            'non1',
            'non2',
        ];
        $argParser = new ArgParser($argv);

        $expected = ['file', '-1'];

        $this->assertSame($expected, $argParser->getUnmarkedOptions());
    }
}
