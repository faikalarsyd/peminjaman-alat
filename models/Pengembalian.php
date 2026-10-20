<?php
class Pengembalian
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all($userId = null): array
    {
        $where = $userId !== null ? 'WHERE p.user_id=?' : '';
        $sql = "SELECT g.*,p.kode_peminjaman,p.tanggal_kembali_rencana,u.nama nama_peminjam
                FROM pengembalian g
                JOIN peminjaman p ON p.id=g.peminjaman_id
                JOIN users u ON u.id=p.user_id
                {$where}
                ORDER BY g.id DESC LIMIT 100";
        $s = $this->db->prepare($sql);
        if ($userId !== null) {
            $s->execute(array((int)$userId));
        } else {
            $s->execute();
        }
        return $s->fetchAll();
    }

    public function find(int $id)
    {
        $s = $this->db->prepare('SELECT g.*,p.kode_peminjaman,p.tanggal_kembali_rencana,p.user_id,u.nama nama_peminjam FROM pengembalian g JOIN peminjaman p ON p.id=g.peminjaman_id JOIN users u ON u.id=p.user_id WHERE g.id=?');
        $s->execute(array($id));
        return $s->fetch() ?: null;
    }

    private function calculateFine(string $planned, string $actual): array
    {
        $s = $this->db->prepare("SELECT CAST(nilai AS DECIMAL(12,2)) FROM pengaturan WHERE kunci='denda_per_jam' LIMIT 1");
        $s->execute();
        $tarif = $s->fetchColumn();
        $tarif = $tarif === false ? 208.33 : (float)$tarif;

        $plannedTime = strtotime($planned);
        $actualTime = strtotime($actual);
        $hours = 0;
        if ($plannedTime !== false && $actualTime !== false && $actualTime > $plannedTime) {
            // Any started hour beyond the deadline is charged as one full hour.
            $hours = (int)ceil(($actualTime - $plannedTime) / 3600);
        }

        return array($hours, round($hours * $tarif, 2));
    }

    public function create(array $d): int
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT p.id,p.user_id,p.status,p.tanggal_kembali_rencana FROM peminjaman p WHERE p.id=? FOR UPDATE");
            $s->execute(array($d['peminjaman_id']));
            $loan = $s->fetch();
            if (!$loan || !in_array($loan['status'], array('DISETUJUI','DIPINJAM','TERLAMBAT'), true)) {
                throw new RuntimeException('Transaksi tidak dapat dikembalikan.');
            }
            if (isset($d['request_user_id']) && (int)$loan['user_id'] !== (int)$d['request_user_id']) {
                throw new RuntimeException('Anda tidak memiliki akses ke transaksi tersebut.');
            }

            $s = $this->db->prepare('SELECT id FROM pengembalian WHERE peminjaman_id=? LIMIT 1');
            $s->execute(array($d['peminjaman_id']));
            if ($s->fetchColumn()) {
                throw new RuntimeException('Pengajuan pengembalian untuk transaksi ini sudah ada.');
            }

            list($hours, $fine) = $this->calculateFine($loan['tanggal_kembali_rencana'], $d['tanggal_pengembalian']);
            $s = $this->db->prepare('INSERT INTO pengembalian(peminjaman_id,tanggal_pengembalian,terlambat_jam,denda,kondisi_alat,catatan,diproses_oleh,status) VALUES(?,?,?,?,?,?,?,?)');
            $s->execute(array(
                $d['peminjaman_id'],
                $d['tanggal_pengembalian'],
                $hours,
                $fine,
                $d['kondisi_alat'],
                $d['catatan'],
                $d['diproses_oleh'],
                'MENUNGGU'
            ));
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function process(int $id, int $userId)
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('SELECT * FROM pengembalian WHERE id=? FOR UPDATE');
            $s->execute(array($id));
            $return = $s->fetch();
            if (!$return) {
                throw new RuntimeException('Data pengembalian tidak ditemukan.');
            }
            if ($return['status'] !== 'MENUNGGU') {
                throw new RuntimeException('Pengembalian sudah diproses.');
            }

            $s = $this->db->prepare('SELECT p.id,p.status FROM peminjaman p WHERE p.id=? FOR UPDATE');
            $s->execute(array($return['peminjaman_id']));
            $loan = $s->fetch();
            if (!$loan || !in_array($loan['status'], array('DISETUJUI','DIPINJAM','TERLAMBAT'), true)) {
                throw new RuntimeException('Transaksi peminjaman tidak aktif.');
            }

            $s = $this->db->prepare('SELECT alat_id,jumlah FROM detail_peminjaman WHERE peminjaman_id=?');
            $s->execute(array($return['peminjaman_id']));
            $details = $s->fetchAll();
            if (!$details) {
                throw new RuntimeException('Detail alat tidak ditemukan.');
            }

            foreach ($details as $detail) {
                $toolId = (int)$detail['alat_id'];
                $s = $this->db->prepare('SELECT id,jumlah,jumlah_tersedia FROM alat WHERE id=? FOR UPDATE');
                $s->execute(array($toolId));
                $tool = $s->fetch();
                if (!$tool) {
                    throw new RuntimeException('Alat yang dipinjam tidak ditemukan.');
                }
                $available = min((int)$tool['jumlah'], (int)$tool['jumlah_tersedia'] + (int)$detail['jumlah']);
                $status = $available > 0 ? 'Tersedia' : 'Tidak Tersedia';
                $u = $this->db->prepare('UPDATE alat SET jumlah_tersedia=?,status=? WHERE id=?');
                $u->execute(array($available, $status, $toolId));

                $u = $this->db->prepare('UPDATE detail_peminjaman SET kondisi_saat_kembali=? WHERE peminjaman_id=? AND alat_id=?');
                $u->execute(array($return['kondisi_alat'], $return['peminjaman_id'], $toolId));
            }

            $s = $this->db->prepare("UPDATE pengembalian SET status='SELESAI',diproses_oleh=? WHERE id=?");
            $s->execute(array($userId, $id));

            $s = $this->db->prepare("UPDATE peminjaman SET status='DIKEMBALIKAN',tanggal_kembali_actual=?,updated_at=NOW() WHERE id=?");
            $s->execute(array($return['tanggal_pengembalian'], $return['peminjaman_id']));

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $id, array $d)
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('SELECT g.*,p.tanggal_kembali_rencana FROM pengembalian g JOIN peminjaman p ON p.id=g.peminjaman_id WHERE g.id=? FOR UPDATE');
            $s->execute(array($id));
            $return = $s->fetch();
            if (!$return || $return['status'] !== 'MENUNGGU') {
                throw new RuntimeException('Hanya pengembalian yang masih menunggu yang dapat diedit.');
            }

            list($hours, $fine) = $this->calculateFine($return['tanggal_kembali_rencana'], $d['tanggal_pengembalian']);
            $s = $this->db->prepare('UPDATE pengembalian SET tanggal_pengembalian=?,terlambat_jam=?,denda=?,kondisi_alat=?,catatan=? WHERE id=? AND status="MENUNGGU"');
            $s->execute(array($d['tanggal_pengembalian'], $hours, $fine, $d['kondisi_alat'], $d['catatan'], $id));
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
        $s = $this->db->prepare('DELETE FROM pengembalian WHERE id=? AND status="MENUNGGU"');
        $s->execute(array($id));
        if ($s->rowCount() < 1) {
            throw new RuntimeException('Data pengembalian tidak dapat dihapus.');
        }
    }
}
