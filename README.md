# NambutApp — Aplikasi Peminjaman Alat

Aplikasi peminjaman alat berbasis PHP Native, PDO, MySQL/MariaDB, HTML, CSS, dan JavaScript. Paket yang sama dapat dijalankan melalui XAMPP di komputer maupun AWeb Server di Android.

## Perbaikan kompatibilitas

- Memisahkan konfigurasi aplikasi (`config/config.php`) dari koneksi database (`config/database.php`), sehingga kelas `Database` tidak dideklarasikan dua kali.
- Menggunakan satu installer database, yaitu `peminjaman.sql`, berisi database, seluruh tabel, relasi, pengaturan, dan data contoh.
- Navigasi menggunakan `index.php?route=...`; URL tidak membutuhkan `mod_rewrite` atau `.htaccess`.
- Koneksi database memakai PDO MySQL, prepared statements, dan transaksi untuk operasi stok/peminjaman.
- Durasi peminjaman dan perhitungan keterlambatan menggunakan jam. Contoh input: 07:50 sampai 12:00 pada hari yang sama.
- Upload foto dibatasi ke tipe gambar yang diizinkan dan maksimal 2 MB.

## Kebutuhan

- PHP 7.0 atau lebih baru.
- Ekstensi PHP `PDO` dan `pdo_mysql` aktif.
- MySQL atau MariaDB aktif.
- Browser modern.

## Instalasi di XAMPP

1. Ekstrak folder `peminjaman-alat` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka phpMyAdmin, lalu pilih menu **Import**.
4. Impor file `peminjaman.sql` yang berada di root folder project.
5. Buka `http://localhost/peminjaman-alat/`.

Konfigurasi bawaan database: host `127.0.0.1`, port `3306`, database `db_peminjaman_alat`, user `root`, password kosong. Ini cocok untuk konfigurasi XAMPP standar. Bila konfigurasi MySQL berbeda, ikuti bagian **Mengatur koneksi database**.

## Instalasi di AWeb Server (Android)

1. Ekstrak seluruh folder project ke direktori web/document root yang digunakan AWeb Server.
2. Pastikan server PHP dan layanan MySQL/MariaDB aktif. PHP harus menyediakan ekstensi `PDO` dan `pdo_mysql`.
3. Buka pengelola database yang tersedia di perangkat, pilih fitur import, lalu impor file `peminjaman.sql`.
4. Bila host atau kredensial MySQL di Android berbeda dari konfigurasi bawaan, buat file `config/database.local.php` seperti dijelaskan di bawah.
5. Buka alamat HTTP project dari browser. Gunakan halaman `index.php`/folder project, bukan membuka file PHP melalui `file://`.

> AWeb Server dan paket server Android tidak selalu menyediakan MySQL/MariaDB atau ekstensi `pdo_mysql` secara otomatis. Jika database tidak dapat tersambung, pastikan layanan database berjalan dan ekstensi tersebut aktif; aplikasi PHP tidak dapat menyimpan data tanpa database yang dapat diakses.

## Mengatur koneksi database

Salin `config/database.local.example.php` menjadi `config/database.local.php`, lalu ubah nilai sesuai lingkungan server. Contoh konfigurasi standar:

```php
<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'db_peminjaman_alat');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Pada XAMPP default biasanya password `root` kosong. Jangan memakai password contoh ini bila server Anda memakai password lain. File `database.local.php` bersifat lokal dan tidak wajib dibuat bila konfigurasi default sudah sesuai.

## Akun demo

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `admin123` |
| Petugas | `petugas@gmail.com` | `petugas123` |
| Peminjam | `user@gmail.com` | `user123` |

Ganti password akun demo sebelum aplikasi dipakai dengan data nyata.

## Fitur utama

**Admin:** mengelola pengguna, kategori, alat, peminjaman, pengembalian, log aktivitas, dan tarif denda per jam.

**Petugas:** melihat data alat, menyetujui/menolak peminjaman, memproses pengembalian, dan melihat laporan.

**Peminjam:** melihat alat, membuat pengajuan peminjaman, melihat riwayat, dan mengajukan pengembalian.

## Database tunggal

Gunakan **hanya `peminjaman.sql`** untuk instalasi baru. File ini membuat database `db_peminjaman_alat`, tabel dan foreign key yang dibutuhkan aplikasi, nilai awal denda per jam, serta data demo. Mengimpor ulang file ini akan menghapus tabel aplikasi beserta datanya sebelum membuat ulang skema, jadi jangan impor ulang pada database yang berisi data penting.

Durasi pada formulir menggunakan jam `HH:MM`, sedangkan tanggal disimpan bersama waktunya di database. Pemakaian yang dibuat dari formulir peminjam memakai tanggal hari ini dan jam kembali harus lebih besar daripada jam pinjam pada hari yang sama. Denda menghitung setiap jam keterlambatan yang sudah dimulai sebagai satu jam.

## Jika masih muncul error

- **Koneksi database gagal:** periksa apakah MySQL/MariaDB hidup, nama database benar, dan file `config/database.local.php` sesuai.
- **Driver MySQL tidak tersedia:** aktifkan ekstensi `pdo_mysql` pada konfigurasi PHP yang digunakan server web.
- **Halaman kosong / error 500:** lihat log error PHP/Apache; pastikan semua file project diekstrak utuh.
- **CSS tidak tampil:** akses project melalui HTTP dari web server dan pastikan folder `assets` ikut diekstrak.
- **Foto gagal diunggah:** pastikan `uploads/alat` bisa ditulisi oleh PHP dan gambar kurang dari 2 MB.

## Struktur proyek

`assets/`, `config/`, `controllers/`, `helpers/`, `models/`, `views/`, `uploads/`, `index.php`, `router.php`, dan `peminjaman.sql`.
