<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

use Closure;
use DateInterval;
use DateTimeImmutable;
use Exception;
use DouglasGreen\Utility\Data\ArgumentException;
use DouglasGreen\Utility\Data\TypeException;
use DouglasGreen\Utility\Data\ValueException;
use DouglasGreen\Utility\FileSystem\PathUtil;
use DouglasGreen\Utility\Regex\Regex;

abstract class Option
{
    /**
     * @var list<string>
     *
     * Most types are just filters. Other types include:
     * - DATE - date in YYYY-MM-DD format
     * - DATETIME - datetime in YYYY-MM-DD HH:MM:SS format
     * - DIR - directory that must exist and be readable
     * - FIXED - fixed-point number
     * - INFILE - input file that must exist and be readable
     * - INTERVAL - time interval
     * - OUTFILE - output file that must not exist and be writable
     * - TIME - time in HH:MM:SS format
     * - UUID - UUID with or without hyphens
     *
     * @see https://www.php.net/manual/en/filter.filters.validate.php
     */
    protected const ARG_TYPES = [
        'BOOL',
        'DATE',
        'DATETIME',
        'DIR',
        'DOMAIN',
        'EMAIL',
        'FIXED',
        'FLOAT',
        'INFILE',
        'INTERVAL',
        'INT',
        'IP_ADDR',
        'MAC_ADDR',
        'OUTFILE',
        'STRING',
        'TIME',
        'URL',
        'UUID',
    ];

    protected ?Closure $callback = null;

    /**
     * @var ?list<string>
     */
    protected ?array $aliases = null;

    /**
     * @var ?string Type of the argument
     */
    protected ?string $argType = null;

    abstract public function write(): string;

    public function __construct(
        protected string $name,
        protected string $desc,
        ?callable $callback = null,
    ) {
        if ($callback !== null) {
            $this->callback = Closure::fromCallable($callback);
        }
    }

    /**
     * @return ?list<string>
     */
    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function getArgType(): ?string
    {
        return $this->argType;
    }

