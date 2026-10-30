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
<<<<<<< HEAD
            $selectedLoanId = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : 0;
            $this->adminForm(null, $selectedLoanId);
=======
            $this->adminForm();
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
            return;
        }
        require_role('peminjam');

        $loans = (new Peminjaman($this->db))->all(current_user()['id']);
        $error = null;
        $returnTime = date('H:i');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $returnTime = trim(isset($_POST['tanggal_pengembalian']) ? (string)$_POST['tanggal_pengembalian'] : '');
            if (valid_time_value($returnTime)) {
                $returnTime = substr($returnTime, 0, 5);
            }
            $d = array(
                'peminjaman_id' => (int)(isset($_POST['peminjaman_id']) ? $_POST['peminjaman_id'] : 0),
                'tanggal_pengembalian' => valid_time_value($returnTime) ? time_on_date($returnTime, date('Y-m-d')) : '',
                'kondisi_alat' => trim(isset($_POST['kondisi_alat']) ? $_POST['kondisi_alat'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
                'diproses_oleh' => current_user()['id'],
                'request_user_id' => current_user()['id'],
                'status' => 'MENUNGGU',
            );
            if (!valid_time_value($returnTime)) {
                $error = 'Jam pengembalian tidak valid. Gunakan format HH:MM.';
            } elseif ($d['peminjaman_id'] < 1 || !$d['kondisi_alat']) {
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

<<<<<<< HEAD
    public function adminForm($id = null, int $selectedLoanId = 0)
=======
    public function adminForm($id = null)
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
    {
        require_role('admin');
        $item = $id ? $this->model->find($id) : null;
        if ($id && !$item) {
            http_response_code(404);
            require __DIR__ . '/../views/info/404.php';
            return;
        }
<<<<<<< HEAD
        if ($id && $item['status'] !== 'MENUNGGU') {
            flash('error', 'Pengembalian yang sudah selesai tidak dapat diubah.');
            redirect('pengembalian');
        }

        // Perbarui status terlambat sebelum menampilkan pilihan transaksi.
        $peminjamanModel = new Peminjaman($this->db);
        $peminjamanModel->syncOverdue();
        $loans = $peminjamanModel->availableForReturn($id ? (int)$id : 0);

        if ($item) {
            $selectedLoanId = (int)$item['peminjaman_id'];
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peminjaman_id'])) {
            $selectedLoanId = (int)$_POST['peminjaman_id'];
        }

=======
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
        $error = null;
        $returnTime = isset($item['tanggal_pengembalian']) ? date('H:i', strtotime($item['tanggal_pengembalian'])) : date('H:i');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $returnTime = trim(isset($_POST['tanggal_pengembalian']) ? (string)$_POST['tanggal_pengembalian'] : '');
            if (valid_time_value($returnTime)) {
                $returnTime = substr($returnTime, 0, 5);
            }
            $returnDate = isset($item['tanggal_pengembalian']) ? date('Y-m-d', strtotime($item['tanggal_pengembalian'])) : date('Y-m-d');
            $d = array(
                'peminjaman_id' => (int)(isset($_POST['peminjaman_id']) ? $_POST['peminjaman_id'] : 0),
                'tanggal_pengembalian' => valid_time_value($returnTime) ? time_on_date($returnTime, $returnDate) : '',
                'kondisi_alat' => trim(isset($_POST['kondisi_alat']) ? $_POST['kondisi_alat'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
            );
            if (!valid_time_value($returnTime)) {
                $error = 'Jam pengembalian tidak valid. Gunakan format HH:MM.';
            } elseif ($d['peminjaman_id'] < 1 || !$d['kondisi_alat']) {
                $error = 'Transaksi dan kondisi alat wajib diisi.';
            } else {
                try {
                    if ($id) {
                        $this->model->update($id, $d);
<<<<<<< HEAD
                        (new LogAktivitas($this->db))->add(current_user()['id'], 'Mengubah data pengembalian', 'Pengembalian');
                        flash('success', 'Data pengembalian berhasil diperbarui.');
                        redirect('pengembalian');
                    }

                    // Admin mencatat pengembalian sekaligus menyelesaikan transaksi.
                    // Jika pembaruan stok gagal, data tetap berstatus MENUNGGU dan
                    // dapat diproses ulang dari halaman Pengembalian.
                    $d['diproses_oleh'] = current_user()['id'];
                    $returnId = $this->model->create($d);
                    try {
                        $this->model->process($returnId, (int)current_user()['id']);
                    } catch (Throwable $processError) {
                        flash('error', 'Pengembalian sudah dicatat, tetapi belum selesai diproses: ' . $processError->getMessage() . ' Silakan proses dari daftar Pengembalian.');
                        redirect('pengembalian');
                    }

                    (new LogAktivitas($this->db))->add(current_user()['id'], 'Mencatat dan menyelesaikan pengembalian', 'Pengembalian');
                    flash('success', 'Pengembalian berhasil diselesaikan. Stok alat dan status peminjaman sudah diperbarui.');
=======
                    } else {
                        $d['diproses_oleh'] = current_user()['id'];
                        $this->model->create($d);
                    }
                    flash('success', 'Data pengembalian berhasil disimpan.');
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
                    redirect('pengembalian');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
<<<<<<< HEAD
=======
        $loans = (new Peminjaman($this->db))->all();
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
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
