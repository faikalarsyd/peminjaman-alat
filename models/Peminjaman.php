<?php
class Peminjaman
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all($userId = null, int $limit = 50, int $offset = 0): array
    {
        $where = $userId !== null ? 'WHERE p.user_id=?' : '';
        $sql = "SELECT p.*, u.nama AS nama_peminjam,
                       (SELECT COALESCE(SUM(d.jumlah),0) FROM detail_peminjaman d WHERE d.peminjaman_id=p.id) AS jumlah_item
                FROM peminjaman p
                JOIN users u ON u.id=p.user_id
                {$where}
                ORDER BY p.id DESC LIMIT ? OFFSET ?";
        $s = $this->db->prepare($sql);
        $i = 1;
        if ($userId !== null) {
            $s->bindValue($i++, (int)$userId, PDO::PARAM_INT);
        }
        $s->bindValue($i++, $limit, PDO::PARAM_INT);
        $s->bindValue($i, $offset, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function find(int $id)
    {
        $s = $this->db->prepare('SELECT p.*,u.nama nama_peminjam,u.email FROM peminjaman p JOIN users u ON u.id=p.user_id WHERE p.id=?');
        $s->execute(array($id));
        $p = $s->fetch();
        if (!$p) {
            return null;
        }

        $s = $this->db->prepare('SELECT d.*,a.nama_alat,a.kode_alat FROM detail_peminjaman d JOIN alat a ON a.id=d.alat_id WHERE d.peminjaman_id=?');
        $s->execute(array($id));
        $p['details'] = $s->fetchAll();
        return $p;
    }

    public function pending(): array
    {
        $s = $this->db->query("SELECT p.id,p.kode_peminjaman,p.tanggal_pinjam,p.tanggal_kembali_rencana,p.status,u.nama nama_peminjam
                              FROM peminjaman p JOIN users u ON u.id=p.user_id
                              WHERE p.status='MENUNGGU' ORDER BY p.id DESC LIMIT 50");
        return $s->fetchAll();
    }

    public function activeCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM peminjaman WHERE status IN ('DISETUJUI','DIPINJAM','TERLAMBAT')")->fetchColumn();
    }

    public function waitingCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM peminjaman WHERE status='MENUNGGU'")->fetchColumn();
    }

    private function generateCode(): string
    {
        do {
            $code = 'PJM-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $s = $this->db->prepare('SELECT id FROM peminjaman WHERE kode_peminjaman=? LIMIT 1');
            $s->execute(array($code));
        } while ($s->fetchColumn());

        return $code;
    }

    private function updateToolStock(int $toolId, int $delta): void
    {
        $s = $this->db->prepare('SELECT id,jumlah,jumlah_tersedia,status FROM alat WHERE id=? FOR UPDATE');
        $s->execute(array($toolId));
        $tool = $s->fetch();
        if (!$tool) {
            throw new RuntimeException('Alat tidak ditemukan.');
        }

        $available = (int)$tool['jumlah_tersedia'] + $delta;
        if ($available < 0) {
            throw new RuntimeException('Stok alat tidak mencukupi.');
        }
        if ($available > (int)$tool['jumlah']) {
            $available = (int)$tool['jumlah'];
        }

        $status = $available > 0 ? 'Tersedia' : 'Tidak Tersedia';
        if ($tool['status'] === 'Perawatan' && $delta < 0) {
            throw new RuntimeException('Alat sedang dalam perawatan.');
        }

        $u = $this->db->prepare('UPDATE alat SET jumlah_tersedia=?,status=? WHERE id=?');
        $u->execute(array($available, $status, $toolId));
    }

    public function create(array $d, array $detail): int
    {
        if ($detail['jumlah'] < 1) {
            throw new RuntimeException('Jumlah alat harus lebih dari 0.');
        }
        if ($d['tanggal_kembali_rencana'] < $d['tanggal_pinjam']) {
            throw new RuntimeException('Tanggal kembali tidak boleh sebelum tanggal pinjam.');
        }

        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT id,kondisi,jumlah_tersedia,status FROM alat WHERE id=? FOR UPDATE");
            $s->execute(array($detail['alat_id']));
            $tool = $s->fetch();
            if (!$tool) {
                throw new RuntimeException('Alat tidak ditemukan.');
            }
            if ($tool['status'] === 'Perawatan') {
                throw new RuntimeException('Alat sedang dalam perawatan.');
            }
            if ((int)$tool['jumlah_tersedia'] < (int)$detail['jumlah']) {
                throw new RuntimeException('Stok alat tidak mencukupi.');
            }

            $s = $this->db->prepare('INSERT INTO peminjaman(kode_peminjaman,user_id,tanggal_pinjam,tanggal_kembali_rencana,keperluan,catatan,status) VALUES(?,?,?,?,?,?,?)');
            $s->execute(array(
                $this->generateCode(),
                (int)$d['user_id'],
                $d['tanggal_pinjam'],
                $d['tanggal_kembali_rencana'],
                $d['keperluan'],
                $d['catatan'],
                'MENUNGGU'
            ));
            $id = (int)$this->db->lastInsertId();

            $s = $this->db->prepare('INSERT INTO detail_peminjaman(peminjaman_id,alat_id,jumlah,kondisi_saat_pinjam) VALUES(?,?,?,?)');
            $s->execute(array($id, (int)$detail['alat_id'], (int)$detail['jumlah'], $tool['kondisi']));

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function approve(int $id, int $userId)
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT id,status FROM peminjaman WHERE id=? FOR UPDATE");
            $s->execute(array($id));
            $loan = $s->fetch();
            if (!$loan) {
                throw new RuntimeException('Peminjaman tidak ditemukan.');
            }
            if ($loan['status'] !== 'MENUNGGU') {
                throw new RuntimeException('Peminjaman tidak berstatus MENUNGGU.');
            }

            $s = $this->db->prepare('SELECT alat_id,jumlah FROM detail_peminjaman WHERE peminjaman_id=?');
            $s->execute(array($id));
            $details = $s->fetchAll();
            if (!$details) {
                throw new RuntimeException('Detail alat pada peminjaman tidak ditemukan.');
            }

            foreach ($details as $detail) {
                $this->updateToolStock((int)$detail['alat_id'], -(int)$detail['jumlah']);
            }

            $s = $this->db->prepare("UPDATE peminjaman SET status='DISETUJUI',approved_by=?,approved_at=NOW(),updated_at=NOW() WHERE id=?");
            $s->execute(array($userId, $id));
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function reject(int $id, int $userId)
    {
        $s = $this->db->prepare("UPDATE peminjaman SET status='DITOLAK',approved_by=?,approved_at=NOW(),updated_at=NOW() WHERE id=? AND status='MENUNGGU'");
        $s->execute(array($userId, $id));
        if ($s->rowCount() < 1) {
            throw new RuntimeException('Peminjaman tidak dapat ditolak.');
        }
    }

    public function syncOverdue()
    {
        $this->db->exec("UPDATE peminjaman SET status='TERLAMBAT' WHERE status IN ('DISETUJUI','DIPINJAM') AND tanggal_kembali_rencana<CURDATE()");
    }

    public function overdue(): int
    {
        $this->syncOverdue();
        return (int)$this->db->query("SELECT COUNT(*) FROM peminjaman WHERE status='TERLAMBAT'")->fetchColumn();
    }

    public function adminCreate(array $d, array $detail): int
    {
        $status = isset($d['status']) ? $d['status'] : 'MENUNGGU';
        if (!in_array($status, array('MENUNGGU', 'DISETUJUI', 'DITOLAK'), true)) {
            $status = 'MENUNGGU';
        }
        if ($d['tanggal_kembali_rencana'] < $d['tanggal_pinjam']) {
            throw new RuntimeException('Tanggal kembali tidak boleh sebelum tanggal pinjam.');
        }

        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('SELECT id,kondisi FROM alat WHERE id=? FOR UPDATE');
            $s->execute(array($detail['alat_id']));
            $tool = $s->fetch();
            if (!$tool) {
                throw new RuntimeException('Alat tidak ditemukan.');
            }

            $s = $this->db->prepare('INSERT INTO peminjaman(kode_peminjaman,user_id,tanggal_pinjam,tanggal_kembali_rencana,keperluan,catatan,status) VALUES(?,?,?,?,?,?,?)');
            $s->execute(array($d['kode_peminjaman'] ?: $this->generateCode(), $d['user_id'], $d['tanggal_pinjam'], $d['tanggal_kembali_rencana'], $d['keperluan'], $d['catatan'], $status));
            $id = (int)$this->db->lastInsertId();

            $s = $this->db->prepare('INSERT INTO detail_peminjaman(peminjaman_id,alat_id,jumlah,kondisi_saat_pinjam) VALUES(?,?,?,?)');
            $s->execute(array($id, $detail['alat_id'], $detail['jumlah'], $tool['kondisi']));

            if ($status === 'DISETUJUI') {
                $this->updateToolStock((int)$detail['alat_id'], -(int)$detail['jumlah']);
                $s = $this->db->prepare('UPDATE peminjaman SET approved_at=NOW() WHERE id=?');
                $s->execute(array($id));
            }

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function adminUpdate(int $id, array $d)
    {
        $allowed = array('MENUNGGU', 'DISETUJUI', 'DITOLAK');
        $newStatus = in_array($d['status'], $allowed, true) ? $d['status'] : 'MENUNGGU';
        if ($d['tanggal_kembali_rencana'] < $d['tanggal_pinjam']) {
            throw new RuntimeException('Tanggal kembali tidak boleh sebelum tanggal pinjam.');
        }

        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('SELECT * FROM peminjaman WHERE id=? FOR UPDATE');
            $s->execute(array($id));
            $loan = $s->fetch();
            if (!$loan) {
                throw new RuntimeException('Peminjaman tidak ditemukan.');
            }
            if (in_array($loan['status'], array('DIKEMBALIKAN','DIPINJAM','TERLAMBAT'), true)) {
                // Status operasional tidak diubah lewat form admin biasa.
                $newStatus = $loan['status'];
            }

            $s = $this->db->prepare('SELECT alat_id,jumlah FROM detail_peminjaman WHERE peminjaman_id=?');
            $s->execute(array($id));
            $details = $s->fetchAll();
            if (!$details) {
                throw new RuntimeException('Detail alat tidak ditemukan.');
            }

            $oldReserved = in_array($loan['status'], array('DISETUJUI','DIPINJAM','TERLAMBAT'), true);
            $newReserved = in_array($newStatus, array('DISETUJUI','DIPINJAM','TERLAMBAT'), true);

            if (!$oldReserved && $newReserved) {
                foreach ($details as $detail) {
                    $this->updateToolStock((int)$detail['alat_id'], -(int)$detail['jumlah']);
                }
            } elseif ($oldReserved && !$newReserved) {
                foreach ($details as $detail) {
                    $this->updateToolStock((int)$detail['alat_id'], (int)$detail['jumlah']);
                }
            }

            $s = $this->db->prepare('UPDATE peminjaman SET tanggal_pinjam=?,tanggal_kembali_rencana=?,keperluan=?,catatan=?,status=?,updated_at=NOW() WHERE id=?');
            $s->execute(array($d['tanggal_pinjam'], $d['tanggal_kembali_rencana'], $d['keperluan'], $d['catatan'], $newStatus, $id));
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $id)
    {
        $s = $this->db->prepare("DELETE FROM peminjaman WHERE id=? AND status IN ('MENUNGGU','DITOLAK')");
        $s->execute(array($id));
        if ($s->rowCount() < 1) {
            throw new RuntimeException('Data peminjaman aktif tidak dapat dihapus.');
        }
    }
}
