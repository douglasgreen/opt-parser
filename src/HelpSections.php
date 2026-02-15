<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

/**
 * Container for optional help section content.
 *
 * @package OptParser
 *
 * @since 1.0.0
 *
 * @internal
 */
final class HelpSections
{
    /** @var array<int, string> */
    public array $examples = [];

    /** @var array<string, string> */
    public array $exitCodes = [];

    /** @var array<string, string> */
    public array $environment = [];

    /** @var array<int, string> */
    public array $documentation = [];
}
