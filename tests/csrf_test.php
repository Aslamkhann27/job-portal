<?php
require_once __DIR__ . '/../includes/auth.php';

session_id('csrf-test-' . bin2hex(random_bytes(8)));
cc_start_session();

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

$token = cc_csrf_token();

assert_same(64, strlen($token), 'CSRF tokens should contain 32 random bytes encoded as hexadecimal.');
assert_same($token, cc_csrf_token(), 'The token should remain stable during a session so multiple forms can be submitted.');
assert_same(true, cc_validate_csrf_token($token), 'The current session token should validate.');
assert_same(false, cc_validate_csrf_token(''), 'An empty token must be rejected.');
assert_same(false, cc_validate_csrf_token(str_repeat('a', 64)), 'An unrelated token must be rejected.');

echo "CSRF protection tests passed." . PHP_EOL;
