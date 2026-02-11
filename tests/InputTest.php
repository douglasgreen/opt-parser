<?php

declare(strict_types=1);

namespace Tests\Unit;

use DouglasGreen\OptParser\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Input::class)]
#[Small]
final class InputTest extends TestCase
{
    public function test_it_stores_and_retrieves_command(): void
    {
        // Arrange
        $input = new Input('add', [], []);

        // Act
        $command = $input->getCommand();

        // Assert
        $this->assertSame('add', $command);
    }

    public function test_it_allows_null_command(): void
    {
        // Arrange
        $input = new Input(null, [], []);

        // Act
        $command = $input->getCommand();

        // Assert
        $this->assertNull($command);
    }

    public function test_it_retrieves_option_value(): void
    {
        // Arrange
        $options = ['verbose' => true, 'output' => '/tmp/file.txt'];
        $input = new Input(null, $options, []);

        // Act
        $verbose = $input->get('verbose');
        $output = $input->get('output');

        // Assert
        $this->assertTrue($verbose);
        $this->assertSame('/tmp/file.txt', $output);
    }

    public function test_it_returns_null_for_missing_option(): void
    {
        // Arrange
        $input = new Input(null, [], []);

        // Act
        $result = $input->get('nonexistent');

        // Assert
        $this->assertNull($result);
    }

    public function test_it_checks_option_existence(): void
    {
        // Arrange
        $input = new Input(null, ['flag' => false], []);

        // Act
        $hasExisting = $input->has('flag');
        $hasMissing = $input->has('missing');

        // Assert
        $this->assertTrue($hasExisting);
        $this->assertFalse($hasMissing);
    }

    public function test_it_distinguishes_between_null_and_missing(): void
    {
        // Arrange
        $input = new Input(null, ['explicit_null' => null], []);

        // Act
        $hasKey = $input->has('explicit_null');
        $value = $input->get('explicit_null');

        // Assert
        $this->assertTrue($hasKey);
        $this->assertNull($value);
    }

    public function test_it_returns_non_options(): void
    {
        // Arrange
        $nonOptions = ['file1.txt', 'file2.txt'];
        $input = new Input(null, [], $nonOptions);

        // Act
        $result = $input->getNonoptions();

        // Assert
        $this->assertSame($nonOptions, $result);
    }

    public function test_it_converts_to_array(): void
    {
        // Arrange
        $options = ['key' => 'value', 'count' => 42];
        $input = new Input('cmd', $options, []);

        // Act
        $array = $input->toArray();

        // Assert
        $this->assertSame($options, $array);
    }
}
