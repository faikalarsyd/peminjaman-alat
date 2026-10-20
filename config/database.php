<?php
require_once __DIR__ . '/config.php';

// Opsional: salin database.local.example.php menjadi database.local.php
// lalu sesuaikan jika host, port, nama database, atau kredensial berbeda.
$localConfig = __DIR__ . '/database.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

if (!defined('DB_HOST')) {
    $envHost = getenv('DB_HOST');
    define('DB_HOST', ($envHost !== false && $envHost !== '') ? $envHost : '127.0.0.1');
}
if (!defined('DB_PORT')) {
    $envPort = getenv('DB_PORT');
    define('DB_PORT', ($envPort !== false && $envPort !== '') ? $envPort : '3306');
}
if (!defined('DB_NAME')) {
    $envName = getenv('DB_NAME');
    define('DB_NAME', ($envName !== false && $envName !== '') ? $envName : 'db_peminjaman_alat');
}
if (!defined('DB_USER')) {
    $envUser = getenv('DB_USER');
    define('DB_USER', ($envUser !== false && $envUser !== '') ? $envUser : 'root');
}
if (!defined('DB_PASS')) {
    $envPassword = getenv('DB_PASS');
    define('DB_PASS', $envPassword !== false ? $envPassword : '');
}

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
                'Koneksi database gagal. Pastikan MySQL/MariaDB aktif, extension PDO MySQL tersedia, database peminjaman sudah diimpor, dan kredensial di config/database.local.php benar.'
            );
        }

        return self::$pdo;
    }

    public static function disconnect()
    {
        self::$pdo = null;
    }
}
