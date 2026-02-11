<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

/**
 * Immutable result container for parsed CLI input.
 */
final readonly class Input
{
    /**
     * @param array<string, mixed> $options
     * @param array<int, string> $nonOptions
     */
    public function __construct(
        private ?string $command,
        private array $options,
        private array $nonOptions,
    ) {}

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function get(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @return array<int, string>
     */
    public function getNonoptions(): array
    {
        return $this->nonOptions;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
