#!/bin/bash

# Test Commands for Simple Script
# This script tests the addUsageAll functionality

SCRIPT="php $(dirname "$0")/simple_script.php"
FAILED=0
PASSED=0

run_test() {
    local description="$1"
    shift
    local expected_exit="$1"
    shift
    local expected_pattern="$1"
    shift

    echo "Testing: $description"
    echo "  Command: $SCRIPT $*"

    output=$($SCRIPT "$@" 2>&1)
    exit_code=$?

    # Convert output to single line for pattern matching (handles multiline patterns)
    local single_line_output
    single_line_output=$(echo "$output" | tr '\n' ' ')

    if [ $exit_code -eq $expected_exit ] && echo "$single_line_output" | grep -q "$expected_pattern"; then
        echo "  ✓ PASS"
        ((PASSED++))
    else
        echo "  ✗ FAIL"
        echo "    Expected exit: $expected_exit, Got: $exit_code"
        echo "    Expected pattern: $expected_pattern"
        echo "    Output: $(echo "$output" | head -c 200)"
        ((FAILED++))
    fi
    echo ""
}

echo "=== Simple Script Test Suite ==="
echo ""

run_test "Basic execution" 0 "Processing test.txt" test.txt
run_test "With verbose flag" 0 "Verbose mode enabled.*Processing test.txt" test.txt -v
run_test "With output param" 0 "Saving to out.txt" test.txt -o out.txt
run_test "Missing required term" 2 "Option 'input' is required"
run_test "Unknown option" 2 "Unknown option" test.txt --unknown

echo "=== Test Summary ==="
echo "Passed: $PASSED"
echo "Failed: $FAILED"

if [ $FAILED -gt 0 ]; then
    exit 1
else
    echo "All tests passed!"
    exit 0
fi
