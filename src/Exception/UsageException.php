<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Throwable;

/**
 * Represents CLI usage errors such as invalid syntax or unknown options.
 *
 * This exception is thrown when the command-line invocation is malformed,
 * including unknown options, missing required arguments, or syntactic errors.
 * Follows the sysexits.h convention with exit code 2 (EX_USAGE) by default.
 *
 * @package OptParser\Exception
 * @api
 * @since 1.0.0
 * @see OptParserException For the base exception contract
 * @link https://man.openbsd.org/sysexits.3 sysexits.h exit code conventions
 */
final class UsageException extends OptParserException
{
    /**
     * Constructs a usage exception with an optional custom exit code.
     *
     * @param string $message Descriptive error message explaining the usage mistake
     * @param int $exitCode Process exit code (default: 2 per sysexits.h)
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
     * Returns the configured exit code for this usage error.
     *
     * @return int The exit code (default: 2 per sysexits.h)
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Indicates whether this is a client-originated error.
     *
     * Usage errors are always client errors as they result from incorrect
     * command-line invocation by the user.
     *
     * @return bool Always returns true for usage exceptions
     */
    public function isClientError(): bool
    {
        return true;
    }
}
