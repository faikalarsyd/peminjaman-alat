<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class UserController
{
    private $db;
    private $model;
    public function __construct() { $this->db = Database::getConnection(); $this->model = new User($this->db); }

    public function index()
    {
        require_role('admin');
        $users = $this->model->all();
        require __DIR__ . '/../views/user/index.php';
    }

    public function form($id = null)
    {
        require_role('admin');
        $user = $id ? $this->model->find($id) : null;
        if ($id && !$user) {
            http_response_code(404);
            require __DIR__ . '/../views/info/404.php';
            return;
        }
        $roles = $this->model->roles();
        $error = null;
        $errors = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'nama' => trim(isset($_POST['nama']) ? $_POST['nama'] : ''),
                'username' => trim(isset($_POST['username']) ? $_POST['username'] : ''),
                'email' => trim(isset($_POST['email']) ? $_POST['email'] : ''),
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'role_id' => (int)(isset($_POST['role_id']) ? $_POST['role_id'] : 0),
                'status' => isset($_POST['status']) ? $_POST['status'] : 'Aktif',
            );
            $errors = validate_required($d, array('nama'=>'Nama','username'=>'Username','email'=>'Email'));
            if (!$errors && validate_email_field($d['email'])) $errors['email'] = validate_email_field($d['email']);
            if (!$id && !$d['password']) $errors['password'] = 'Password wajib diisi.';
            if ($d['password'] && strlen($d['password']) < 6) $errors['password'] = 'Password minimal 6 karakter.';
            if (!in_array($d['status'], array('Aktif','Nonaktif'), true)) $errors['status'] = 'Status pengguna tidak valid.';
            if (!$d['role_id']) $errors['role_id'] = 'Role wajib dipilih.';
            if (!$errors && $this->model->exists('username', $d['username'], $id)) $errors['username'] = 'Username sudah digunakan.';
            if (!$errors && $this->model->exists('email', $d['email'], $id)) $errors['email'] = 'Email sudah digunakan.';
            if (!$errors) {
                try {
                    if ($id) { $this->model->update($id, $d); $msg = 'Mengubah pengguna'; }
                    else { $this->model->create($d); $msg = 'Menambahkan pengguna'; }
                    (new LogAktivitas($this->db))->add(current_user()['id'], $msg, 'User');
                    flash('success', 'Data pengguna berhasil disimpan.');
                    redirect('users');
                } catch (Throwable $e) {
                    $error = 'Data pengguna tidak dapat disimpan.';
                }
            } else {
                $error = implode(' ', $errors);
            }
        }
        require __DIR__ . '/../views/user/' . ($id ? 'edit' : 'create') . '.php';
    }

    public function delete(int $id)
    {
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');
        verify_csrf();
        if ($id === (int)current_user()['id']) {
            flash('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
            redirect('users');
        }
        try {
            $this->model->delete($id);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menghapus pengguna', 'User');
            flash('success', 'Pengguna dihapus.');
        } catch (Throwable $e) {
            flash('error', 'Pengguna tidak dapat dihapus karena masih memiliki data transaksi/log.');
        }
        redirect('users');
    }
}
