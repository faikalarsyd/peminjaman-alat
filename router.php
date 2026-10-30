<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/redirect.php';

$route = isset($_GET['route']) ? (string)$_GET['route'] : '';
$route = trim($route, " /\t\n\r\0\x0B");
$route = preg_replace('#/+#', '/', $route);
$route = strtolower($route);

$parts = $route === '' ? array() : explode('/', $route);
$name = isset($parts[0]) && $parts[0] !== '' ? $parts[0] : 'dashboard';
$action = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'index';
$id = isset($parts[2]) && ctype_digit($parts[2]) ? (int)$parts[2] : null;

try {
    if ($name === 'login') {
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        exit;
    }
    if ($name === 'register') {
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->register();
        exit;
    }
    if ($name === 'logout') {
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->logout();
        exit;
    }
    if ($name === 'dashboard') {
        require_once __DIR__ . '/controllers/DashboardController.php';
        (new DashboardController())->index();
        exit;
    }

    if ($name === 'info') {
        require_login();
        $files = array(
            'index' => 'index.php',
            'bantuan' => 'bantuan.php',
            'tentang' => 'tentang.php',
        );
        $file = isset($files[$action]) ? $files[$action] : 'index.php';
        require __DIR__ . '/views/info/' . $file;
        exit;
    }

    $map = array(
        'users' => 'UserController',
        'alat' => 'AlatController',
        'kategori' => 'KategoriController',
        'peminjaman' => 'PeminjamanController',
        'pengembalian' => 'PengembalianController',
        'laporan' => 'LaporanController',
        'log' => 'LogController',
        'profile' => 'UserProfileController',
        'pengaturan' => 'PengaturanController',
    );

    if (!isset($map[$name])) {
        http_response_code(404);
        require __DIR__ . '/views/info/404.php';
        exit;
    }

    require_once __DIR__ . '/controllers/' . $map[$name] . '.php';
    $class = $map[$name];
    $controller = new $class();

    if (in_array($name, array('users', 'alat', 'kategori'), true) && in_array($action, array('create', 'edit'), true)) {
        $controller->form($action === 'edit' ? $id : null);
        exit;
    }
    if ($name === 'peminjaman' && $action === 'create') {
        $controller->create();
        exit;
    }
    if ($name === 'pengembalian' && $action === 'create') {
        $controller->create();
        exit;
    }
    if ($name === 'profile' && $action === 'password') {
        $controller->password();
        exit;
    }
    if ($action === 'index') {
        $controller->index();
        exit;
    }
    if ($action === 'detail') {
        $controller->detail((int)$id);
        exit;
    }
    if ($action === 'delete') {
        $controller->delete((int)$id);
        exit;
    }
    if ($action === 'approve') {
        $controller->approve((int)$id);
        exit;
    }
    if ($action === 'reject') {
        $controller->reject((int)$id);
        exit;
    }
    if ($action === 'process') {
        $controller->process((int)$id);
        exit;
    }
    if ($action === 'edit' && method_exists($controller, 'adminForm')) {
        $controller->adminForm((int)$id);
        exit;
    }
    if ($action === 'create' && method_exists($controller, 'adminForm')) {
        $controller->adminForm(null);
        exit;
    }
    if ($action === 'form' && method_exists($controller, 'form')) {
        $controller->form((int)$id);
        exit;
    }

    http_response_code(404);
    require __DIR__ . '/views/info/404.php';
} catch (Throwable $e) {
    $log = '[NambutApp] ' . date('Y-m-d H:i:s') . ' ' . $e->getMessage() . PHP_EOL;
    error_log($log);

    http_response_code(500);
    $message = 'Terjadi kendala pada aplikasi. Periksa koneksi database atau log server, lalu coba lagi.';
    require __DIR__ . '/views/info/500.php';
}
