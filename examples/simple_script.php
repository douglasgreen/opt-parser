#!/usr/bin/env php
<?php

declare(strict_types=1);

use DouglasGreen\OptParser\OptParser;

require_once __DIR__ . '/../vendor/autoload.php';

$optParser = new OptParser('Simple Script', 'A script without subcommands');

$optParser
    ->addTerm('input', 'STRING', 'Input file to process')
    ->addParam(['output', 'o'], 'STRING', 'Output file path', null, false)
    ->addFlag(['verbose', 'v'], 'Enable verbose output')
    ->addUsageAll();

try {
    $input = $optParser->parse();
} catch (Exception $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(2);
}

$verbose = $input->get('verbose') ?? false;
$inputFile = $input->get('input');
$outputFile = $input->get('output');

if ($verbose) {
    echo "Verbose mode enabled\n";
    echo "Input file: $inputFile\n";
    if ($outputFile) {
        echo "Output file: $outputFile\n";
    }
}

echo "Processing $inputFile...\n";
if ($outputFile) {
    echo "Saving to $outputFile...\n";
}
echo "Done!\n";
