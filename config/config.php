<?php

// Base directory aplikasi. Mengikuti lokasi index.php sehingga tetap benar
// saat project dipasang di XAMPP, AWeb Server, atau folder lain.
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '.' || $scriptDir === DIRECTORY_SEPARATOR) {
    $scriptDir = '';
}

define('APP_BASE', $scriptDir);
define('APP_NAME', 'NambutApp');
define('APP_VERSION', '2.0.0');

define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/uploads/alat');

function base_url(string $path = ''): string
{
    // Runtime menggunakan index.php?route=... sehingga tidak bergantung
    // pada mod_rewrite/.htaccess. Ini membuat link stabil di AWeb dan XAMPP.
    $parts = explode('?', ltrim($path, '/'), 2);
    $route = trim($parts[0], '/');
    $url = APP_BASE . '/index.php';

    if ($route !== '') {
        $url .= '?route=' . rawurlencode($route);
    }

    if (isset($parts[1]) && $parts[1] !== '') {
        $url .= ($route === '' ? '?' : '&') . $parts[1];
    }

    return $url;
}

function asset_url(string $path = ''): string
{
    return APP_BASE . '/assets/' . ltrim($path, '/');
}

function upload_url(string $path = ''): string
{
    return APP_BASE . '/uploads/' . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

date_default_timezone_set('Asia/Jakarta');
