<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser;

use DouglasGreen\Utility\Data\ValueException;
use DouglasGreen\Utility\Regex\Regex;

/**
 * Parse command-line arguments from $args.
 *
 * This class parses command-line arguments according to the GNU argument syntax.
 *
 * @see https://www.gnu.org/software/libc/manual/html_node/Argument-Syntax.html
 * @see \DouglasGreen\OptParser\Tests\ArgParserTest
 */
class ArgParser
{
    /**
     * @var list<string>
     */
    protected array $errors = [];

    /**
     * @var array<string, string> Marked options (options with leading dash)
     */
    protected array $markedOptions = [];

    /**
     * @var list<string> Non-option arguments
     */
    protected array $nonOptions = [];

    /**
     * @var list<string> Unmarked options (options without leading dash)
     */
    protected array $unmarkedOptions = [];

    /**
     * @var string Program name
     */
    protected string $programName;

    /**
     * Constructor.
     *
     * @param list<string> $args Command-line arguments
     *
     * @throws ValueException If no program name is provided
     */
    public function __construct(array $args)
    {
        $programName = array_shift($args);
        if ($programName === null) {
            throw new ValueException('No program name');
        }

        $this->programName = basename($programName);
        [$options, $this->nonOptions] = $this->splitArrayAroundDash($args);
        $options = $this->joinArguments($options);
        foreach ($options as $option) {
            $match = Regex::match('/^--?([a-z]\w*(-[a-z]\w*)*)(=(.*))?/', $option);
            if ($match !== []) {
                $name = $match[1];
                $arg = $match[4] ?? '';
                $this->markedOptions[$name] = $arg;
            } else {
                $this->unmarkedOptions[] = $option;
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the marked options.
     *
     * @return array<string, string> Marked options as key-value pairs
     */
    public function getMarkedOptions(): array
    {
        return $this->markedOptions;
    }

    /**
     * Get the non-option arguments.
     *
     * @return list<string> Non-option arguments
     */
    public function getNonOptions(): array
    {
        return $this->nonOptions;
    }

    /**
     * Get the program name.
     *
     * @return string Program name
     */
    public function getProgramName(): string
    {
        return $this->programName;
    }

    /**
     * Get the unmarked options.
     *
     * @return list<string> Unmarked options
     */
    public function getUnmarkedOptions(): array
    {
        return $this->unmarkedOptions;
    }

    /**
     * Join option names with their arguments using '='.
     *
     * @param list<string> $array Input array of options and arguments
     *
     * @return list<string> Array with joined options and arguments
     */
    protected function joinArguments(array $array): array
    {
        $newArray = [];
        $index = 0;
        $length = count($array);
        $wordRegex = '(-[a-zA-Z]|--[a-z]\w*(-[a-z]\w*)*)\b';

        // Note that we must distinguish between flags like -a and negative
        // numbers like -1 here.
        $wordStartRegex = '--?[a-zA-Z]';

        while ($index < $length) {
            $value = $array[$index];
            if (Regex::hasMatch('/^-([a-zA-Z]{2,})/', $value)) {
                // Matched combined short options.
                $this->errors[] = 'Combined short options are not allowed: ' . $value;
            } elseif (Regex::hasMatch('/^' . $wordRegex . '$/', $value)) {
                if (
                    isset($array[$index + 1]) &&
                    ! Regex::hasMatch('/^' . $wordStartRegex . '/', $array[$index + 1])
                ) {
                    // Matched a param with no =, so join parameter with
                    // argument by =.
                    $value .= '=' . $array[++$index];
                    $newArray[] = $value;
                } else {
                    // Matched a flag.
                    $newArray[] = $value;
                }
            } elseif (Regex::hasMatch('/^' . $wordRegex . '=.+/', $value)) {
                // Matched a param with = and argument.
                $newArray[] = $value;
            } elseif (Regex::hasMatch('/^' . $wordStartRegex . '/i', $value)) {
                $this->errors[] = 'Unrecognized flag or parameter format: ' . $value;
            } else {
                $newArray[] = $value;
            }

            ++$index;
        }

        return $newArray;
    }

    /**
     * Split an array into two parts around the '--' separator.
     *
     * @param list<string> $array Input array
     *
     * @return array{list<string>, list<string>} Array with elements before '--' and after '--'
     */
    protected function splitArrayAroundDash(array $array): array
    {
        // Find the index of '--'
        $dashIndex = array_search('--', $array, true);

        // Check if '--' was found
        if ($dashIndex === false) {
            // '--' is not in the array, return the whole array and an empty array
            return [$array, []];
        }

        // Split the array into two subarrays
        $before = array_slice($array, 0, $dashIndex);
        $after = array_slice($array, $dashIndex + 1);

        return [$before, $after];
    }
}
