<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Util;

use Psr\Log\LoggerInterface;

/**
 * Handles stdout/stderr output with TTY detection and color support.
 *
 * Provides a centralized output mechanism for command-line applications with
 * automatic detection of terminal capabilities. Supports the NO_COLOR environment
 * variable convention and optional PSR-3 logger integration for error tracking.
 *
 * @package DouglasGreen\OptParser\Util
 *
 * @api
 *
 * @since 1.0.0
 *
 * @example
 * ```php
 * // Basic usage
 * $output = new OutputHandler();
 * $output->stdout('Processing complete');
 * $output->stderr('Warning: deprecated option used');
 *
 * // With logger integration
 * $logger = new Monolog\Logger('app');
 * $output = new OutputHandler($logger);
 * $output->stderr('Error message'); // Also logs to $logger
 *
 * // Force color output for piping
 * $output = new OutputHandler(null, true);
 * if ($output->supportsColor()) {
 *     echo "\033[32mGreen text\033[0m";
 * }
 * ```
 */
final class OutputHandler
{
    /**
     * Cached TTY detection result for stdout.
     *
     * Lazily initialized on first call to isTty().
     *
     * @var bool
     */
    private bool $isTty;

    /**
     * Whether color output should be disabled per NO_COLOR convention.
     *
     * @var bool
     */
    private readonly bool $noColor;

    /**
     * Constructs the output handler with optional logger and color forcing.
     *
     * @param LoggerInterface|null $logger Optional PSR-3 logger for stderr messages
     * @param bool $forceColor Force color output regardless of TTY detection
     */
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $forceColor = false,
    ) {
        $this->noColor = $this->detectNoColor();
    }

    /**
     * Writes a message to standard output with a trailing newline.
     *
     * @param string $message The message to write to stdout
     */
    public function stdout(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    /**
     * Writes a message to standard error with a trailing newline.
     *
     * If a logger was provided during construction, the message is also
     * logged at error level for centralized error tracking.
     *
     * @param string $message The message to write to stderr
     */
    public function stderr(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }

    /**
     * Determines if stdout is connected to a terminal (TTY).
     *
     * Uses posix_isatty() for detection. Results are cached after the
     * first call to avoid repeated system calls.
     *
     * @return bool True if stdout is a TTY, false if piped or redirected
     */
    public function isTty(): bool
    {
        if (!isset($this->isTty)) {
            $this->isTty = posix_isatty(STDOUT);
        }

        return $this->isTty;
    }

    /**
     * Determines whether color escape sequences should be emitted.
     *
     * Returns true if:
     * - Force color mode is enabled, OR
     * - NO_COLOR is not set AND stdout is a TTY
     *
     * @return bool True if color output is supported/enabled
     */
    public function supportsColor(): bool
    {
        if ($this->forceColor) {
            return true;
        }

        return !$this->noColor && $this->isTty();
    }

    /**
     * Detects the NO_COLOR environment variable.
     *
     * Follows the NO_COLOR convention (https://no-color.org) where the
     * presence of the environment variable (regardless of value) signals
     * that color output should be disabled.
     *
     * @return bool True if NO_COLOR is set, false otherwise
     */
    private function detectNoColor(): bool
    {
        return getenv('NO_COLOR') !== false;
    }
}
