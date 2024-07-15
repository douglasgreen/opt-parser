<?php

declare(strict_types=1);

namespace DouglasGreen\Tests\OptParser;

use DouglasGreen\OptParser\OptResult;
use PHPUnit\Framework\TestCase;

class OptResultTest extends TestCase
{
    public function testAddError(): void
    {
        $optResult = new OptResult([]);
        $optResult->addError('Missing term: "username"');

        $this->assertContains('Missing term: "username"', $optResult->getErrors());
    }

    public function testGetMatchResults(): void
    {
        $optResult = new OptResult([]);
        $optResult->setCommand('add', true);
        $optResult->setFlag('verbose', true);
        $optResult->setParam('password', 'secret');
        $optResult->setTerm('username', 'john');

        $matchResults = $optResult->getMatchResults();

        $this->assertEquals(true, $matchResults['add']);
        $this->assertEquals(true, $matchResults['verbose']);
        $this->assertSame('secret', $matchResults['password']);
        $this->assertSame('john', $matchResults['username']);
    }

    public function testMagicGet(): void
    {
        $optResult = new OptResult([]);
        $optResult->setParam('file-path', '/path/to/file');

        /** @phpstan-ignore-next-line Magic getter */
        $this->assertSame('/path/to/file', $optResult->filePath);
    }

    public function testNonOptions(): void
    {
        $nonOptions = ['file1', 'file2'];
        $optResult = new OptResult($nonOptions);

        $this->assertSame($nonOptions, $optResult->getNonoptions());
    }

    public function testSetAndGetCommand(): void
    {
        $optResult = new OptResult([]);
        $optResult->setCommand('add', true);

        $this->assertSame('add', $optResult->getCommand());
        $this->assertTrue($optResult->get('add'));
    }

    public function testSetAndGetFlag(): void
    {
        $optResult = new OptResult([]);
        $optResult->setFlag('verbose', true);

        $this->assertTrue($optResult->get('verbose'));
    }

    public function testSetAndGetParam(): void
    {
        $optResult = new OptResult([]);
        $optResult->setParam('password', 'secret');

        $this->assertSame('secret', $optResult->get('password'));
    }

    public function testSetAndGetTerm(): void
    {
        $optResult = new OptResult([]);
        $optResult->setTerm('username', 'john');

        $this->assertSame('john', $optResult->get('username'));
    }
}
