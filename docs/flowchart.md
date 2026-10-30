# Flowchart / Pseudocode

## Login
Mulai → form login → validasi email/password → ambil user dan role → `password_verify()` dan status Aktif? → Ya: buat session dan tampilkan dashboard → Tidak: tampilkan notifikasi gagal.

## Peminjaman
Pilih alat → isi jumlah, jam pinjam, jam rencana kembali, dan keperluan → validasi input serta stok → mulai transaksi database → buat peminjaman dan detail → status MENUNGGU → commit → Admin/Petugas menyetujui → stok berkurang secara atomik.

## Pengembalian dan denda
Pilih transaksi aktif → isi jam pengembalian dan kondisi → hitung keterlambatan serta denda per jam → buat pengajuan pengembalian → Admin/Petugas memproses → stok dikembalikan secara atomik → status peminjaman menjadi DIKEMBALIKAN.
