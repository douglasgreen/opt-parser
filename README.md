# OptParser

A POSIX.1-2017 compliant command-line option parser for PHP with GNU extensions.

## Overview

OptParser implements the [IEEE Std 1003.1-2017 (POSIX.1-2017)](https://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap12.html) utility conventions, supporting:

- **Standard option syntax**: Short options (`-a`), clustering (`-abc`), and arguments (`-a value` or `-a=value`)
- **GNU extensions**: Long options (`--option`, `--option=value`) for enhanced readability
- **Strict separation**: stdout for data, stderr for diagnostics
- **Standard exit codes**: `0` (success), `2` (usage error), `130` (SIGINT), etc.
- **Type safety**: Built-in validation for 20+ data types (paths, dates, networks, etc.)

Unlike PHP's built-in `getopt()`, OptParser supports positional arguments (terms), subcommands, automatic help generation, and strict validation with descriptive error messages.

## Installation

```bash
composer require douglasgreen/opt-parser
```

## POSIX Compliance

This library adheres to POSIX.1-2017 Section 12.2 (Utility Syntax Guidelines) and XCU Section 12.2 (Utility Conventions):

### Option Syntax

| Syntax | Description | Example |
|--------|-------------|---------|
| `-a` | Short option | `-v` (verbose) |
| `-abc` | Clustered options (equivalent to `-a -b -c`) | `-vrf` (verbose, recursive, force) |
| `-a value` | Option with argument (space-separated) | `-o output.txt` |
| `-a=value` | Option with argument (equals-separated) | `-o=output.txt` |
| `--long` | Long option (GNU extension) | `--verbose` |
| `--long=value` | Long option with argument | `--output=file.txt` |
| `--` | Option terminator (remaining args are operands) | `-- -filename-starting-with-dash` |

**Important**: When using clustered options, if the last option in a cluster requires an argument, the remaining characters are interpreted as the argument. For example, if `-o` requires an argument, `-abcovalue` is equivalent to `-a -b -c -o value`.

### Exit Codes

OptParser uses standard exit codes per `sysexits.h` and POSIX conventions:

| Code | Meaning | Usage |
|------|---------|-------|
| `0` | `EXIT_SUCCESS` | Successful execution |
| `1` | `EXIT_FAILURE` | General error (catchall) |
| `2` | `EX_USAGE` | CLI usage error (invalid arguments, unknown options) |
| `126` | - | Command invoked cannot execute (permission denied) |
| `127` | - | Command not found |
| `130` | - | Fatal error signal (`128 + SIGINT(2)`) |

### Stream Handling

- **stdout**: Primary data output (results, matched options, machine-readable formats)
- **stderr**: Diagnostic messages (errors, warnings, progress, help text when explicitly requested via error)

### Signal Handling

Long-running operations should handle `SIGINT` (Ctrl+C) gracefully, performing cleanup and exiting with status `130`.

## Usage Guide

### Basic Program Structure

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

use DouglasGreen\OptParser\OptParser;

require_once __DIR__ . '/../vendor/autoload.php';

// Define program with name and description
$optParser = new OptParser('User Manager', 'Manage system user accounts');

// Define options
$optParser
    // Commands (mutually exclusive operations)
    ->addCommand(['add', 'a'], 'Add a new user')
    ->addCommand(['delete', 'd'], 'Delete an existing user')
    ->addCommand(['list', 'l'], 'List all users')
    
    // Terms (positional arguments)
    ->addTerm('username', 'STRING', 'Username of the user')
    ->addTerm('email', 'EMAIL', 'Email address of the user')
    
    // Parameters (named arguments with values)
    ->addParam(['password', 'p'], 'STRING', 'Password for the user')
    ->addParam(['role', 'r'], 'STRING', 'Role of the user')
    ->addParam(['output', 'o'], 'OUTFILE', 'Output file path')
    
    // Flags (boolean switches)
    ->addFlag(['verbose', 'v'], 'Enable verbose output')
    ->addFlag(['quiet', 'q'], 'Suppress non-error output')
    ->addFlag(['force', 'f'], 'Force operation without confirmation');

// Define usage patterns (which options go with which commands)
$optParser
    ->addUsage('add', ['username', 'email', 'password', 'role', 'verbose', 'quiet'])
    ->addUsage('delete', ['username', 'force', 'verbose'])
    ->addUsage('list', ['output', 'verbose']);

// Parse arguments (uses $argv by default)
try {
    $input = $optParser->parse();
} catch (Exception $e) {
    // Fatal parsing errors exit with code 2 (EX_USAGE)
    // Use --help for usage information
    exit(2);
}

// Execute based on matched command
$command = $input->getCommand();

switch ($command) {
    case 'add':
        $username = $input->get('username');
        $email = $input->get('email');
        $password = $input->get('password');
        $role = $input->get('role') ?? 'user';
        $verbose = $input->get('verbose') ?? false;
        
        // Implementation...
        break;
        
    case 'delete':
        $username = $input->get('username');
        $force = $input->get('force') ?? false;
        // Implementation...
        break;
        
    case 'list':
        $output = $input->get('output');
        $verbose = $input->get('verbose') ?? false;
        // Implementation...
        break;
}
```

### Option Types

The parser supports four option categories:

| Type | Description | POSIX Equivalent | Example |
|------|-------------|------------------|---------|
| **Command** | Subcommand selector (first positional) | Utility operand | `git clone` |
| **Term** | Positional argument with validation | Utility operand | `file.txt` |
| **Param** | Option requiring an argument | Option with operand | `-o file` or `--output=file` |
| **Flag** | Boolean option without argument | Option without operand | `-v` or `--verbose` |

### POSIX Option Syntax Details

**Short Options:**
- Single hyphen followed by single character: `-a`
- May be clustered: `-abc` equivalent to `-a -b -c`
- Argument may follow immediately or be separated by space: `-ovalue` or `-o value`
- If clustering options where the last takes an argument: `-abco value` or `-abcovalue`

**Long Options:**
- Double hyphen followed by name: `--verbose`
- Arguments may be separated by space or equals: `--output file` or `--output=file`
- Names use kebab-case (lowercase with hyphens)

**Option Terminator:**
- Double hyphen `--` indicates end of options
- All subsequent arguments are treated as operands (terms), even if they start with `-`

### Data Types

Built-in validation types (extending POSIX with type safety):

| Type | Validation | Example |
|------|------------|---------|
| `STRING` | Any string | `"hello"` |
| `INT` | Integer (octal/hex supported) | `42`, `0x2A` |
| `FLOAT` | Floating point | `3.14`, `1e10` |
| `BOOL` | Boolean | `true`, `1`, `yes` |
| `DATE` | ISO 8601 date | `2024-01-15` |
| `DATETIME` | ISO 8601 datetime | `2024-01-15 14:30:00` |
| `TIME` | Time string | `14:30:00` |
| `INTERVAL` | Date interval | `1 day 2 hours` |
| `EMAIL` | Email address | `user@example.com` |
| `URL` | URL | `https://example.com` |
| `DOMAIN` | Domain name | `example.com` |
| `IP_ADDR` | IP address | `192.168.1.1` |
| `MAC_ADDR` | MAC address | `00:1B:44:11:3A:B7` |
| `UUID` | UUID | `550e8400-e29b-41d4-a716-446655440000` |
| `INFILE` | Readable file path | `/path/to/input` |
| `OUTFILE` | Writable file path | `/path/to/output` |
| `DIR` | Readable directory | `/path/to/dir` |
| `FIXED` | Fixed-point number | `1,234.56` |

### Exit Codes

The library uses standard exit codes per `sysexits.h` and POSIX conventions:

| Code | Constant | Meaning |
|------|----------|---------|
| `0` | `EXIT_SUCCESS` | Successful execution |
| `1` | `EXIT_FAILURE` | General error during execution |
| `2` | `EX_USAGE` | Command line usage error (invalid arguments, unknown options, missing required options) |
| `126` | - | Command invoked cannot execute (permission denied) |
| `127` | - | Command not found |
| `128` | - | Invalid exit argument |
| `130` | - | Script terminated by Ctrl+C (SIGINT) |

### Error Handling

Errors are written to **stderr** with descriptive messages. The parser distinguishes between:

- **Usage errors** (exit code 2): Invalid syntax, unknown options, missing arguments
- **Validation errors** (exit code 1): Type mismatches, file not found, invalid formats
- **Logic errors**: Exceptions thrown during callback execution

Example error output:
```
error: unrecognized option '--verbos'
error: option '--output' requires an argument
error: term 'username' has invalid argument '123': Not a valid string
```

### Advanced Features

#### Custom Validation Filters

Apply custom logic to any parameter or term:

```php
$optParser->addParam(
    ['role', 'r'],
    'STRING',
    'User role',
    function ($value) {
        $allowed = ['admin', 'editor', 'viewer'];
        if (!in_array($value, $allowed, true)) {
            throw new Exception(
                "Role must be one of: " . implode(', ', $allowed)
            );
        }
        return $value;
    }
);
```

#### Signal Handling

For long-running operations, handle interruption gracefully:

```php
declare(ticks=1);

pcntl_signal(SIGINT, function () {
    // Cleanup temporary files, database transactions, etc.
    error_log("Operation interrupted by user");
    exit(130); // 128 + SIGINT(2)
});

// Your main logic here
```

#### Non-Options (Operands)

Arguments after `--` are treated as operands regardless of content:

```bash
# Delete a file literally named "--force"
php delete.php -- --force
```

Access via:
```php
$nonOptions = $input->getNonoptions(); // ['--force']
```

### Best Practices

1. **Options before operands**: While POSIX allows intermixing, place all options before positional arguments for clarity
2. **Use long options in scripts**: `--verbose` is more readable than `-v` in automation
3. **Check exit codes**: Always check `$?` in shell scripts; don't assume success
4. **Quote operands**: Always quote variables that might contain spaces or special characters
5. **Use `--`**: When passing arbitrary filenames, always use `--` to prevent option injection

### Comparison with getopt()

| Feature | PHP getopt() | OptParser |
|---------|--------------|-------------|
| Short options | Yes | Yes |
| Long options | Limited | Yes (GNU style) |
| Option clustering | No | Yes (`-abc`) |
| Positional arguments | No | Yes (Terms) |
| Subcommands | No | Yes (Commands) |
| Type validation | No | Yes (20+ types) |
| Automatic help | No | Yes |
| Standard exit codes | No | Yes |
| `--` terminator | Partial | Full |

### License

MIT License - See LICENSE file for details.
