# opt-parser

Command-line option parser for PHP

## Setup

Add the project with Composer.

```
composer require douglasgreen/utility
```

## About

OptParser is a replacement for using the built-in
[`getopt()`](https://www.php.net/manual/en/function.getopt.php) to get command-line options in PHP
programs. It's better than getopt because:

-   It supports defining multiple option types and aliases.
-   It allows mixing positional and named options.
-   It allows defining separate commands, each with its own usage.
-   It automatically prints formatted program help.
-   It checks for unrecognized arguments to prevent invalid program execution.

## Commands

A program accepts one or more types of usage. A usage is a series of options.

## Options

An option is one of the following:

-   A command, which is the name of a requested operation.
-   A term, which is a positional argument.
-   A parameter, which is a hyphen-prefixed name with one required argument.
-   A flag, which is a hyphen-prefixed name with zero arguments.

If there is a command, there can only be one command per usage and it must come first. If there are
more than one usage, each usage must have a command to distinguish it.

If there are terms, they are required and must follow the command, if any.

Parameters and flags come last. The order is important to avoid ambiguity.

Commands and terms are required, because they are positional, but parameters and flags are optional,
because they are named.

## Naming convention

Long names for commands, flags, and parameters must all follow the Unix convention for their names:

-   All lowercase
-   Start with a letter
-   Contain letters, digits, and hyphens but no underscores
-   End in a letter or digit

That is also known as kebab case. An example name is `file-path`.

## Command matching

Each usage is distinguished by its command. Each command is different and so only one usage can
possibly match at runtime.

## Argument aliases

Each argument can have one or more aliases. If an alias is short (only one character), it is invoked
with a single hyphen like `-h`. If an alias is long (more than one character), it is invoked with
two hyphens like `--help`.

Short aliases can be lower case or upper case.

Each alias must be defined only once.

## Argument names

Every argument must have at least one long alias. The first long alias that is specified for that
argument is used as the argument name. You must retrieve the argument using its name.

Commands, parameters, and flags can have aliases. But a term is not marked so it only has a name,
not aliases.

## Argument types

Most of the list of permitted argument types was taken from the list of
[PHP validation filters](https://www.php.net/manual/en/filter.filters.validate.php). Some custom
types were added. Permitted types include:

-   `BOOL` - Valid Boolean value (true for "1", "true", "on", and "yes")
-   `DATE` - Valid date value
-   `DATETIME` - Valid date and time value
-   `DIR` - Readable directory path
-   `DOMAIN` - Valid domain name length and value
-   `EMAIL` - Valid email address
-   `FIXED` - Valid fixed-point value
-   `FLOAT` - Valid floating-point value, allowing commas and scientific
-   `INFILE` - Readable file path
-   `INTERVAL` - Valid DateInterval string description
-   `INT` - Valid integer value, allowing octal and hex notations
-   `IP_ADDR` - Valid IP address
-   `MAC_ADDR` - Valid MAC address
-   `OUTFILE` - File path with writable parent dir, may or may not exist
-   `STRING` - Any string
-   `TIME` - Valid time value
-   `URL` - Valid URL
-   `UUID` - Valid Universal Unique ID, with or without hyphens

These are specified as the second argument of `OptParser::addParam()` and `OptParser::addTerm()`,
because parameters and terms accept arguments and therefore have type. These types are printed in
program help and applied automatically during argument processing, resulting in program error and
termination on failure to validate.

## Argument filters

You can define your own filter callback as the last argument of `OptParser::addParam()` and
`OptParser::addTerm()` also. The filter can do custom validation. If validation succeeds, you can
return the original value or a filtered version of it. If validation fails, throw a
`BadArgumentException` with a descriptive error message, and the program will error and terminate.

## Formatting

Flags can't be combined, for example `-a -b -c` can't be written as `-abc`. That is to prevent
errors where you type one hyphen like -pass instead of --pass and get options -a -p and -s by
mistake.

A space or equal sign must separate every flag or parameter from its argument.

The argument list can be terminated by `--`, which can be followed by non-options. The program
ignores non-options but returns them with the matched usage. You can retrieve them using
`OptResult::getNonoptions()`.

## Fetching results

Results are returned by `OptParser::parse()` as an `OptResult` object which I call `$input` as it
represents user input.

You can retrieve matched arguments from the user with `$input->get($name)`, where name was the
option name. You can also retrieve arguments with as attributes with `$input->$name`. Camel case in
the attribute names is mapped to kebab case in option names. For example, `$input->filePath` would
map to the `file-path` option name.

## Program interface

To use OptParser:

1. Create an `OptParser` instance with the name and description of the program. This is used when
   displaying program help.
2. Use chained calls on `$optParser` to `addCommand()`, `addTerm()`, `addParam()`, and `addFlag()`
   to define those types of options.
3. Combine the options together into usages by calling `$optParser->addUsage()` to add a specific
   combination of option names. If there is only one usage, you can call the simpler
   `$optParser->addUsageAll()` to add all the options at once.
4. Parse the arguments with `$input = $optParser->parse();`.
5. Get the command executed with `$command = $input->getCommand();` to determine how to interpret
   output.
6. Fetch each option name for the current command with `$input->get($name)` or the more concise
   `$input->$name`.

## Sample usage

There is a [sample usage](bin/sample-usage) in a file. You can also run the program with `-h` to see
sample help output.

## Sample help

The sample help output looks like this:

```
User Manager

A program to manage user accounts

Usage:
  sample-usage --help
  sample-usage add username:STRING email:STRING --password=STRING --role=STRING
  sample-usage delete username:STRING
  sample-usage list --output=OUTFILE --verbose

Commands:
  add | a  Add a new user
  delete | d  Delete an existing user
  list | l  List all users

Terms:
  username: STRING  Username of the user
  email: STRING  Email of the user

Parameters:
  --password | -p = STRING  Password for the user
  --role | -r = STRING  Role of the user
  --output | -o = OUTFILE  Output file for the list command

Flags:
  --help | -h  Display program help
  --verbose | -v  Enable verbose output
  --quiet | -q  Suppress output
```

This shows:

-   Program name
-   Proram description
-   List of usages, including the always-available `--help`. Each usage is presented in order:
    command, terms, paramaters, and flags.
-   Term types are marked with a colon and paramater types are marked with an equals sign which may
    be used in passing the argument. Type names are in all caps.
-   Each section shows its aliases alternating by pipes with the primary name first, followed by a
    description.

## Frequently Asked Questions

### Why use OptParser instead of getopt()?

OptParser supports positional arguments, does error checking, and prints a well-formatted help
message. `getopt()` doesn't do these things.

### What option type should I use?

Commands are just there to distinguish between usages. If you have two or three arguments of
different type that you won't confuse, then you can make them terms. But mostly you should give
options names like parameters, which take an argument, and flags, which don't. Named options are
harder to confuse.

### What's the difference between addUsage() and addUsageAll()?

`OptParser::addUsageAll()` is for the simple case when you only have one usage. Call this function
to add all terms, parameters, and flags without having to repeat their names.
`OptParser::addUsage()` is for the case when you have multiple commands. Call this function to add
each command with its associated terms, parameters, and flags by name.
