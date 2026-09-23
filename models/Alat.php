<?php
class Alat
{
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function all(int $limit = 50, int $offset = 0, string $q = ''): array
    {
        $sql = 'SELECT a.*,k.nama_kategori FROM alat a JOIN kategori k ON k.id=a.kategori_id WHERE a.nama_alat LIKE ? OR a.kode_alat LIKE ? ORDER BY a.id DESC LIMIT ? OFFSET ?';
        $s = $this->db->prepare($sql);
        $like = '%' . $q . '%';
        $s->bindValue(1, $like);
        $s->bindValue(2, $like);
        $s->bindValue(3, $limit, PDO::PARAM_INT);
        $s->bindValue(4, $offset, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function find(int $id)
    {
        $s = $this->db->prepare('SELECT a.*,k.nama_kategori FROM alat a JOIN kategori k ON k.id=a.kategori_id WHERE a.id=?');
        $s->execute(array($id));
        return $s->fetch() ?: null;
    }

    public function create(array $d): int
    {
        $s = $this->db->prepare('INSERT INTO alat(kode_alat,nama_alat,kategori_id,deskripsi,kondisi,jumlah,jumlah_tersedia,lokasi,foto,status) VALUES(?,?,?,?,?,?,?,?,?,?)');
        $s->execute(array($d['kode_alat'],$d['nama_alat'],$d['kategori_id'],$d['deskripsi'],$d['kondisi'],$d['jumlah'],$d['jumlah_tersedia'],$d['lokasi'],$d['foto'],$d['status']));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d)
    {
        $s = $this->db->prepare('UPDATE alat SET kode_alat=?,nama_alat=?,kategori_id=?,deskripsi=?,kondisi=?,jumlah=?,jumlah_tersedia=?,lokasi=?,foto=?,status=? WHERE id=?');
        $s->execute(array($d['kode_alat'],$d['nama_alat'],$d['kategori_id'],$d['deskripsi'],$d['kondisi'],$d['jumlah'],$d['jumlah_tersedia'],$d['lokasi'],$d['foto'],$d['status'],$id));
    }

    public function delete(int $id)
    {
        try {
            $s = $this->db->prepare('DELETE FROM alat WHERE id=?');
            $s->execute(array($id));
        } catch (PDOException $e) {
            throw new RuntimeException('Alat tidak dapat dihapus karena masih dipakai pada transaksi.');
        }
    }

    public function count(): int { return (int)$this->db->query('SELECT COUNT(*) FROM alat')->fetchColumn(); }
    public function available(): int { return (int)$this->db->query('SELECT COALESCE(SUM(jumlah_tersedia),0) FROM alat')->fetchColumn(); }
}
