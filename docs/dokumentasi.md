# Dokumentasi Program

## Analisis kebutuhan
Tiga role: Admin, Petugas, dan Peminjam. Admin mengelola pengguna, alat, kategori, dan transaksi; Petugas melakukan persetujuan/pemantauan peminjaman dan pengembalian serta melihat laporan; Peminjam melihat alat, mengajukan pinjaman, dan mengajukan pengembalian.

## Arsitektur MVC
**View → Controller → Model → Database**. View menampilkan halaman; Controller memproses request, otorisasi, dan validasi; Model menangani akses data menggunakan PDO.

## Database
Impor satu file `peminjaman.sql`. File ini membuat database `db_peminjaman_alat`, sembilan tabel, relasi foreign key, nilai awal `denda_per_jam`, dan data contoh. Aplikasi tidak membutuhkan stored procedure atau trigger untuk menjalankan transaksi; alur stok dan transaksi ditangani oleh model PHP dengan prepared statements dan transaksi database.

## Keamanan
PDO prepared statements, password hashing, password verification, session, CSRF token, otorisasi berbasis role, output escaping, validasi unggahan, dan pembatasan ukuran file.

## Perhitungan denda
`jam_terlambat = max(ceil((actual - rencana) dalam detik / 3600), 0)` dan `denda = jam_terlambat × denda_per_jam`. Keterlambatan yang belum mencapai satu jam penuh tetap dihitung satu jam. Tarif awal Rp208,33 per jam dan dapat diubah Admin melalui Pengaturan.
