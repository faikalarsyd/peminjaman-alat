# Dokumentasi Program

## Analisis kebutuhan
Tiga role: Admin, Petugas, Peminjam. Fitur mengikuti tabel fitur pada Paket 1: login/logout; CRUD user/alat/kategori/transaksi untuk Admin; approval, pemantauan return dan laporan untuk Petugas; daftar alat, pengajuan pinjam dan pengembalian untuk Peminjam.

## Arsitektur MVC
**View → Controller → Model → Database**. View tidak menjalankan query SQL; Controller memproses request/otorisasi/validasi; Model menangani akses data.

## Fungsi/procedure/method database
- `fn_hitung_denda(rencana, actual, tarif)` mengembalikan denda keterlambatan.
- `sp_ajukan_peminjaman` mengecek stok dan membuat header/detail dengan transaction.
- `sp_setujui_peminjaman` mengecek stok terkunci dan mengubah status; trigger menangani pengurangan stok.
- `sp_proses_pengembalian` menghitung denda dan membuat return request.
- `sp_laporan_peminjaman` menyediakan dataset laporan.
- Trigger menjaga perubahan stok pada approval/return.

## Keamanan
PDO prepared statements, password hashing, password verification, session, CSRF token, role authorization, output escaping, validasi upload dan pembatasan ukuran file.

## Perhitungan denda
`hari_terlambat = max(DATEDIFF(actual, rencana), 0)` dan `denda = hari_terlambat × denda_per_hari`. Tarif default seed: Rp5.000/hari dan dapat diubah Admin melalui Pengaturan.
