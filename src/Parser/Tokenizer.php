<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Converts command-line argument arrays into structured tokens.
 *
 * Transforms raw argv arrays into a list of Token objects representing
 * long options, short options, operands, and terminators. Implements
 * POSIX tokenization rules including handling of -- terminator and
 * attached option values.
 *
 * @package DouglasGreen\OptParser\Parser
 * @api
 * @since 1.0.0
 * @see Token For the token value object
 * @see SyntaxParser For parsing tokens into results
 *
 * @example
 * ```php
 * $tokenizer = new Tokenizer();
 *
 * $tokens = $tokenizer->tokenize(['script.php', '--verbose', '-o', 'file.txt', '--', 'operand']);
 * // Produces tokens for: LONG_OPTION(verbose), SHORT_OPTION(o), OPERAND(file.txt),
 * //                      TERMINATOR(--), OPERAND(operand)
 *
 * foreach ($tokens as $token) {
 *     echo $token->type->name . ': ' . $token->value . PHP_EOL;
 * }
 * ```
 */
final class Tokenizer
{
    /**
     * Flag indicating if the terminator (--) has been encountered.
     *
     * Once terminated, all subsequent arguments are treated as operands
     * regardless of their format.
     *
     * @var bool
     */
    private bool $terminated = false;

    /**
     * Tokenizes an argv array into a list of Token objects.
     *
     * Processes each argument according to POSIX rules: long options
     * start with --, short options start with -, and -- terminates
     * option processing. Handles attached values (e.g., --opt=value, -ovalue).
     *
     * @param array<int, string> $argv Command-line arguments (typically from $_SERVER['argv'])
     * @return list<Token> List of structured tokens
     *
     * @example
     * ```php
     * $tokens = $tokenizer->tokenize(['--config=my.ini', '-v', 'input.txt']);
     * // $tokens[0] = Token(LONG_OPTION, 'config', 'my.ini')
     * // $tokens[1] = Token(SHORT_OPTION, 'v')
     * // $tokens[2] = Token(OPERAND, 'input.txt')
     * ```
     */
    public function tokenize(array $argv): array
    {
        $this->terminated = false;
        $tokens = [];
        $count = count($argv);

        for ($i = 0; $i < $count; $i++) {
            $arg = $argv[$i];

            if ($this->terminated) {
                $tokens[] = new Token(TokenType::OPERAND, $arg);
                continue;
            }

            if ($arg === '--') {
                $this->terminated = true;
                $tokens[] = new Token(TokenType::TERMINATOR, '--');
                continue;
            }

            if (str_starts_with($arg, '--')) {
                $this->tokenizeLongOption($arg, $tokens);
            } elseif (str_starts_with($arg, '-') && strlen($arg) > 1) {
                $this->tokenizeShortOption($arg, $tokens);
            } else {
                $tokens[] = new Token(TokenType::OPERAND, $arg);
            }
        }

        return $tokens;
    }

    /**
     * Returns whether the tokenizer encountered a terminator (--).
     *
     * After a terminator, all remaining arguments are treated as operands.
     * This method is useful for understanding the tokenization state.
     *
     * @return bool True if -- was encountered, false otherwise
     */
    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    /**
     * Tokenizes a long option argument (e.g., --verbose, --output=file).
     *
     * Parses the argument to extract the option name and any attached value
     * following an equals sign.
     *
     * @param string $arg The long option argument without the -- prefix handling
     * @param list<Token> $tokens The token array to append to (passed by reference)
     */
    private function tokenizeLongOption(string $arg, array &$tokens): void
    {
        $eqPos = strpos($arg, '=');

        if ($eqPos === false) {
            $tokens[] = new Token(TokenType::LONG_OPTION, substr($arg, 2));
        } else {
            $name = substr($arg, 2, $eqPos - 2);
            $value = substr($arg, $eqPos + 1);
            $tokens[] = new Token(TokenType::LONG_OPTION, $name, $value);
        }
    }

    /**
     * Tokenizes a short option argument (e.g., -v, -abc, -ofile).
     *
     * Handles single short options, clustered flags (-abc), and attached
     * values (-ovalue). Distinguishes between numeric attached values
     * and clustered flags based on character type.
     *
     * @param string $arg The short option argument starting with -
     * @param list<Token> $tokens The token array to append to (passed by reference)
     */
    private function tokenizeShortOption(string $arg, array &$tokens): void
    {
        $chars = substr($arg, 1);

        if (strlen($chars) === 1) {
            $tokens[] = new Token(TokenType::SHORT_OPTION, $chars);
            return;
        }

        // Check for attached value (e.g., -ovalue)
        $first = $chars[0];
        $rest = substr($chars, 1);

        if (!ctype_digit($rest)) {
            // Likely an attached argument like -ovalue or -abc (clustered)
            $tokens[] = new Token(TokenType::SHORT_OPTION, $first, $rest);
        } else {
            // Clustered flags like -abc
            $len = strlen($chars);
            for ($j = 0; $j < $len - 1; $j++) {
                $tokens[] = new Token(TokenType::SHORT_OPTION, $chars[$j]);
            }

            $tokens[] = new Token(TokenType::SHORT_OPTION, $chars[$len - 1]);
        }
    }
}
