<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Registry for type validators with built-in types pre-registered.
 *
 * Maintains a central registry of available type validators, providing
 * type lookup by name and supporting custom type registration. All built-in
 * types are automatically registered on instantiation.
 *
 * @package DouglasGreen\OptParser\Type
 *
 * @api
 *
 * @since 1.0.0
 *
 * @example
 * ```php
 * $registry = new TypeRegistry();
 *
 * // Get a built-in type
 * $intType = $registry->get('INT');
 * $value = $intType->validate('42');  // Returns 42 (int)
 *
 * // List all available types
 * $types = $registry->getAvailableTypes();
 * // ['STRING', 'INT', 'FLOAT', 'BOOL', 'DATE', ...]
 *
 * // Register a custom type
 * $registry->register(new CustomType());
 * ```
 */
final class TypeRegistry
{
    /**
     * Internal storage for registered type validators.
     *
     * @var array<string, TypeInterface> Map of type name to validator instance
     */
    private array $types = [];

    /**
     * Constructs the registry and registers all built-in types.
     */
    public function __construct()
    {
        $this->registerBuiltInTypes();
    }

    /**
     * Registers a custom type validator in the registry.
     *
     * If a type with the same name already exists, it will be replaced.
     * This allows overriding built-in types with custom implementations.
     *
     * @param TypeInterface $type The type validator to register
     */
    public function register(TypeInterface $type): void
    {
        $this->types[$type->getName()] = $type;
    }

    /**
     * Retrieves a type validator by its name identifier.
     *
     * Looks up the type in the registry and returns the associated validator
     * instance for use in value validation and transformation.
     *
     * @param string $name The type name identifier (e.g., 'INT', 'EMAIL')
     *
     * @return TypeInterface The type validator instance
     *
     * @throws ValidationException When no type is registered with the given name
     *
     * @example
     * ```php
     * $emailType = $registry->get('EMAIL');
     * $email = $emailType->validate('user@example.com');
     * ```
     */
    public function get(string $name): TypeInterface
    {
        if (!isset($this->types[$name])) {
            throw new ValidationException('Unknown type: ' . $name);
        }

        return $this->types[$name];
    }

    /**
     * Returns a list of all registered type name identifiers.
     *
     * Useful for displaying available types in help output or validation
     * error messages.
     *
     * @return list<string> List of type names in registration order
     *
     * @example
     * ```php
     * $types = $registry->getAvailableTypes();
     * // ['STRING', 'INT', 'FLOAT', 'BOOL', 'DATE', ...]
     * ```
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->types);
    }

    /**
     * Registers all built-in type validators provided by the library.
     *
     * Instantiates and registers the complete set of standard types
     * including primitive types (STRING, INT, FLOAT, BOOL), temporal types
     * (DATE, DATETIME, TIME, INTERVAL), network types (EMAIL, URL, DOMAIN,
     * IP_ADDR, MAC_ADDR), filesystem types (INFILE, OUTFILE, DIR), and
     * special formats (UUID, FIXED).
     */
    private function registerBuiltInTypes(): void
    {
        $builtIns = [
            new StringType(),
            new IntType(),
            new FloatType(),
            new BoolType(),
            new DateType(),
            new DateTimeType(),
            new TimeType(),
            new IntervalType(),
            new EmailType(),
            new UrlType(),
            new DomainType(),
            new IpAddrType(),
            new MacAddrType(),
            new UuidType(),
            new InfileType(),
            new OutfileType(),
            new DirType(),
            new FixedType(),
        ];

        foreach ($builtIns as $type) {
            $this->register($type);
        }
    }
}
