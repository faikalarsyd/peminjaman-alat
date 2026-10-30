<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class AuthController
{
    private $db;
    private $user;
    public function __construct() { $this->db = Database::getConnection(); $this->user = new User($this->db); }

    public function login()
    {
        if (is_logged_in()) redirect('dashboard');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $pass = isset($_POST['password']) ? $_POST['password'] : '';
            $u = $this->user->findByEmail($email);
            if ($u && $u['status'] === 'Aktif' && password_verify($pass, $u['password'])) {
                login_user($u);
                (new LogAktivitas($this->db))->add($u['id'], 'Login ke aplikasi', 'Auth');
                redirect('dashboard');
            }
            $error = 'Email atau password salah, atau akun tidak aktif.';
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function register()
    {
        if (is_logged_in()) redirect('dashboard');
        $error = null;
        $errors = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'nama' => trim(isset($_POST['nama']) ? $_POST['nama'] : ''),
                'username' => trim(isset($_POST['username']) ? $_POST['username'] : ''),
                'email' => trim(isset($_POST['email']) ? $_POST['email'] : ''),
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'confirm' => isset($_POST['confirm']) ? $_POST['confirm'] : '',
            );
            $errors = validate_required($d, array('nama'=>'Nama Lengkap','username'=>'Username','email'=>'Email','password'=>'Password','confirm'=>'Konfirmasi Password'));
            if (!$errors && validate_email_field($d['email'])) $errors['email'] = validate_email_field($d['email']);
            if (!$errors && strlen($d['password']) < 6) $errors['password'] = 'Password minimal 6 karakter.';
            if (!$errors && $d['password'] !== $d['confirm']) $errors['confirm'] = 'Konfirmasi password tidak sama.';
            if (!$errors && $this->user->exists('username', $d['username'])) $errors['username'] = 'Username sudah digunakan.';
            if (!$errors && $this->user->exists('email', $d['email'])) $errors['email'] = 'Email sudah digunakan.';
            if (!$errors) {
                $roles = $this->user->roles();
                $role = null;
                foreach ($roles as $r) {
                    if (strtolower($r['nama_role']) === 'peminjam') { $role = $r; break; }
                }
                if (!$role) {
                    $error = 'Role Peminjam belum tersedia di database.';
                } else {
                    try {
                        $this->user->create(array('nama'=>$d['nama'],'username'=>$d['username'],'email'=>$d['email'],'password'=>$d['password'],'role_id'=>$role['id'],'status'=>'Aktif'));
                        flash('success', 'Registrasi berhasil. Silakan masuk.');
                        redirect('login');
                    } catch (Throwable $e) {
                        $error = 'Registrasi gagal. Silakan periksa data lalu coba lagi.';
                    }
                }
            }
            if (!$error && $errors) $error = implode(' ', $errors);
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function logout()
    {
        logout_user();
        header('Location: ' . base_url('login'));
        exit;
    }
}
