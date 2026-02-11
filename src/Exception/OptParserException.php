<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Exception;

use Exception;

/**
 * Base exception for all OptParser errors.
 */
abstract class OptParserException extends Exception
{
    abstract public function getExitCode(): int;

    abstract public function isClientError(): bool;
}
