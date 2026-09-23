# NambutApp — Aplikasi Peminjaman Alat

Aplikasi peminjaman alat berbasis PHP Native + PDO + MySQL/MariaDB, dirancang agar satu folder yang sama dapat dijalankan di **XAMPP laptop maupun AWeb Server Android**.

## Yang sudah diperbaiki

- URL aplikasi memakai `index.php?route=...`, jadi **tidak bergantung pada mod_rewrite**.
- `database/database.sql` sekarang menjadi **installer tunggal**: database + tabel + data demo.
- Runtime transaksi memakai **PDO murni** dan transaksi database (`BEGIN/COMMIT/ROLLBACK`), sehingga aplikasi tidak wajib bergantung pada stored procedure/trigger.
- Alur approval mengurangi stok secara atomik.
- Alur pengembalian mengembalikan stok secara atomik dan menghitung denda.
- Error database dan error aplikasi tidak menampilkan stack trace/raw exception kepada pengguna.
- Upload foto divalidasi dan folder upload diblokir dari eksekusi PHP.
- Password tidak disimpan ke session.
- Halaman 404 dan 500 disediakan.

## Requirement

- PHP 7.0 atau lebih baru.
- Extension PHP: **PDO** dan **PDO_MySQL** untuk koneksi ke MySQL/MariaDB.
- MySQL 5.7+/MariaDB yang mendukung InnoDB dan utf8mb4.
- Apache atau web server lain. `.htaccess` hanya bonus; aplikasi tetap dapat berjalan tanpa rewrite.

## Instalasi XAMPP

1. Ekstrak folder `peminjaman-alat` ke `C:\xampp\htdocs\`.
2. Jalankan **Apache** dan **MySQL**.
3. Buka phpMyAdmin.
4. Import **`database/database.sql` satu kali**.
5. Buka `http://localhost/peminjaman-alat/`.

Konfigurasi default:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `db_peminjaman_alat`
- User: `root`
- Password: kosong

Bila MySQL memakai password atau port berbeda, buat file `config/database.local.php` berdasarkan `config/database.local.example.php`.

## Instalasi AWeb Server

1. Ekstrak seluruh folder `peminjaman-alat` ke document root AWeb Server.
2. Pastikan PHP dan MySQL/MariaDB AWeb aktif.
3. Buat/import database **`database/database.sql`** melalui phpMyAdmin/AWeb database manager.
4. Bila credential database berbeda, buat `config/database.local.php`.
5. Buka alamat folder project pada browser.

Aplikasi menggunakan URL berbentuk `index.php?route=...`, jadi **tidak perlu mengandalkan fitur rewrite** untuk navigasi utama.

## Akun demo

- Admin — `admin@gmail.com` / `admin123`
- Petugas — `petugas@gmail.com` / `petugas123`
- Peminjam — `user@gmail.com` / `user123`
- Peminjam tambahan — `user2@gmail.com` / `user234`, `user3@gmail.com` / `user345`, dst.

## Role

**Admin:** kelola pengguna, kategori, alat, peminjaman, pengembalian, log, laporan, pengaturan.

**Petugas:** melihat alat, menyetujui/menolak peminjaman, memproses pengembalian, melihat laporan.

**Peminjam:** melihat alat, membuat peminjaman, melihat riwayat, mengajukan pengembalian.

## Database

### Import untuk menjalankan aplikasi

**Cukup import:** `database/database.sql`

File ini sudah membuat ulang database dari awal dan memasukkan data demo.

### File SQL akademik

- `database/function.sql` — function denda versi lama/terpisah.
- `database/stored_procedure.sql` — stored procedure untuk dokumentasi tugas.
- `database/trigger.sql` — trigger guard/stok versi dokumentasi tugas.
- `database/advanced_features.sql` — kumpulan function/procedure/trigger yang dapat diimport setelah database utama bila diperlukan untuk demonstrasi tugas.
- `database/seed.sql` — hanya data demo.
- `database/all_in_one.sql` — installer inti + data demo tanpa ketergantungan runtime terhadap routine SQL.

**Runtime PHP tidak memanggil `CALL ...` lagi.** Ini sengaja dilakukan agar aplikasi tetap berjalan walaupun konfigurasi AWeb/XAMPP tidak mengaktifkan stored routine tertentu.

## Struktur

`assets/`, `controllers/`, `models/`, `views/`, `config/`, `helpers/`, `uploads/`, `database/`, `docs/`, `index.php`, `router.php`, `.htaccess`.

## Troubleshooting

### “Not Found” saat membuka menu

Gunakan URL utama folder project. Navigasi internal sudah menggunakan `index.php?route=...` dan tidak membutuhkan rewrite.

### “Koneksi database gagal”

Pastikan MySQL/MariaDB hidup dan cek `config/database.local.php` bila credential/port tidak default.

### Halaman tampil tetapi CSS tidak ada

Pastikan folder `assets` ikut diekstrak dan project dibuka melalui URL web server, bukan file lokal `file://`.

### Upload foto gagal

Pastikan folder `uploads/alat` dapat ditulisi oleh web server. Ukuran maksimal foto 2 MB.

## Pengujian minimum

1. Login Admin.
2. Tambah/ubah kategori.
3. Tambah/ubah alat.
4. Register/login Peminjam.
5. Peminjam ajukan peminjaman.
6. Admin/Petugas menyetujui; stok berkurang.
7. Peminjam ajukan pengembalian.
8. Admin/Petugas memproses; stok kembali dan transaksi berubah menjadi `DIKEMBALIKAN`.
9. Cek laporan dan log.
10. Uji ulang project pada XAMPP dan AWeb dengan `database/database.sql` yang sama.
