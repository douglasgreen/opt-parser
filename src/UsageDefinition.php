<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\OptParser\Exception\UsageException;

/**
 * Defines valid option combinations for specific commands.
 */
final class UsageDefinition
{
    /** @var array<string, list<string>> */
    private array $usages = [];

    /**
     * @param array<int, string> $optionNames
     */
    public function addUsage(string $command, array $optionNames): void
    {
        if (!isset($this->usages[$command])) {
            $this->usages[$command] = [];
        }

        foreach ($optionNames as $name) {
            $this->usages[$command][] = $name;
        }
    }

    /**
     * Validates that the given options are compatible with the command.
     *
     * @param array<string, mixed> $providedOptions
     *
     * @throws Exception\UsageException if invalid combination
     */
    public function validate(string $command, array $providedOptions): void
    {
        if (!isset($this->usages[$command])) {
            return; // No usage defined, allow anything
        }

        $allowed = $this->usages[$command];

        foreach (array_keys($providedOptions) as $name) {
            if ($name === '_') {
                continue;
            }
            if ($name === $command) {
                continue;
            }
            if (!in_array($name, $allowed, true)) {
                throw new UsageException(
                    sprintf("Option '%s' is not allowed with command '%s'", $name, $command),
                );
            }
        }
    }
}
