<?php

function cc_start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Accept session IDs only from cookies and reject uninitialized IDs.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        $isHttps = isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && strtolower((string)$_SERVER['HTTPS']) !== 'off';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

function cc_read_json_file(string $filePath): array
{
    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    $decoded = json_decode($content, true);

    return is_array($decoded) ? $decoded : [];
}

/**
 * Persist JSON without exposing a partially-written file to concurrent readers.
 *
 * The temporary file is created in the destination directory so rename() stays
 * on the same filesystem and can atomically replace the previous data file.
 */
function cc_write_json_file(string $filePath, array $data): bool
{
    $dir = dirname($filePath);
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        return false;
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    $temporaryPath = tempnam($dir, '.cc-json-');
    if ($temporaryPath === false) {
        return false;
    }

    $bytesWritten = file_put_contents($temporaryPath, $json, LOCK_EX);
    if ($bytesWritten !== strlen($json)) {
        @unlink($temporaryPath);
        return false;
    }

    if (!rename($temporaryPath, $filePath)) {
        @unlink($temporaryPath);
        return false;
    }

    return true;
}

function cc_csrf_token(): string
{
    cc_start_session();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function cc_validate_csrf_token(string $token): bool
{
    cc_start_session();

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    return is_string($sessionToken)
        && $sessionToken !== ''
        && $token !== ''
        && hash_equals($sessionToken, $token);
}

function cc_users_file_path(): string
{
    return __DIR__ . '/../data/users.json';
}

function cc_load_users(): array
{
    return cc_read_json_file(cc_users_file_path());
}

function cc_save_users(array $users): bool
{
    return cc_write_json_file(cc_users_file_path(), $users);
}

function cc_find_user_by_email(string $email): ?array
{
    $normalizedEmail = strtolower(trim($email));
    foreach (cc_load_users() as $user) {
        if (($user['email'] ?? '') === $normalizedEmail) {
            return $user;
        }
    }

    return null;
}

function cc_admin_exists(): bool
{
    foreach (cc_load_users() as $user) {
        if (($user['role'] ?? '') === 'admin') {
            return true;
        }
    }

    return false;
}

function cc_register_user(string $name, string $email, string $password, ?string $role = null): array
{
    $users = cc_load_users();
    $normalizedEmail = strtolower(trim($email));

    foreach ($users as $existingUser) {
        if (($existingUser['email'] ?? '') === $normalizedEmail) {
            return ['success' => false, 'message' => 'Email already exists.'];
        }
    }

    $assignedRole = $role ?: 'candidate';
    if (empty($users)) {
        $assignedRole = 'admin';
    }

    if ($assignedRole === 'admin' && cc_admin_exists()) {
        return ['success' => false, 'message' => 'Only one admin account is allowed.'];
    }

    $user = [
        'id' => uniqid('user_', true),
        'name' => trim($name),
        'email' => $normalizedEmail,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $assignedRole,
        'created_at' => date('c')
    ];

    $users[] = $user;

    if (!cc_save_users($users)) {
        return ['success' => false, 'message' => 'Could not save user right now.'];
    }

    return ['success' => true, 'user' => $user];
}

function cc_authenticate_user(string $email, string $password): ?array
{
    $user = cc_find_user_by_email($email);
    if (!$user) {
        return null;
    }

    if (!password_verify($password, $user['password_hash'] ?? '')) {
        return null;
    }

    return $user;
}

function cc_login_user(array $user): void
{
    cc_start_session();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
}

function cc_logout_user(): void
{
    cc_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function cc_current_user(): ?array
{
    cc_start_session();
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function cc_is_logged_in(): bool
{
    return cc_current_user() !== null;
}

function cc_is_admin(): bool
{
    $user = cc_current_user();
    return $user && ($user['role'] ?? '') === 'admin';
}

function cc_is_recruiter(): bool
{
    $user = cc_current_user();
    if (!$user) {
        return false;
    }

    $role = $user['role'] ?? '';
    return $role === 'recruiter';
}

function cc_require_login(string $next = 'index.php'): void
{
    if (!cc_is_logged_in()) {
        header('Location: login.php?next=' . urlencode($next));
        exit;
    }
}

function cc_require_admin(): void
{
    if (!cc_is_logged_in()) {
        header('Location: login.php?next=' . urlencode('admin.php'));
        exit;
    }

    if (!cc_is_admin()) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Admin access required.</p>';
        exit;
    }
}

function cc_require_recruiter(): void
{
    if (!cc_is_logged_in()) {
        header('Location: login.php?next=' . urlencode('recruiter.php'));
        exit;
    }

    if (!cc_is_recruiter()) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Recruiter access required.</p>';
        exit;
    }
}

function cc_sanitize_next(string $next, string $default = 'index.php'): string
{
    $next = trim($next);
    if ($next === '') {
        return $default;
    }

    if (strpos($next, "\n") !== false || strpos($next, "\r") !== false) {
        return $default;
    }

    if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $next)) {
        return $default;
    }

    if (strpos($next, '/') === 0) {
        return ltrim($next, '/');
    }

    return $next;
}
