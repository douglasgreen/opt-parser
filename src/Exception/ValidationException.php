<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Throwable;

/**
 * Represents errors caused by type mismatches or invalid argument formats.
 *
 * This exception is thrown when option or argument values fail type validation,
 * such as when a non-numeric string is provided for an integer argument.
 * The default exit code is 2 (Usage Error) for format mismatches, but can be
 * overridden to 1 for system-level validation failures.
 *
 * @package OptParser\Exception
 * @api
 * @since 1.0.0
 * @see OptParserException For the base exception contract
 */
final class ValidationException extends OptParserException
{
    /**
     * Constructs a validation exception with an optional custom exit code.
     *
     * @param string $message Descriptive error message explaining the validation failure
     * @param int $exitCode Process exit code (default: 2 for usage errors)
     * @param Throwable|null $previous Previous exception for exception chaining
     */
    public function __construct(
        string $message,
        private readonly int $exitCode = 2,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $exitCode, $previous);
    }

    /**
     * Returns the configured exit code for this validation error.
     *
     * @return int The exit code (default: 2)
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Indicates whether this is a client-originated error.
     *
     * Validation errors are not considered client errors as they typically
     * represent format or type issues rather than user input mistakes.
     *
     * @return bool Always returns false for validation exceptions
     */
    public function isClientError(): bool
    {
        return false;
    }
}
