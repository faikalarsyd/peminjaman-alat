CREATE DATABASE IF NOT EXISTS db_peminjaman_alat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_peminjaman_alat;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS pengembalian;
DROP TABLE IF EXISTS detail_peminjaman;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS alat;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS pengaturan;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE roles(
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_role VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id TINYINT UNSIGNED NOT NULL,
  status ENUM('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role(role_id),
  INDEX idx_users_status(status),
  CONSTRAINT fk_users_role FOREIGN KEY(role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alat(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_alat VARCHAR(50) NOT NULL UNIQUE,
  nama_alat VARCHAR(150) NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  deskripsi TEXT,
  kondisi VARCHAR(50) NOT NULL DEFAULT 'Baik',
  jumlah INT UNSIGNED NOT NULL DEFAULT 0,
  jumlah_tersedia INT UNSIGNED NOT NULL DEFAULT 0,
  lokasi VARCHAR(120) NOT NULL,
  foto VARCHAR(255),
  status ENUM('Tersedia','Tidak Tersedia','Perawatan') NOT NULL DEFAULT 'Tersedia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_alat_kategori(kategori_id),
  INDEX idx_alat_status(status),
  INDEX idx_alat_nama(nama_alat),
  CONSTRAINT fk_alat_kategori FOREIGN KEY(kategori_id) REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE peminjaman(
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_peminjaman VARCHAR(40) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_kembali_rencana DATE NOT NULL,
  tanggal_kembali_actual DATE NULL,
  keperluan TEXT NOT NULL,
  catatan TEXT,
  status ENUM('MENUNGGU','DISETUJUI','DITOLAK','DIPINJAM','DIKEMBALIKAN','TERLAMBAT') NOT NULL DEFAULT 'MENUNGGU',
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pinjam_user(user_id),
  INDEX idx_pinjam_status(status),
  INDEX idx_pinjam_tanggal(tanggal_pinjam,tanggal_kembali_rencana),
  CONSTRAINT fk_pinjam_user FOREIGN KEY(user_id) REFERENCES users(id),
  CONSTRAINT fk_pinjam_approved FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detail_peminjaman(
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id BIGINT UNSIGNED NOT NULL,
  alat_id INT UNSIGNED NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  kondisi_saat_pinjam VARCHAR(100),
  kondisi_saat_kembali VARCHAR(100),
  catatan TEXT,
  INDEX idx_detail_pinjam(peminjaman_id),
  INDEX idx_detail_alat(alat_id),
  CONSTRAINT fk_detail_pinjam FOREIGN KEY(peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
  CONSTRAINT fk_detail_alat FOREIGN KEY(alat_id) REFERENCES alat(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengembalian(
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id BIGINT UNSIGNED NOT NULL UNIQUE,
  tanggal_pengembalian DATE NOT NULL,
  terlambat_hari INT UNSIGNED NOT NULL DEFAULT 0,
  denda DECIMAL(12,2) NOT NULL DEFAULT 0,
  kondisi_alat VARCHAR(100),
  catatan TEXT,
  diproses_oleh INT UNSIGNED NULL,
  status ENUM('MENUNGGU','SELESAI') NOT NULL DEFAULT 'MENUNGGU',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_return_status(status),
  CONSTRAINT fk_return_pinjam FOREIGN KEY(peminjaman_id) REFERENCES peminjaman(id),
  CONSTRAINT fk_return_user FOREIGN KEY(diproses_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE log_aktivitas(
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  aktivitas VARCHAR(255) NOT NULL,
  modul VARCHAR(80) NOT NULL,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_log_user(user_id),
  INDEX idx_log_created(created_at),
  CONSTRAINT fk_log_user FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengaturan(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kunci VARCHAR(80) NOT NULL UNIQUE,
  nilai VARCHAR(255) NOT NULL,
  deskripsi VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles(nama_role) VALUES ('Admin'),('Petugas'),('Peminjam');
INSERT INTO pengaturan(kunci,nilai,deskripsi) VALUES ('denda_per_hari','5000','Denda keterlambatan per hari dalam rupiah');

-- Data demo.
USE db_peminjaman_alat;
INSERT INTO users(nama,username,email,password,role_id,status) VALUES
('Administrator','admin','admin@gmail.com','$2y$12$J8FFs4XEKJgoQ/gEce41gumhnRZ/OpsOcwZQ/zDl3IrRSPsaT2Mdu',1,'Aktif'),('Petugas Utama','petugas','petugas@gmail.com','$2y$12$C2PuqzctUeuj/orDtYBjDuW5McDcZswLMynrQFlvws88jWwZozeFy',2,'Aktif'),('Peminjam Satu','user','user@gmail.com','$2y$12$JCjLmQax1Uaat.lWzoZBdOgK3W1f8RWiadBLWZDSMDqSMREgD8186',3,'Aktif'),('Peminjam Dua','user2','user2@gmail.com','$2y$12$kx6Bf.mvksPy9REKSfy.EuxhB.zFvjv.jYet5a5.Q4JZYjYi/hXC6',3,'Aktif'),('Peminjam Tiga','user3','user3@gmail.com','$2y$12$tYNe5sT1kctfkInlqtgWsO9LTvlHNxfTyHW6yxPtbm9HC/9b9YBPa',3,'Aktif'),('Peminjam Empat','user4','user4@gmail.com','$2y$12$EeU7TeXRZJ7CLrAZDdsEn.5HOLHf4p3riYVPDJvRtFrfay2Xlehqm',3,'Aktif'),('Peminjam Lima','user5','user5@gmail.com','$2y$12$Vw2r9EvDQ8ILoWl3SyHyee7D6EfI3BCsTrcXj4aWLE0fHTYy6NLk6',3,'Aktif');
INSERT INTO kategori(nama_kategori,deskripsi) VALUES ('Elektronik','Perangkat elektronik'),('Peralatan Kantor','Peralatan administrasi kantor'),('Peralatan Produksi','Peralatan produksi'),('Peralatan Multimedia','Audio visual dan multimedia'),('Peralatan Lapangan','Peralatan untuk kegiatan lapangan');
INSERT INTO alat(kode_alat,nama_alat,kategori_id,deskripsi,kondisi,jumlah,jumlah_tersedia,lokasi,status) VALUES
('ALT-001','Proyektor Epson HD',1,'Proyektor untuk presentasi','Baik',3,3,'Gudang A','Tersedia'),('ALT-002','Laptop Lenovo',1,'Laptop kerja','Baik',5,4,'Gudang A','Tersedia'),('ALT-003','Kamera DSLR',4,'Kamera dokumentasi','Baik',2,1,'Studio','Tersedia'),('ALT-004','Tripod Kamera',4,'Tripod kamera','Baik',4,3,'Studio','Tersedia'),('ALT-005','Speaker Portable',4,'Speaker portable','Baik',3,3,'Studio','Tersedia'),('ALT-006','Microphone Wireless',4,'Mic wireless','Baik',5,5,'Studio','Tersedia'),('ALT-007','Kabel HDMI',1,'Kabel HDMI 5 meter','Baik',10,10,'Gudang A','Tersedia'),('ALT-008','Printer',2,'Printer dokumen','Baik',2,2,'Kantor','Tersedia'),('ALT-009','Scanner',2,'Scanner dokumen','Baik',2,2,'Kantor','Tersedia'),('ALT-010','Monitor 24 Inch',1,'Monitor kerja','Baik',4,4,'Gudang A','Tersedia'),('ALT-011','Keyboard',1,'Keyboard USB','Baik',10,10,'Gudang A','Tersedia'),('ALT-012','Mouse',1,'Mouse USB','Baik',10,10,'Gudang A','Tersedia'),('ALT-013','Whiteboard',2,'Papan tulis','Baik',3,3,'Kantor','Tersedia'),('ALT-014','Megaphone',5,'Megaphone lapangan','Baik',2,2,'Gudang B','Tersedia'),('ALT-015','Tenda',5,'Tenda kegiatan','Baik',4,4,'Gudang B','Tersedia'),('ALT-016','Kursi Lipat',2,'Kursi kegiatan','Baik',20,20,'Gudang B','Tersedia'),('ALT-017','Meja Lipat',2,'Meja kegiatan','Baik',10,10,'Gudang B','Tersedia'),('ALT-018','Extension Cable',1,'Kabel ekstensi','Baik',8,8,'Gudang A','Tersedia'),('ALT-019','Capture Card',4,'Capture card video','Baik',2,2,'Studio','Tersedia'),('ALT-020','Ring Light',4,'Lampu dokumentasi','Baik',3,3,'Studio','Tersedia');
INSERT INTO peminjaman(kode_peminjaman,user_id,tanggal_pinjam,tanggal_kembali_rencana,tanggal_kembali_actual,keperluan,catatan,status,approved_by,approved_at) VALUES
('DEMO-001',3,'2026-09-01','2026-09-20',NULL,'Presentasi sekolah','Data demo','DIPINJAM',2,'2026-09-01 09:00:00'),
('DEMO-002',4,'2026-09-02','2026-09-18',NULL,'Dokumentasi kegiatan','Data demo','TERLAMBAT',2,'2026-09-02 09:00:00'),
('DEMO-003',5,'2026-09-03','2026-09-25',NULL,'Produksi video','Data demo','DISETUJUI',2,'2026-09-03 09:00:00'),
('DEMO-004',6,'2026-09-04','2026-09-08','2026-09-08','Rapat','Data demo','DIKEMBALIKAN',2,'2026-09-04 09:00:00'),
('DEMO-005',7,'2026-09-05','2026-09-10',NULL,'Kegiatan lapangan','Data demo','DITOLAK',2,'2026-09-05 09:00:00'),
('DEMO-006',3,'2026-09-06','2026-09-15',NULL,'Peminjaman demo','Data demo','MENUNGGU',NULL,NULL),
('DEMO-007',4,'2026-09-07','2026-09-16',NULL,'Peminjaman demo','Data demo','MENUNGGU',NULL,NULL),
('DEMO-008',5,'2026-09-08','2026-09-17',NULL,'Peminjaman demo','Data demo','MENUNGGU',NULL,NULL),
('DEMO-009',6,'2026-09-09','2026-09-19',NULL,'Peminjaman demo','Data demo','MENUNGGU',NULL,NULL),
('DEMO-010',7,'2026-09-10','2026-09-22',NULL,'Peminjaman demo','Data demo','MENUNGGU',NULL,NULL);
INSERT INTO detail_peminjaman(peminjaman_id,alat_id,jumlah,kondisi_saat_pinjam,kondisi_saat_kembali,catatan) VALUES
(1,2,1,'Baik',NULL,'Demo aktif'),(2,3,1,'Baik',NULL,'Demo terlambat'),(3,4,1,'Baik',NULL,'Demo disetujui'),(4,5,1,'Baik','Baik','Demo selesai'),(5,6,1,'Baik',NULL,'Demo ditolak'),(6,7,1,'Baik',NULL,'Demo menunggu'),(7,8,1,'Baik',NULL,'Demo menunggu'),(8,9,1,'Baik',NULL,'Demo menunggu'),(9,10,1,'Baik',NULL,'Demo menunggu'),(10,11,1,'Baik',NULL,'Demo menunggu');
INSERT INTO pengembalian(peminjaman_id,tanggal_pengembalian,terlambat_hari,denda,kondisi_alat,catatan,diproses_oleh,status) VALUES(4,'2026-09-08',0,0,'Baik','Dikembalikan sesuai jadwal',2,'SELESAI');
