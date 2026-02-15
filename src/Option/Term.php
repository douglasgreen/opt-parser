<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use Closure;
use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\TypeRegistry;
use Exception;
use Override;

/**
 * Represents a positional command-line argument (term).
 *
 * Terms are positional arguments that appear after options and commands,
 * identified by their position rather than a name prefix. They are parsed
 * in the order they were registered and validated against a specified type.
 *
 * Unlike named options, terms:
 * - Have only a single name (no aliases)
 * - Are identified by position in the argument list
 * - Typically appear after all named options
 *
 * @package OptParser\Option
 * @api
 * @since 1.0.0
 * @see AbstractOption For inherited base functionality
 * @see OptionRegistry::getTerms() For retrieving all registered terms
 *
 * @example
 * ```php
 * // Required source file term
 * $source = new Term('source', 'Source file path', 'path', required: true);
 *
 * // Optional destination with filter
 * $dest = new Term(
 *     'destination',
 *     'Destination file path',
 *     'path',
 *     required: false,
 *     filter: fn(string $p): string => realpath($p) ?: $p
 * );
 *
 * // Term parsing result:
 * // Input: myapp /path/to/source /path/to/dest
 * // Result: $input->get('source') === '/path/to/source'
 * ```
 */
final readonly class Term extends AbstractOption
{
    /**
     * Constructs a positional term with type validation.
     *
     * Terms differ from other options in that they accept only a single name
     * (no aliases) since they are identified by position rather than name.
     *
     * @param string $name Term identifier for value retrieval
     * @param string $description Human-readable description for help output
     * @param string $typeName Registered type name for validation (e.g., 'string', 'int', 'path')
     * @param bool $required Whether the term must be provided (default: true)
     * @param Closure(mixed): mixed|null $filter Optional transformation/filter closure
     */
    public function __construct(
        string $name,
        string $description,
        private string $typeName,
        private bool $required = true,
        private ?Closure $filter = null,
    ) {
        parent::__construct([$name], $description);
    }

    /**
     * Confirms that terms accept value arguments.
     *
     * @return bool Always returns true
     */
    public function acceptsValue(): bool
    {
        return true;
    }

    /**
     * Returns whether this term is required.
     *
     * @return bool True if required, false otherwise
     */
    #[Override]
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Validates a string value against the registered type and applies optional filter.
     *
     * The validation process:
     * 1. Retrieves the type validator from the registry
     * 2. Validates and converts the string to the typed value
     * 3. Applies the filter closure if configured
     *
     * @param string $value The raw string value from command line
     * @param TypeRegistry $registry Type validators for validation
     * @return mixed The validated and possibly transformed value
     * @throws ValidationException When type validation fails or filter throws
     */
    public function validateValue(string $value, TypeRegistry $registry): mixed
    {
        $type = $registry->get($this->typeName);
        $typedValue = $type->validate($value);

        if ($this->filter instanceof Closure) {
            try {
                $typedValue = ($this->filter)($typedValue);
            } catch (Exception $e) {
                throw new ValidationException(
                    sprintf("Filter rejected value for '%s': %s", $this->getPrimaryName(), $e->getMessage()),
                );
            }
        }

        return $typedValue;
    }
}
