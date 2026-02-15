<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Exception;

/**
 * Provides the base exception contract for all OptParser error types.
 *
 * This abstract class defines the common interface for OptParser exceptions,
 * ensuring consistent exit code handling and error categorization across
 * all specialized exception types. Concrete implementations differentiate
 * between validation errors, usage errors, and other failure modes.
 *
 * @package OptParser\Exception
 * @api
 * @since 1.0.0
 * @see ValidationException For type mismatch and format errors
 * @see UsageException For CLI syntax and unknown option errors
 */
abstract class OptParserException extends Exception
{
    /**
     * Returns the appropriate process exit code for this exception.
     *
     * @return int The exit code to use when terminating the CLI process
     */
    abstract public function getExitCode(): int;

    /**
     * Determines whether this exception represents a client-originated error.
     *
     * Client errors indicate user-initiated issues (e.g., invalid input)
     * versus system-level failures that are not the user's responsibility.
     *
     * @return bool True if the error originated from client input, false otherwise
     */
    abstract public function isClientError(): bool;
}
