<?php
class DetailPeminjaman { private $db; public function __construct(PDO $db) { $this->db = $db; } public function byLoan(int$id):array{$s=$this->db->prepare('SELECT d.*,a.nama_alat,a.kode_alat FROM detail_peminjaman d JOIN alat a ON a.id=d.alat_id WHERE d.peminjaman_id=?');$s->execute([$id]);return$s->fetchAll();} }
