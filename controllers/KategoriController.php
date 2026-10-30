<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/Kategori.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class KategoriController
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->model = new Kategori($this->db);
    }

    public function index()
    {
        require_role('admin');
        $kategori = $this->model->all();
        require __DIR__ . '/../views/kategori/index.php';
    }

    public function form($id = null)
    {
        require_role('admin');
        $item = $id ? $this->model->find($id) : null;
        if ($id && !$item) {
            http_response_code(404);
            require __DIR__ . '/../views/info/404.php';
            return;
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $n = trim(isset($_POST['nama_kategori']) ? $_POST['nama_kategori'] : '');
            $d = trim(isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '');
            if (!$n) {
                $error = 'Nama kategori wajib diisi.';
            } else {
                try {
                    if ($id) $this->model->update($id, $n, $d); else $id = $this->model->create($n, $d);
                    (new LogAktivitas($this->db))->add(current_user()['id'], ($item ? 'Mengubah' : 'Menambahkan') . ' kategori', 'Kategori');
                    flash('success', 'Kategori berhasil disimpan.');
                    redirect('kategori');
                } catch (Throwable $e) {
                    $error = 'Kategori tidak dapat disimpan. Pastikan nama kategori belum dipakai.';
                }
            }
        }
        require __DIR__ . '/../views/kategori/' . ($id ? 'edit' : 'create') . '.php';
    }

    public function delete(int $id)
    {
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('kategori');
        verify_csrf();
        try {
            $this->model->delete($id);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menghapus kategori', 'Kategori');
            flash('success', 'Kategori dihapus.');
        } catch (Throwable $e) {
            flash('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh alat.');
        }
        redirect('kategori');
    }
}
