<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/Peminjaman.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Alat.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class PeminjamanController
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->model = new Peminjaman($this->db);
    }

    public function index()
    {
        require_role(array('admin', 'petugas', 'peminjam'));
        $this->model->syncOverdue();
        $uid = has_role('peminjam') ? current_user()['id'] : null;
        $peminjaman = $this->model->all($uid);
        $pending = has_role(array('admin', 'petugas')) ? $this->model->pending() : array();
        require __DIR__ . '/../views/peminjaman/index.php';
    }

    public function create()
    {
        if (has_role('admin')) {
            $this->adminForm();
            return;
        }

        require_role('peminjam');
        $alat = (new Alat($this->db))->all(100, 0, trim(isset($_GET['q']) ? $_GET['q'] : ''));
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'user_id' => current_user()['id'],
                'tanggal_pinjam' => isset($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d'),
                'tanggal_kembali_rencana' => isset($_POST['tanggal_kembali_rencana']) ? $_POST['tanggal_kembali_rencana'] : '',
                'keperluan' => trim(isset($_POST['keperluan']) ? $_POST['keperluan'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
            );
            $detail = array(
                'alat_id' => (int)(isset($_POST['alat_id']) ? $_POST['alat_id'] : 0),
                'jumlah' => (int)(isset($_POST['jumlah']) ? $_POST['jumlah'] : 0),
            );
            $errors = validate_required($d, array(
                'tanggal_kembali_rencana' => 'Tanggal rencana kembali',
                'keperluan' => 'Keperluan',
            ));
            if ($detail['alat_id'] < 1 || $detail['jumlah'] < 1) {
                $errors['alat'] = 'Alat dan jumlah wajib valid.';
            }
            if ($d['tanggal_kembali_rencana'] !== '' && $d['tanggal_kembali_rencana'] < $d['tanggal_pinjam']) {
                $errors['tanggal'] = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
            }

            if (!$errors) {
                try {
                    $this->model->create($d, $detail);
                    (new LogAktivitas($this->db))->add(current_user()['id'], 'Mengajukan peminjaman', 'Peminjaman');
                    flash('success', 'Pengajuan peminjaman berhasil dibuat dan menunggu persetujuan.');
                    redirect('peminjaman');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = implode(' ', $errors);
            }
        }

        require __DIR__ . '/../views/peminjaman/create.php';
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

        $db = $this->db;
        $users = (new User($db))->all(200);
        $alat = (new Alat($db))->all(200);
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $d = array(
                'kode_peminjaman' => trim(isset($_POST['kode_peminjaman']) ? $_POST['kode_peminjaman'] : ''),
                'user_id' => (int)(isset($_POST['user_id']) ? $_POST['user_id'] : 0),
                'tanggal_pinjam' => isset($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d'),
                'tanggal_kembali_rencana' => isset($_POST['tanggal_kembali_rencana']) ? $_POST['tanggal_kembali_rencana'] : '',
                'keperluan' => trim(isset($_POST['keperluan']) ? $_POST['keperluan'] : ''),
                'catatan' => trim(isset($_POST['catatan']) ? $_POST['catatan'] : ''),
                'status' => isset($_POST['status']) ? $_POST['status'] : 'MENUNGGU',
            );
            $detail = array(
                'alat_id' => (int)(isset($_POST['alat_id']) ? $_POST['alat_id'] : 0),
                'jumlah' => (int)(isset($_POST['jumlah']) ? $_POST['jumlah'] : 0),
            );
            $errors = validate_required($d, array(
                'kode_peminjaman' => 'Kode peminjaman',
                'tanggal_kembali_rencana' => 'Tanggal rencana kembali',
                'keperluan' => 'Keperluan',
            ));
            if ($d['user_id'] < 1 || (!$id && $detail['alat_id'] < 1) || (!$id && $detail['jumlah'] < 1)) {
                $errors['data'] = 'Peminjam dan detail alat wajib valid.';
            }
            if ($d['tanggal_kembali_rencana'] !== '' && $d['tanggal_kembali_rencana'] < $d['tanggal_pinjam']) {
                $errors['tanggal'] = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
            }

            if (!$errors) {
                try {
                    if ($id) {
                        $this->model->adminUpdate($id, $d);
                    } else {
                        $this->model->adminCreate($d, $detail);
                    }
                    (new LogAktivitas($this->db))->add(current_user()['id'], ($item ? 'Mengubah' : 'Menambahkan') . ' data peminjaman', 'Peminjaman');
                    flash('success', 'Data peminjaman berhasil disimpan.');
                    redirect('peminjaman');
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = implode(' ', $errors);
            }
        }

        require __DIR__ . '/../views/peminjaman/admin_form.php';
    }

    public function delete(int $id)
    {
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('peminjaman');
        verify_csrf();
        try {
            $this->model->delete($id);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menghapus data peminjaman', 'Peminjaman');
            flash('success', 'Data peminjaman dihapus.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('peminjaman');
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
        if (has_role('peminjam') && (int)$item['user_id'] !== (int)current_user()['id']) {
            require_role('admin');
        }
        require __DIR__ . '/../views/peminjaman/detail.php';
    }

    public function approve(int $id)
    {
        require_role(array('admin', 'petugas'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('peminjaman');
        verify_csrf();
        try {
            $this->model->approve($id, current_user()['id']);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menyetujui peminjaman', 'Peminjaman');
            flash('success', 'Peminjaman disetujui dan stok diperbarui.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('peminjaman');
    }

    public function reject(int $id)
    {
        require_role(array('admin', 'petugas'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('peminjaman');
        verify_csrf();
        try {
            $this->model->reject($id, current_user()['id']);
            (new LogAktivitas($this->db))->add(current_user()['id'], 'Menolak peminjaman', 'Peminjaman');
            flash('success', 'Peminjaman ditolak.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('peminjaman');
    }
}
