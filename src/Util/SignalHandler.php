<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Util;

/**
 * Handles POSIX signals for graceful shutdown.
 */
final class SignalHandler
{
    public function __construct(
        private OutputHandler $output,
    ) {}

    public function register(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGINT, function () {
            $this->output->stderr('');
            $this->output->stderr('Operation interrupted');
            exit(130); // 128 + SIGINT(2)
        });
    }
}
