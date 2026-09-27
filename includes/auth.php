<?php

function cc_start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
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

function cc_write_json_file(string $filePath, array $data): bool
{
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
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
