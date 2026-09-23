<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/Alat.php';
require_once __DIR__ . '/../models/Kategori.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class AlatController
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->model = new Alat($this->db);
    }

    public function index()
    {
        require_role(array('admin', 'petugas', 'peminjam'));
        $alat = $this->model->all(100, 0, trim(isset($_GET['q']) ? $_GET['q'] : ''));
        require __DIR__ . '/../views/alat/index.php';
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
        $kategori = (new Kategori($this->db))->all();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'kode_alat' => trim(isset($_POST['kode_alat']) ? $_POST['kode_alat'] : ''),
                'nama_alat' => trim(isset($_POST['nama_alat']) ? $_POST['nama_alat'] : ''),
                'kategori_id' => (int)(isset($_POST['kategori_id']) ? $_POST['kategori_id'] : 0),
                'deskripsi' => trim(isset($_POST['deskripsi']) ? $_POST['deskripsi'] : ''),
                'kondisi' => trim(isset($_POST['kondisi']) ? $_POST['kondisi'] : 'Baik'),
                'jumlah' => (int)(isset($_POST['jumlah']) ? $_POST['jumlah'] : 0),
                'jumlah_tersedia' => (int)(isset($_POST['jumlah_tersedia']) ? $_POST['jumlah_tersedia'] : 0),
                'lokasi' => trim(isset($_POST['lokasi']) ? $_POST['lokasi'] : ''),
                'foto' => $item ? $item['foto'] : null,
                'status' => isset($_POST['status']) ? $_POST['status'] : 'Tersedia',
            );
            $errors = validate_required($d, array(
                'kode_alat' => 'Kode alat',
                'nama_alat' => 'Nama alat',
                'kategori_id' => 'Kategori',
                'lokasi' => 'Lokasi',
            ));
            if ($d['jumlah'] < 1) $errors['jumlah'] = 'Jumlah minimal 1.';
            if ($d['jumlah_tersedia'] < 0 || $d['jumlah_tersedia'] > $d['jumlah']) $errors['jumlah_tersedia'] = 'Jumlah tersedia harus 0 sampai jumlah.';

            $up = valid_upload(isset($_FILES['foto']) ? $_FILES['foto'] : array());
            if ($up) $errors['foto'] = $up;

            if (!$errors && isset($_FILES['foto']) && (int)$_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) $ext = 'jpg';
                $name = bin2hex(random_bytes(12)) . '.' . $ext;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . '/' . $name)) {
                    $errors['foto'] = 'Foto gagal disimpan.';
                } else {
                    $d['foto'] = $name;
                    if ($item && !empty($item['foto'])) {
                        $oldFile = UPLOAD_DIR . '/' . basename($item['foto']);
                        if (is_file($oldFile) && $oldFile !== UPLOAD_DIR . '/' . $name) @unlink($oldFile);
                    }
                }
            }

            if (!$errors) {
                try {
                    if ($id) $this->model->update($id, $d); else $id = $this->model->create($d);
                    (new LogAktivitas($this->db))->add(current_user()['id'], ($item ? 'Mengubah' : 'Menambahkan') . ' alat', 'Alat');
                    flash('success', 'Data alat berhasil disimpan.');
                    redirect('alat');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = implode(' ', $errors);
            }
        }

        require __DIR__ . '/../views/alat/' . ($id ? 'edit' : 'create') . '.php';
    }

    public function detail(int $id)
    {
        require_role(array('admin', 'petugas', 'peminjam'));
        $item = $this->model->find($id);
        if (!$item) {
            http_response_code(404);
            require __DIR__ . '/../views/info/404.php';
            return;
        }
        require __DIR__ . '/../views/alat/detail.php';
    }

    public function delete(int $id)
    {
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('alat'); }
        verify_csrf();
        try {
            $this->model->delete($id);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menghapus alat', 'Alat');
            flash('success', 'Data alat dihapus.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('alat');
    }
}
