<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use DouglasGreen\OptParser\Type\TypeRegistry;

/**
 * Represents a boolean command-line flag that requires no value argument.
 *
 * Flags are presence-based options that resolve to `true` when provided on the
 * command line and `false` when omitted. They support both short (`-v`) and
 * long (`--verbose`) option forms.
 *
 * Common use cases include:
 * - Enabling verbose output: `--verbose`, `-v`
 * - Suppressing messages: `--quiet`, `-q`
 * - Forcing operations: `--force`, `-f`
 *
 * @package OptParser\Option
 *
 * @api
 *
 * @since 1.0.0
 * @see AbstractOption For inherited base functionality
 *
 * @example
 * ```php
 * // Verbose flag with short and long forms
 * $verbose = new Flag(['v', 'verbose'], 'Enable verbose output');
 *
 * // Quiet flag (single form)
 * $quiet = new Flag(['q', 'quiet'], 'Suppress all output');
 *
 * // Usage in parsing result:
 * // Command: myapp --verbose
 * // Result: $input->get('verbose') === true
 * ```
 */
final readonly class Flag extends AbstractOption
{
    /**
     * Confirms that flags do not accept value arguments.
     *
     * @return bool Always returns false
     */
    public function acceptsValue(): bool
    {
        return false;
    }

    /**
     * Validates the flag presence (always returns true).
     *
     * Since flags are presence-based and require no value, this method
     * always returns `true` to indicate the flag was present.
     *
     * @param string $value Ignored for flags
     * @param TypeRegistry $registry Ignored for flags
     *
     * @return true Always returns true when flag is present
     */
    public function validateValue(string $value, TypeRegistry $registry): true
    {
        return true;
    }

    /**
     * Returns the default value for flags when not provided.
     *
     * Flags default to `false` when the option is not present on the
     * command line, and `true` when provided.
     *
     * @return bool Always returns false
     */
    public function getDefault(): bool
    {
        return false;
    }
}
