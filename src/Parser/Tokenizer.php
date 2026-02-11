<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Converts argv array into structured tokens.
 */
final class Tokenizer
{
    private bool $terminated = false;

    /**
     * @param array<int, string> $argv
     *
     * @return list<Token>
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

    public function isTerminated(): bool
    {
        return $this->terminated;
    }

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
