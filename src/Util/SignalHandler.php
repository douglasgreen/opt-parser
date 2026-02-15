<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Util;

/**
 * Handles POSIX signals for graceful shutdown.
 *
 * Registers signal handlers to enable clean termination of command-line
 * applications. When a SIGINT (Ctrl+C) is received, outputs an interruption
 * message and exits with the appropriate signal-specific exit code.
 *
 * Requires the PCNTL extension for signal handling. Gracefully degrades
 * when the extension is not available (common in Docker Alpine images
 * or restricted environments).
 *
 * @package DouglasGreen\OptParser\Util
 *
 * @api
 *
 * @since 1.0.0
 * @see OutputHandler For output handling during signal events
 *
 * @example
 * ```php
 * $output = new OutputHandler($logger);
 * $signalHandler = new SignalHandler($output);
 * $signalHandler->register();
 *
 * // Application continues running...
 * // On Ctrl+C: outputs "Operation interrupted" to stderr and exits with code 130
 * ```
 */
final readonly class SignalHandler
{
    /**
     * Constructs the signal handler with an output handler for messages.
     *
     * @param OutputHandler $output Handler for stderr output during signal events
     */
    public function __construct(
        private OutputHandler $output,
    ) {}

    /**
     * Registers the SIGINT handler for graceful interruption.
     *
     * On SIGINT (Ctrl+C), outputs an interruption message to stderr and
     * exits with code 130 (128 + signal number 2), following Unix convention.
     *
     * Does nothing if the PCNTL extension is not available. This allows
     * the application to run in environments where signal handling is
     * not supported without requiring additional configuration.
     */
    public function register(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGINT, function (): never {
            $this->output->stderr('');
            $this->output->stderr('Operation interrupted');
            exit(130); // 128 + SIGINT(2)
        });
    }
}
