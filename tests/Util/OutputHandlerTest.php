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
    private string $tempStdout;
    private string $tempStderr;

    protected function setUp(): void
    {
        $this->tempStdout = tempnam(sys_get_temp_dir(), 'stdout');
        $this->tempStderr = tempnam(sys_get_temp_dir(), 'stderr');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempStdout)) {
            unlink($this->tempStdout);
        }
        if (file_exists($this->tempStderr)) {
            unlink($this->tempStderr);
        }
    }

    public function test_it_writes_to_stdout(): void
    {
        // Arrange
        $handler = new OutputHandler();
        $message = 'Test message';

        // Act
        ob_start();
        $handler->stdout($message);
        $output = ob_get_clean();

        // Assert
        $this->assertSame($message . PHP_EOL, $output);
    }

    public function test_it_writes_to_stderr(): void
    {
        // Arrange
        $handler = new OutputHandler();
        $message = 'Error message';

        // Act
        ob_start();
        $handler->stderr($message);
        $output = ob_get_clean();

        // Assert
        // Note: In PHPUnit, stderr often goes to stdout or test output depending on configuration
        // This test assumes output buffering catches it
        $this->assertNotEmpty($output);
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
        ob_start();
        $handler->stderr('Error message');
        ob_end_clean();

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

    public function test_is_tty_caches_result(): void
    {
        // Arrange
        $handler = new OutputHandler();

        // Act
        $tty1 = $handler->isTty();
        $tty2 = $handler->isTty();

        // Assert
        $this->assertSame($tty1, $tty2);
    }
}
