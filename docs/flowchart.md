# Flowchart / Pseudocode

## Login
Mulai → form login → validasi email/password → ambil user + role → `password_verify()` dan status Aktif? → Ya: buat session dan dashboard → Tidak: notifikasi gagal.

## Peminjaman
Pilih alat → isi jumlah/tanggal/keperluan → `sp_ajukan_peminjaman` → cek stok → stok kurang: rollback/error → stok cukup: insert peminjaman + detail → status MENUNGGU → commit → Petugas approval → trigger mengurangi stok.

## Pengembalian dan denda
Pilih transaksi aktif → isi tanggal/kondisi → `fn_hitung_denda()` → pengembalian MENUNGGU → Petugas proses → trigger menambah stok → peminjaman DIKEMBALIKAN.
