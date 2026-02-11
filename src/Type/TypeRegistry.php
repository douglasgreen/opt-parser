<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Type;

use DouglasGreen\OptParser\Exception\ValidationException;

/**
 * Registry for type validators with built-in types pre-registered.
 */
final class TypeRegistry
{
    /** @var array<string, TypeInterface> */
    private array $types = [];

    public function __construct()
    {
        $this->registerBuiltInTypes();
    }

    public function register(TypeInterface $type): void
    {
        $this->types[$type->getName()] = $type;
    }

    /**
     * @throws ValidationException if type is unknown
     */
    public function get(string $name): TypeInterface
    {
        if (!isset($this->types[$name])) {
            throw new ValidationException('Unknown type: ' . $name);
        }

        return $this->types[$name];
    }

    /**
     * @return list<string>
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->types);
    }

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
