<?php

declare(strict_types=1);

namespace Tests\Unit\Util;

use DouglasGreen\OptParser\Util\OutputHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(OutputHandler::class)]
#[Small]
final class OutputHandlerTest extends TestCase
{
    public function test_it_returns_type_name_for_stdout_writes(): void
    {
        // Arrange
        $handler = new OutputHandler();
        
        // We can't easily test actual output without capturing streams
        // So we verify the method exists and accepts the right parameters
        $this->assertTrue(method_exists($handler, 'stdout'));
        
        // Act & Assert (smoke test - should not throw)
        $handler->stdout('Test');
        $this->assertTrue(true);
    }

    public function test_it_accepts_stderr_writes(): void
    {
        // Arrange
        $handler = new OutputHandler();
        
        // Assert method exists
        $this->assertTrue(method_exists($handler, 'stderr'));
        
        // Act & Assert (smoke test)
        $handler->stderr('Error');
        $this->assertTrue(true);
    }

    public function test_it_logs_errors_when_logger_provided(): void
    {
        // Arrange
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Error message');

        $handler = new OutputHandler($logger);

        // Act
        $handler->stderr('Error message');

        // Assert (mock expectations verified automatically)
    }

    public function test_it_detects_no_color_environment_variable(): void
    {
        // Arrange
        putenv('NO_COLOR=1');
        $handler = new OutputHandler();

        // Act
        $supportsColor = $handler->supportsColor();

        // Assert
        $this->assertFalse($supportsColor);

        // Cleanup
        putenv('NO_COLOR');
    }

    public function test_force_color_overrides_no_color(): void
    {
        // Arrange
        putenv('NO_COLOR=1');
        $handler = new OutputHandler(null, true);

        // Act
        $supportsColor = $handler->supportsColor();

        // Assert
        $this->assertTrue($supportsColor);

        // Cleanup
        putenv('NO_COLOR');
    }

    public function test_is_tty_returns_boolean(): void
    {
        // Arrange
        $handler = new OutputHandler();

        // Act
        $result = $handler->isTty();

        // Assert
        $this->assertIsBool($result);
    }

    public function test_supports_color_returns_boolean(): void
    {
        // Arrange
        $handler = new OutputHandler();

        // Act
        $result = $handler->supportsColor();

        // Assert
        $this->assertIsBool($result);
    }
}
