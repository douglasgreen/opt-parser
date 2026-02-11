<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\OptionInterface;
use DouglasGreen\OptParser\Option\OptionRegistry;

/**
 * POSIX.1-2017 compliant syntax parser.
 */
final readonly class SyntaxParser
{
    public function __construct(
        private OptionRegistry $optionRegistry,
    ) {}

    /**
     * @param list<Token> $tokens
     *
     * @throws UsageException on syntax violations
     */
    public function parse(array $tokens): ParsingResult
    {
        $result = new ParsingResult();
        $expectingValue = false;
        $currentOption = null;
        $operandOnly = false;

        foreach ($tokens as $token) {
            // If we were expecting a value for previous option, consume this token
            if ($expectingValue && $currentOption instanceof OptionInterface) {
                if ($token->type === TokenType::OPERAND || $token->type === TokenType::VALUE) {
                    $result->mappedOptions[$currentOption->getPrimaryName()] = $token->value;
                    $result->rawValues[$currentOption->getPrimaryName()] = $token->value;
                    $expectingValue = false;
                    $currentOption = null;
                    continue;
                }
                throw new UsageException(sprintf("Option '%s' requires a value", $currentOption->getPrimaryName()));
            }

            if ($token->type === TokenType::TERMINATOR) {
                $operandOnly = true;
                continue;
            }

            if ($operandOnly || $token->type === TokenType::OPERAND) {
                // Check if this operand is actually a command
                if ($result->command === null) {
                    try {
                        $potentialOption = $this->optionRegistry->get($token->value);
                        if ($potentialOption instanceof Command) {
                            $result->command = $potentialOption->getPrimaryName();
                            continue;
                        }
                    } catch (UsageException) {
                        // Not a known option/command, treat as operand
                    }
                }

                $result->operands[] = $token->value;
                continue;
            }

            if ($token->type === TokenType::LONG_OPTION) {
                $this->parseLongOption($token, $result, $expectingValue, $currentOption);
                continue;
            }

            if ($token->type === TokenType::SHORT_OPTION) {
                $this->parseShortOption($token, $result, $expectingValue, $currentOption);
                continue;
            }
        }

        // Check if we were expecting a value that never came
        if ($expectingValue && $currentOption instanceof OptionInterface) {
            throw new UsageException(sprintf("Option '%s' requires a value", $currentOption->getPrimaryName()));
        }

        $this->processOperands($result);

        return $result;
    }

    private function parseLongOption(Token $token, ParsingResult $result, bool &$expectingValue, ?OptionInterface &$currentOption): void
    {
        $name = $token->value;

        try {
            $option = $this->optionRegistry->get($name);
        } catch (UsageException) {
            throw new UsageException(sprintf("Unknown option '--%s'", $name));
        }

        if ($option instanceof Command) {
            if ($result->command !== null) {
                throw new UsageException('Multiple commands specified');
            }

            $result->command = $option->getPrimaryName();
            return;
        }

        if ($option->acceptsValue()) {
            if ($token->attachedValue !== null) {
                $result->mappedOptions[$option->getPrimaryName()] = $token->attachedValue;
                $result->rawValues[$option->getPrimaryName()] = $token->attachedValue;
            } else {
                // Set flag to consume next token as value
                $expectingValue = true;
                $currentOption = $option;
            }
        } else {
            $result->mappedOptions[$option->getPrimaryName()] = true;
            // Set rawValue so it is processed during validation
            $result->rawValues[$option->getPrimaryName()] = 'true';
        }
    }

    private function parseShortOption(
        Token $token,
        ParsingResult $result,
        bool &$expectingValue,
        ?OptionInterface &$currentOption,
    ): void {
        $name = $token->value;

        try {
            $option = $this->optionRegistry->get($name);
        } catch (UsageException) {
            throw new UsageException(sprintf("Unknown option '-%s'", $name));
        }

        if ($option instanceof Command) {
            if ($result->command !== null) {
                throw new UsageException('Multiple commands specified');
            }

            $result->command = $option->getPrimaryName();
            return;
        }

        if ($option->acceptsValue()) {
            if ($token->attachedValue !== null) {
                $result->mappedOptions[$option->getPrimaryName()] = $token->attachedValue;
                $result->rawValues[$option->getPrimaryName()] = $token->attachedValue;
            } else {
                // Set flag to consume next token as value
                $expectingValue = true;
                $currentOption = $option;
            }
        } else {
            $result->mappedOptions[$option->getPrimaryName()] = true;
            // Set rawValue so it is processed during validation
            $result->rawValues[$option->getPrimaryName()] = 'true';
        }
    }

    private function processOperands(ParsingResult $result): void
    {
        $terms = $this->optionRegistry->getTerms();
        $termIndex = 0;

        foreach ($result->operands as $operand) {
            if (isset($terms[$termIndex])) {
                $term = $terms[$termIndex];
                $result->mappedOptions[$term->getPrimaryName()] = $operand;
                $result->rawValues[$term->getPrimaryName()] = $operand;
                $termIndex++;
            } else {
                // Extra operands (non-options)
                $result->mappedOptions['_'][] = $operand;
            }
        }

        // Delegated requirement checks to OptParser::validateValues
    }
}
