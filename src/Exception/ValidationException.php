<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Throwable;

/**
 * Validation errors (type mismatches) - Exit code 1.
 */
final class ValidationException extends OptParserException
{
    public function __construct(
        string $message,
        private readonly int $exitCode = 1,
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
        return false;
    }
}
