<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/Pengembalian.php';
require_once __DIR__ . '/../models/Peminjaman.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class PengembalianController
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->model = new Pengembalian($this->db);
    }

    public function index()
    {
        require_role(array('admin', 'petugas', 'peminjam'));
        $returns = $this->model->all(has_role('peminjam') ? current_user()['id'] : null);
        require __DIR__ . '/../views/pengembalian/index.php';
    }

    public function create()
    {
        if (has_role('admin')) {
            $this->adminForm();
            return;
        }
        require_role('peminjam');

        $loans = (new Peminjaman($this->db))->all(current_user()['id']);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'peminjaman_id' => (int)(isset($_POST['peminjaman_id']) ? $_POST['peminjaman_id'] : 0),
                'tanggal_pengembalian' => isset($_POST['tanggal_pengembalian']) ? $_POST['tanggal_pengembalian'] : date('Y-m-d'),
                'kondisi_alat' => trim(isset($_POST['kondisi_alat']) ? $_POST['kondisi_alat'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
                'diproses_oleh' => current_user()['id'],
                'request_user_id' => current_user()['id'],
                'status' => 'MENUNGGU',
            );
            if ($d['peminjaman_id'] < 1 || !$d['kondisi_alat']) {
                $error = 'Transaksi dan kondisi alat wajib diisi.';
            } else {
                try {
                    $this->model->create($d);
                    (new LogAktivitas($this->db))->add(current_user()['id'], 'Mengajukan pengembalian', 'Pengembalian');
                    flash('success', 'Pengajuan pengembalian berhasil.');
                    redirect('pengembalian');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/pengembalian/detail.php';
    }

    public function adminForm($id = null)
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
            $d = array(
                'peminjaman_id' => (int)(isset($_POST['peminjaman_id']) ? $_POST['peminjaman_id'] : 0),
                'tanggal_pengembalian' => isset($_POST['tanggal_pengembalian']) ? $_POST['tanggal_pengembalian'] : date('Y-m-d'),
                'kondisi_alat' => trim(isset($_POST['kondisi_alat']) ? $_POST['kondisi_alat'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
            );
            if ($d['peminjaman_id'] < 1 || !$d['kondisi_alat']) {
                $error = 'Transaksi dan kondisi alat wajib diisi.';
            } else {
                try {
                    if ($id) {
                        $this->model->update($id, $d);
                    } else {
                        $d['diproses_oleh'] = current_user()['id'];
                        $this->model->create($d);
                    }
                    flash('success', 'Data pengembalian berhasil disimpan.');
                    redirect('pengembalian');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
        $loans = (new Peminjaman($this->db))->all();
        require __DIR__ . '/../views/pengembalian/admin_form.php';
    }

    public function delete(int $id)
    {
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pengembalian');
        verify_csrf();
        try {
            $this->model->delete($id);
            flash('success', 'Data pengembalian dihapus.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('pengembalian');
    }

    public function process(int $id)
    {
        require_role(array('admin', 'petugas'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pengembalian');
        verify_csrf();
        try {
            $this->model->process($id, current_user()['id']);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Memproses pengembalian', 'Pengembalian');
            flash('success', 'Pengembalian diproses dan stok dikembalikan.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('pengembalian');
    }
}
