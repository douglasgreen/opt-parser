<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

use DouglasGreen\OptParser\Exception\UsageException;
use DouglasGreen\OptParser\Option\Command;
use DouglasGreen\OptParser\Option\OptionInterface;
use DouglasGreen\OptParser\Option\OptionRegistry;

/**
 * POSIX.1-2017 compliant syntax parser for command-line arguments.
 *
 * Transforms a stream of tokens into a structured parsing result by applying
 * POSIX option parsing rules. Handles long options, short options, clustered
 * flags, option values, commands, and operands according to the POSIX.1-2017
 * specification.
 *
 * @package DouglasGreen\OptParser\Parser
 *
 * @api
 *
 * @since 1.0.0
 * @see Tokenizer For token generation from raw argv arrays
 * @see ParsingResult For the output structure
 *
 * @example
 * ```php
 * $registry = new OptionRegistry();
 * $registry->register(new Flag(['verbose', 'v'], 'Enable verbose output'));
 * $registry->register(new Param(['output', 'o'], 'Output file', 'STRING'));
 *
 * $tokenizer = new Tokenizer();
 * $tokens = $tokenizer->tokenize(['-v', '--output', 'result.txt']);
 *
 * $parser = new SyntaxParser($registry);
 * $result = $parser->parse($tokens);
 * ```
 */
final readonly class SyntaxParser
{
    /**
     * Initializes the syntax parser with an option registry.
     *
     * @param OptionRegistry $optionRegistry Registry containing known options and commands
     */
    public function __construct(
        private OptionRegistry $optionRegistry,
    ) {}

    /**
     * Parses a list of tokens into a structured parsing result.
     *
     * Processes tokens according to POSIX syntax rules, extracting commands,
     * options with their values, and operands. Handles value consumption for
     * parameters that require values and validates against the option registry.
     *
     * @param list<Token> $tokens Tokens produced by the Tokenizer
     *
     * @return ParsingResult Structured result containing mapped options and operands
     *
     * @throws UsageException When syntax violations occur (unknown options, missing values, multiple commands)
     *
     * @example
     * ```php
     * $tokens = $tokenizer->tokenize($argv);
     * $result = $parser->parse($tokens);
     *
     * $command = $result->command;
     * $verbose = $result->mappedOptions['verbose'] ?? false;
     * ```
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
                    $this->addOptionValue($result, $currentOption, $token->value);
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

    /**
     * Parses a long option token (e.g., --verbose, --output=file).
     *
     * Resolves the option name against the registry, handles attached values,
     * and sets up value consumption for options requiring separate value tokens.
     *
     * @param Token $token The long option token to parse
     * @param ParsingResult $result The parsing result to populate
     * @param bool $expectingValue Reference flag indicating if a value is expected
     * @param OptionInterface|null $currentOption Reference to the option awaiting a value
     *
     * @throws UsageException When the option is unknown or other syntax errors occur
     */
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
                $this->addOptionValue($result, $option, $token->attachedValue);
            } else {
                // Set flag to consume next token as value
                $expectingValue = true;
                $currentOption = $option;
            }
        } else {
            $this->addFlagOccurrence($result, $option);
        }
    }

    /**
     * Parses a short option token (e.g., -v, -ofile).
     *
     * Resolves the option name against the registry, handles attached values,
     * and sets up value consumption for options requiring separate value tokens.
     *
     * @param Token $token The short option token to parse
     * @param ParsingResult $result The parsing result to populate
     * @param bool $expectingValue Reference flag indicating if a value is expected
     * @param OptionInterface|null $currentOption Reference to the option awaiting a value
     *
     * @throws UsageException When the option is unknown or other syntax errors occur
     */
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
                $this->addOptionValue($result, $option, $token->attachedValue);
            } else {
                // Set flag to consume next token as value
                $expectingValue = true;
                $currentOption = $option;
            }
        } else {
            $this->addFlagOccurrence($result, $option);
        }
    }

    /**
     * Adds a value for a parameter option, handling multiple values.
     *
     * For options with multiple=true, values are collected in an array.
     * For single-value options, the value is stored directly.
     *
     * @param ParsingResult $result The parsing result to populate
     * @param OptionInterface $option The option receiving the value
     * @param string $value The value to add
     */
    private function addOptionValue(ParsingResult $result, OptionInterface $option, string $value): void
    {
        $name = $option->getPrimaryName();

        if ($option->isMultiple()) {
            if (!isset($result->mappedOptions[$name])) {
                $result->mappedOptions[$name] = [];
                $result->rawValues[$name] = [];
            }

            $result->mappedOptions[$name][] = $value;
            $result->rawValues[$name] = (array) $result->rawValues[$name];
            $result->rawValues[$name][] = $value;
        } else {
            $result->mappedOptions[$name] = $value;
            $result->rawValues[$name] = $value;
        }
    }

    /**
     * Records a flag occurrence, handling multiple occurrences.
     *
     * For flags with multiple=true, occurrences are counted.
     * For single flags, the presence is marked as true.
     *
     * @param ParsingResult $result The parsing result to populate
     * @param OptionInterface $option The flag option being recorded
     */
    private function addFlagOccurrence(ParsingResult $result, OptionInterface $option): void
    {
        $name = $option->getPrimaryName();

        if ($option->isMultiple()) {
            if (!isset($result->mappedOptions[$name])) {
                $result->mappedOptions[$name] = 0;
                $result->rawValues[$name] = [];
            }

            $result->mappedOptions[$name]++;
            $result->rawValues[$name] = (array) $result->rawValues[$name];
            $result->rawValues[$name][] = 'true';
        } else {
            $result->mappedOptions[$name] = true;
            $result->rawValues[$name] = 'true';
        }
    }

    /**
     * Processes operands and maps them to defined terms.
     *
     * Assigns positional operands to their corresponding term definitions
     * in order. For terms with multiple=true, collects all remaining operands
     * into an array. Extra operands that don't match defined terms are stored
     * in the '_' key for later access.
     *
     * @param ParsingResult $result The parsing result containing operands to process
     */
    private function processOperands(ParsingResult $result): void
    {
        $terms = $this->optionRegistry->getTerms();
        $termIndex = 0;

        foreach ($result->operands as $operand) {
            if (isset($terms[$termIndex])) {
                $term = $terms[$termIndex];
                $primaryName = $term->getPrimaryName();

                if ($term->isMultiple()) {
                    // Collect multiple operands for this term
                    if (!isset($result->mappedOptions[$primaryName])) {
                        $result->mappedOptions[$primaryName] = [];
                        $result->rawValues[$primaryName] = [];
                    }

                    $result->mappedOptions[$primaryName][] = $operand;
                    $result->rawValues[$primaryName] = (array) $result->rawValues[$primaryName];
                    $result->rawValues[$primaryName][] = $operand;
                    // Don't increment termIndex - keep collecting for this term
                } else {
                    $result->mappedOptions[$primaryName] = $operand;
                    $result->rawValues[$primaryName] = $operand;
                    $termIndex++;
                }
            } else {
                // Extra operands (non-options)
                $result->mappedOptions['_'][] = $operand;
            }
        }

        // Delegated requirement checks to OptParser::validateValues
    }
}
