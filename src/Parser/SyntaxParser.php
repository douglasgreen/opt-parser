<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\OptionInterface;
use DouglasGreen\OptParser\Option\OptionRegistry;
use DouglasGreen\OptParser\Option\Param;

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

        foreach ($tokens as $i => $token) {
            if ($token->type === TokenType::TERMINATOR) {
                $operandOnly = true;
                continue;
            }

            if ($operandOnly || $token->type === TokenType::OPERAND) {
                $result->operands[] = $token->value;
                continue;
            }

            if ($token->type === TokenType::LONG_OPTION) {
                $this->parseLongOption($token, $result);
                continue;
            }

            if ($token->type === TokenType::SHORT_OPTION) {
                $this->parseShortOption($token, $result, $expectingValue, $currentOption);
                continue;
            }
        }

        // Check if we were expecting a value that never came
        if ($expectingValue && $currentOption !== null) {
            throw new UsageException("Option '{$currentOption->getPrimaryName()}' requires a value");
        }

        $this->processOperands($result);

        return $result;
    }

    private function parseLongOption(Token $token, ParsingResult $result): void
    {
        $name = $token->value;

        try {
            $option = $this->optionRegistry->get($name);
        } catch (UsageException $e) {
            throw new UsageException("Unknown option '--{$name}'");
        }

        if ($option instanceof Command) {
            if ($result->command !== null) {
                throw new UsageException('Multiple commands specified');
            }
            $result->command = $name;
            return;
        }

        if ($option->acceptsValue()) {
            if ($token->attachedValue !== null) {
                $result->mappedOptions[$option->getPrimaryName()] = $token->attachedValue;
                $result->rawValues[$option->getPrimaryName()] = $token->attachedValue;
            } else {
                throw new UsageException("Option '--{$name}' requires a value");
            }
        } else {
            $result->mappedOptions[$option->getPrimaryName()] = true;
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
        } catch (UsageException $e) {
            throw new UsageException("Unknown option '-{$name}'");
        }

        if ($option instanceof Command) {
            if ($result->command !== null) {
                throw new UsageException('Multiple commands specified');
            }
            $result->command = $name;
            return;
        }

        if ($option->acceptsValue()) {
            if ($token->attachedValue !== null) {
                $result->mappedOptions[$option->getPrimaryName()] = $token->attachedValue;
                $result->rawValues[$option->getPrimaryName()] = $token->attachedValue;
            } else {
                $expectingValue = true;
                $currentOption = $option;
            }
        } else {
            $result->mappedOptions[$option->getPrimaryName()] = true;
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

        // Check required terms
        foreach ($terms as $i => $term) {
            if ($i >= $termIndex && $term->isRequired()) {
                throw new UsageException("Missing required argument: {$term->getPrimaryName()}");
            }
        }
    }
}
