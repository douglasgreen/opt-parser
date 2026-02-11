<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Throwable;

/**
 * Validation errors (type mismatches) - Exit code 1 or 2.
 * Defaults to 2 (Usage Error) for format mismatches, can be overridden to 1 for system errors.
 */
final class ValidationException extends OptParserException
{
    public function __construct(
        string $message,
        private readonly int $exitCode = 2,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $exitCode, $previous);
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
