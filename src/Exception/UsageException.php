<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Throwable;

/**
 * CLI usage errors (invalid syntax, unknown options) - Exit code 2 per sysexits.h.
 */
final class UsageException extends OptParserException
{
    public function __construct(
        string $message,
        private readonly int $exitCode = 2,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function isClientError(): bool
    {
        return true;
    }
}
