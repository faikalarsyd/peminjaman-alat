# Testing

| No | Skenario | Input | Expected Result | Actual Result | Status |
|---:|---|---|---|---|---|
| 1 | Login user | Email/password valid | Masuk dashboard sesuai role | Isi setelah uji XAMPP | Siap diuji |
| 2 | Tambah alat | Data alat valid | Alat tersimpan | Isi setelah uji XAMPP | Siap diuji |
| 3 | Pinjam alat | Jam pinjam dan kembali (contoh 07:50–12:00), jumlah <= stok | MENUNGGU dengan jam tersimpan dan tanggal transaksi tersimpan internal | Isi setelah uji XAMPP | Siap diuji |
| 4 | Kembalikan alat | Waktu pengembalian + kondisi | Return tercatat + denda per jam | Isi setelah uji XAMPP | Siap diuji |
| 5 | Cek privilege | Peminjam akses Admin | 403 / akses ditolak | Isi setelah uji XAMPP | Siap diuji |
| 6 | Login salah | Password salah | Login gagal | Isi setelah uji XAMPP | Siap diuji |
| 7 | Stok kurang | Jumlah > tersedia | Error stok | Isi setelah uji XAMPP | Siap diuji |
| 8 | Approval | MENUNGGU | DISETUJUI + stok turun | Isi setelah uji XAMPP | Siap diuji |
| 9 | Penolakan | MENUNGGU | DITOLAK | Isi setelah uji XAMPP | Siap diuji |
| 10 | Denda per jam | 2 jam 1 menit terlambat | 3 × tarif per jam (pembulatan ke atas) | Isi setelah uji XAMPP | Siap diuji |
| 11 | Akses admin | Peminjam buka `/users` | 403 | Isi setelah uji XAMPP | Siap diuji |
| 12 | CRUD kategori | Tambah/edit/hapus | Data berubah | Isi setelah uji XAMPP | Siap diuji |
| 13 | CRUD user | Tambah/edit/hapus | Data berubah | Isi setelah uji XAMPP | Siap diuji |
