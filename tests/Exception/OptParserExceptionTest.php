<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use DouglasGreen\OptParser\Exception\OptParserException;
use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Exception\ValidationException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptParserException::class)]
#[CoversClass(UsageException::class)]
#[Small]
final class OptParserExceptionTest extends TestCase
{
    public function test_usage_exception_is_client_error(): void
    {
        // Arrange
        $exception = new UsageException('Test message');

        // Act
        $isClientError = $exception->isClientError();
        $exitCode = $exception->getExitCode();

        // Assert
        $this->assertTrue($isClientError);
        $this->assertSame(2, $exitCode);
    }

    public function test_usage_exception_allows_custom_exit_code(): void
    {
        // Arrange
        $customExitCode = 64;
        $exception = new UsageException('Test message', $customExitCode);

        // Act
        $exitCode = $exception->getExitCode();

        // Assert
        $this->assertSame($customExitCode, $exitCode);
    }

    public function test_usage_exception_preserves_previous_exception(): void
    {
        // Arrange
        $previous = new Exception('Original error');
        $exception = new UsageException('Wrapped error', 2, $previous);

        // Act
        $result = $exception->getPrevious();

        // Assert
        $this->assertSame($previous, $result);
    }

    public function test_validation_exception_is_not_client_error(): void
    {
        // Arrange
        $exception = new ValidationException('Invalid value');

        // Act
        $isClientError = $exception->isClientError();
        $exitCode = $exception->getExitCode();

        // Assert
        $this->assertFalse($isClientError);
        $this->assertSame(1, $exitCode);
    }

    public function test_validation_exception_allows_custom_exit_code(): void
    {
        // Arrange
        $customExitCode = 78;
        $exception = new ValidationException('Config error', $customExitCode);

        // Act & Assert
        $this->assertSame($customExitCode, $exception->getExitCode());
    }
}