    public function getDesc(): string
    {
        return $this->desc;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hyphenate(string $alias): string
    {
        if (strlen($alias) === 1) {
            return '-' . $alias;
        }

        return '--' . $alias;
    }

    public function matchName(string $name): bool
    {
        return $this->name === $name || $this->hasAlias($name);
    }

    /**
     * @throws ArgumentException
     */
    public function matchValue(string $value): string|float|int|bool|null
    {
        $filtered = match ($this->argType) {
            'BOOL' => $this->castBool($value),
            'DATE' => $this->castDate($value),
            'DATETIME' => $this->castDatetime($value),
            'DIR' => $this->checkDir($value),
            'DOMAIN' => $this->castDomain($value),
            'EMAIL' => $this->castEmail($value),
            'FIXED' => $this->castFixed($value),
            'FLOAT' => $this->castFloat($value),
            'INFILE' => $this->checkInputFile($value),
            'INT' => $this->castInt($value),
            'INTERVAL' => $this->castDateInterval($value),
            'IP_ADDR' => $this->castIpAddress($value),
            'MAC_ADDR' => $this->castMacAddress($value),
            'OUTFILE' => $this->checkOutputFile($value),
            'STRING' => $value,
            'TIME' => $this->castTime($value),
            'URL' => $this->castUrl($value),
            'UUID' => $this->castUuid($value),
            default => null,
        };

        // Check for failure of basic type
        if ($filtered === null) {
            throw new ArgumentException('Unrecognized type');
        }

        // Apply callback to validate if available
        if ($this->callback instanceof Closure) {
            return ($this->callback)($filtered);
        }

        return $filtered;
    }

    /**
     * @throws ValueException
     */
    protected function addAlias(string $alias): void
    {
        // Only matches lower case separated by hyphens
        if (! Regex::hasMatch('/^([a-z][a-z0-9]*(-[a-z0-9]+)*|[A-Z])$/', $alias)) {
            throw new ValueException(
                'Alias is not hyphenated lower case or single-letter upper case: ' . $alias,
            );
        }

        $this->aliases[] = $alias;
    }

    /**
     * @throws ArgumentException
     */
    protected function castBool(string $value): bool
    {
        $valid = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($valid === null) {
            throw new ArgumentException('Not a valid Boolean');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castDate(string $value): string
    {
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        throw new ArgumentException('Not a valid date');
    }

    /**
     * @throws ArgumentException
     */
    protected function castDateInterval(string $input): string
    {
        try {
            $interval = DateInterval::createFromDateString($input);
        } catch (Exception) {
            // Catch a generic exception because catching DateMalformedIntervalStringException requires PHP 8.3.
            $interval = false;
        }

        if ($interval === false) {
            throw new ArgumentException('Not a valid date interval');
        }

        $formatted = [];

        // Convert DateInterval to total seconds
        $start = new DateTimeImmutable();
        $end = $start->add($interval);
        $seconds = $end->getTimestamp() - $start->getTimestamp();

        // Break down the total seconds into years, months, days, hours, and minutes
        $years = (int) floor($seconds / (365 * 24 * 60 * 60));
        $seconds -= $years * (365 * 24 * 60 * 60);

        $months = (int) floor($seconds / (30 * 24 * 60 * 60));
        $seconds -= $months * (30 * 24 * 60 * 60);

        $days = (int) floor($seconds / (24 * 60 * 60));
        $seconds -= $days * (24 * 60 * 60);

        $hours = (int) floor($seconds / (60 * 60));
        $seconds -= $hours * (60 * 60);

        $minutes = (int) floor($seconds / 60);
        $seconds -= $minutes * 60;

        $formatted = [];

        if ($years !== 0) {
            $formatted[] = $years . ' year' . ($years > 1 ? 's' : '');
        }

        if ($months !== 0) {
            $formatted[] = $months . ' month' . ($months > 1 ? 's' : '');
        }

        if ($days !== 0) {
            $formatted[] = $days . ' day' . ($days > 1 ? 's' : '');
        }

        if ($hours !== 0) {
            $formatted[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        if ($minutes !== 0) {
            $formatted[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        if ($seconds !== 0) {
            $formatted[] = $seconds . ' second' . ($seconds > 1 ? 's' : '');
        }

        return $formatted === [] ? '0 seconds' : implode(', ', $formatted);
    }

    /**
     * @throws ArgumentException
     */
    protected function castDatetime(string $value): ?string
    {
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        throw new ArgumentException('Not a valid datetime');
    }

    /**
     * @throws ArgumentException
     */
    protected function castDomain(string $value): string
    {
        $valid = filter_var(
            $value,
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME | FILTER_NULL_ON_FAILURE,
        );
        if ($valid === null) {
            throw new ArgumentException('Not a valid domain');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castEmail(string $value): string
    {
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        if ($valid === null) {
            throw new ArgumentException('Not a valid email');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castFixed(string $value): string
    {
        if (Regex::hasMatch('/^[+-]?\d+([,_]\d{3})*(\.\d+)?$/', $value)) {
            return Regex::replace('/[,_]/', '', $value);
        }

        throw new ArgumentException('Not a valid fixed-point number');
    }

    /**
     * @throws ArgumentException
     */
    protected function castFloat(string $value): float
    {
        $valid = filter_var(
            $value,
            FILTER_VALIDATE_FLOAT,
            FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_NULL_ON_FAILURE,
        );
        if ($valid === null) {
            throw new ArgumentException('Not a valid floating-point number');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castInt(string $value): int
    {
        $valid = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX | FILTER_NULL_ON_FAILURE,
        );
        if ($valid === null) {
            throw new ArgumentException('Not a valid integer');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castIpAddress(string $value): string
    {
        $valid = filter_var($value, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE);
        if ($valid === null) {
            throw new ArgumentException('Not a valid IP address');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castMacAddress(string $value): string
    {
        $valid = filter_var($value, FILTER_VALIDATE_MAC, FILTER_NULL_ON_FAILURE);
        if ($valid === null) {
            throw new ArgumentException('Not a valid MAC address');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castTime(string $value): string
    {
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('H:i:s', $timestamp);
        }

        throw new ArgumentException('Not a valid time');
    }

    /**
     * @throws ArgumentException
     */
    protected function castUrl(string $value): string
    {
        $valid = filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
        if ($valid === null) {
            throw new ArgumentException('Not a valid URL');
        }

        return $valid;
    }

    /**
     * @throws ArgumentException
     */
    protected function castUuid(string $value): string
    {
        // Remove any hyphens from the input value
        $value = str_replace('-', '', $value);

        // Check if the length is 32 characters
        if (strlen($value) !== 32) {
            throw new ArgumentException('UUID is not 32 characters');
        }

        // Check if it contains only hexadecimal characters
        if (! ctype_xdigit($value)) {
            throw new ArgumentException('UUID contains invalid characters');
        }

        // Insert hyphens at the appropriate positions
        $uuid =
            substr($value, 0, 8) .
            '-' .
            substr($value, 8, 4) .
            '-' .
            substr($value, 12, 4) .
            '-' .
            substr($value, 16, 4) .
            '-' .
            substr($value, 20);

        return $uuid;
    }

    /**
     * Check that the dir is readable then form the dir path.
     *
     * @throws ArgumentException
     */
    protected function checkDir(string $value): string
    {
        if (! is_dir($value)) {
            throw new ArgumentException('Path is not a directory');
        }

        if (! is_readable($value)) {
            throw new ArgumentException('Directory is not readable');
        }

        return PathUtil::resolve($value);
    }

    /**
     * Check that the input file is readable then form the file path.
     *
     * @throws ArgumentException
     */
    protected function checkInputFile(string $value): string
    {
        if (! file_exists($value)) {
            throw new ArgumentException('Path is not a file');
        }

        if (! is_readable($value)) {
            throw new ArgumentException('File is not readable');
        }

        return PathUtil::resolve($value);
    }

    /**
     * Check that the parent directory is writable then form the new file path.
     *
     * @throws ArgumentException
     */
    protected function checkOutputFile(string $value): string
    {
        $directory = PathUtil::resolve(dirname($value));
        if (! is_writable($directory)) {
            throw new ArgumentException('File directory is not writable');
        }

        return PathUtil::addSubpath($directory, basename($value));
    }

    /**
     * Check for supported types.
     *
     * @throws TypeException
     */
    protected function checkType(string $argType): void
    {
        if (! in_array($argType, self::ARG_TYPES, true)) {
            throw new TypeException('Unsupported argument type: ' . $argType);
        }
    }

    protected function hasAlias(string $alias): bool
    {
        return $this->aliases && in_array($alias, $this->aliases, true);
    }

    protected function setArgType(string $argType): void
    {
        $argType = strtoupper($argType);
        $this->checkType($argType);
        $this->argType = $argType;
    }
}
