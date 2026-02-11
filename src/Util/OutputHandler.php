<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Util;

use Psr\Log\LoggerInterface;

/**
 * Handles stdout/stderr output with TTY detection and color support.
 */
final class OutputHandler
{
    private bool $isTty;

    private readonly bool $noColor;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $forceColor = false,
    ) {
        $this->noColor = $this->detectNoColor();
    }

    public function stdout(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    public function stderr(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }

    public function isTty(): bool
    {
        if (!isset($this->isTty)) {
            $this->isTty = posix_isatty(STDOUT);
        }

        return $this->isTty;
    }

    public function supportsColor(): bool
    {
        if ($this->forceColor) {
            return true;
        }

        return !$this->noColor && $this->isTty();
    }

    private function detectNoColor(): bool
    {
        return getenv('NO_COLOR') !== false;
    }
}
