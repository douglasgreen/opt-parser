#!/bin/bash

# Test Commands for User Manager
# This script tests various argument combinations

SCRIPT="php $(dirname "$0")/user_manager.php"
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

    if [ $exit_code -eq $expected_exit ] && echo "$output" | grep -q "$expected_pattern"; then
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

echo "=== User Manager Test Suite ==="
echo ""

# ADD Command Tests
echo "## ADD Command Tests"
run_test "Add user with all parameters" 0 "SUCCESS" add john -p secret123 -r admin -e john@example.com -v
run_test "Add user with short alias" 0 "SUCCESS" a jane --password secret123 -e jane@example.com
run_test "Add user missing password" 2 "Option 'password' is required" add bob -e bob@example.com
run_test "Add user invalid email" 2 "Invalid email" add bob -e invalid-email -p secret123
run_test "Add user short password" 1 "at least 6 characters" add bob -p "123"
run_test "Add user quiet mode" 0 "SUCCESS" add quietuser -p secret123 -q

# DELETE Command Tests
echo "## DELETE Command Tests"
run_test "Delete with force" 0 "SUCCESS" delete testuser --force -v
run_test "Delete without force" 1 "requires --force" delete testuser
run_test "Delete missing username" 2 "Option 'usernames' is required" delete
run_test "Delete clustered options" 0 "SUCCESS" delete testuser -fv

# LIST Command Tests
echo "## LIST Command Tests"
run_test "List simple" 0 "admin" list
run_test "List verbose" 0 "admin@example.com" list -v
run_test "List to file" 0 "SUCCESS" list -o /tmp/test_users.txt -v
run_test "List invalid path" 1 "Directory not writable" list -o /nonexistent/path/file.txt

# SEARCH Command Tests (Multiple Terms)
echo "## SEARCH Command Tests (Multiple Terms)"
run_test "Search single user" 0 "admin" search admin
run_test "Search multiple users" 0 "jane_smith" search admin jane_smith bob_wilson
run_test "Search with verbose" 0 "admin@example.com" search admin -v
run_test "Search non-existent user" 1 "Not found" search nonexistent
run_test "Search mixed found/not-found" 0 "admin" search admin nonexistent john_doe
run_test "Search with short alias" 0 "admin" s admin jane_smith

# Multiple Parameter Tests
echo "## Multiple Parameter Tests"
run_test "Add user with multiple tags" 0 "tags: admin, developer" add testuser -p secret123 --tag admin --tag developer
run_test "Add user with short tag alias" 0 "tags: dev, qa" add testuser -p secret123 -t dev -t qa
run_test "Search with tags filter" 0 "Tags: tag1, tag2" search admin --tag tag1 --tag tag2 -v

# Multiple Flag Tests (Verbosity Levels)
echo "## Multiple Flag Tests (Verbosity Levels)"
run_test "Verbosity level 1 (-v)" 0 "Verbosity level: 1" add testuser -p secret123 -v
run_test "Verbosity level 2 (-v -v)" 0 "Verbosity level: 2" add testuser -p secret123 -v -v
run_test "Verbosity level 3 (-v -v -v)" 0 "Verbosity level: 3" add testuser -p secret123 -v -v -v
run_test "Verbosity level 2 (mixed)" 0 "Verbosity level: 2" add testuser -p secret123 -v --verbose
run_test "Verbosity level 4 (extreme)" 0 "Verbosity level: 4" add testuser -p secret123 -vvvv
run_test "Search with verbosity 2" 0 "Verbosity level: 2" search admin -vv

# Combined Multiple Params and Flags
echo "## Combined Multiple Params and Flags"
run_test "Add with tags and verbosity" 0 "tags: tester, reviewer.*Verbosity level: 2" add newuser -p secret123 --tag tester --tag reviewer -vv
run_test "Search with tags and high verbosity" 0 "Tags: important, verified.*Verbosity level: 3" search admin --tag important --tag verified -vvv

# Edge Cases
echo "## Edge Cases"
run_test "No command" 2 "No command specified"
run_test "Unknown command" 2 "Unknown command" unknowncmd
run_test "Unknown option" 2 "Unknown option" add user user@test.com -p pass --unknown
run_test "Help flag" 0 "Usage:" --help
run_test "Version flag" 0 "user_manager.php" --version
run_test "Option terminator" 2 "Option 'password' is required" add -- -username test@test.com -p secret123

# Summary
echo "=== Test Summary ==="
echo "Passed: $PASSED"
echo "Failed: $FAILED"

if [ $FAILED -gt 0 ]; then
    exit 1
else
    echo "All tests passed!"
    exit 0
fi
