# Evaluasi

## Fitur yang disiapkan
Login/register/logout, tiga role, CRUD pengguna/alat/kategori, peminjaman, persetujuan, pengembalian, denda per jam, pembaruan stok, log aktivitas, laporan, pengaturan denda, profil, bantuan, informasi aplikasi, dan antarmuka responsif.

## Hasil pemeriksaan di lingkungan pembuatan
- Seluruh file PHP lulus pemeriksaan sintaks menggunakan PHP CLI yang tersedia.
- Bootstrap konfigurasi, helper URL, dan definisi kelas koneksi database berhasil diuji.
- File SQL tunggal `peminjaman.sql` berisi sembilan tabel yang dibutuhkan aplikasi beserta data demo.
- Uji koneksi database dan transaksi secara langsung belum dapat dilakukan pada lingkungan ini karena ekstensi PHP `pdo_mysql` serta layanan MySQL/MariaDB tidak tersedia. Jalankan pengujian integrasi setelah database diimpor dan `pdo_mysql` diaktifkan di lingkungan XAMPP/AWeb.

## Rencana pengembangan
Reset password nyata, notifikasi, ekspor PDF, pagination, dashboard grafik, transaksi multi-item dari UI, dan audit log lebih detail.
