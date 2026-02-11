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
run_test "Add user with all parameters" 0 "SUCCESS" add john john@example.com -p secret123 -r admin -v
run_test "Add user with short alias" 0 "SUCCESS" a jane jane@example.com --password secret123
run_test "Add user missing password" 2 "Password is required" add bob bob@example.com
run_test "Add user invalid email" 2 "Invalid email" add bob invalid-email -p secret123
run_test "Add user short password" 1 "at least 6 characters" add bob bob@example.com -p "123"
run_test "Add user quiet mode" 0 "SUCCESS" add quietuser quiet@example.com -p secret123 -q

# DELETE Command Tests
echo "## DELETE Command Tests"
run_test "Delete with force" 0 "SUCCESS" delete testuser --force -v
run_test "Delete without force" 1 "requires --force" delete testuser
run_test "Delete missing username" 2 "Username is required" delete
run_test "Delete clustered options" 0 "SUCCESS" delete testuser -fv

# LIST Command Tests
echo "## LIST Command Tests"
run_test "List simple" 0 "admin" list
run_test "List verbose" 0 "admin@example.com" list -v
run_test "List to file" 0 "SUCCESS" list -o /tmp/test_users.txt -v
run_test "List invalid path" 1 "Directory not writable" list -o /nonexistent/path/file.txt

# Edge Cases
echo "## Edge Cases"
run_test "No command" 2 "No command specified"
run_test "Unknown command" 2 "Unknown command" unknowncmd
run_test "Unknown option" 2 "Unknown option" add user user@test.com -p pass --unknown
run_test "Help flag" 0 "Usage:" --help
run_test "Version flag" 0 "User Manager" --version
run_test "Option terminator" 0 "SUCCESS" add -- -username test@test.com -p secret123

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
