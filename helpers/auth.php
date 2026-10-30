<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function login_user(array $user)
{
    // Jangan menyimpan password hash ke session browser.
    unset($user['password']);
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
}

function logout_user()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . base_url('login'));
        exit;
    }
}

function has_role($roles): bool
{
    $roles = (array)$roles;
    $current = current_user();
<<<<<<< HEAD
    $currentRole = is_array($current) && isset($current['role']) ? strtolower(trim((string)$current['role'])) : '';
=======
    $currentRole = is_array($current) && isset($current['role']) ? strtolower((string)$current['role']) : '';
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f

    if ($currentRole === '') {
        return false;
    }

<<<<<<< HEAD
    $roles = array_map(static function ($role) { return strtolower(trim((string)$role)); }, $roles);
=======
    $roles = array_map('strtolower', $roles);
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
    return in_array($currentRole, $roles, true);
}

function require_role($roles)
{
    require_login();

    if (!has_role($roles)) {
        http_response_code(403);
        $message = 'Anda tidak memiliki akses ke halaman ini.';
        require __DIR__ . '/../views/info/403.php';
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf()
{
    $sessionToken = isset($_SESSION['_csrf']) ? (string)$_SESSION['_csrf'] : '';
    $postedToken = isset($_POST['_csrf']) ? (string)$_POST['_csrf'] : '';

    if ($sessionToken === '' || $postedToken === '' || !hash_equals($sessionToken, $postedToken)) {
        http_response_code(419);
        exit('Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.');
    }
}
