<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Parser;

/**
 * Token types for lexical analysis.
 */
enum TokenType
{
    case SHORT_OPTION;      // -a

    case LONG_OPTION;       // --option

    case AGGREGATED_SHORT;  // -abc (clustered)

    case VALUE;             // argument to option

    case TERMINATOR;        // --

    case OPERAND;           // positional argument
}
