<?php
require_once __DIR__ . '/config.php';

// Konfigurasi default cocok untuk XAMPP dan AWeb Server yang memakai
// MySQL/MariaDB lokal dengan user root tanpa password.
$localConfig = __DIR__ . '/database.local.php';
if (is_file($localConfig)) {
    require $localConfig;
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'db_peminjaman_alat');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

class Database
{
    private static $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ));
        } catch (PDOException $e) {
            error_log('[NambutApp] Database connection failed: ' . $e->getMessage());
            throw new RuntimeException(
                'Koneksi database gagal. Pastikan MySQL/MariaDB aktif dan konfigurasi database sudah benar.'
            );
        }

        return self::$pdo;
    }
}
