<?php
require_once __DIR__ . '/../includes/auth.php';

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

session_id('session-cookie-test-' . bin2hex(random_bytes(8)));
cc_start_session();

$params = session_get_cookie_params();
assert_same('/', $params['path'], 'Session cookies should use the application root path.');
assert_same(true, $params['httponly'], 'Session cookies must not be readable by JavaScript.');
assert_same('Lax', $params['samesite'], 'Session cookies should use SameSite=Lax.');
assert_same(0, $params['lifetime'], 'Session cookies should expire when the browser session ends.');
assert_same('1', ini_get('session.use_strict_mode'), 'Strict session ID validation must be enabled.');
assert_same('1', ini_get('session.use_only_cookies'), 'Session IDs must only be accepted from cookies.');

session_destroy();

echo "Session security tests passed." . PHP_EOL;
