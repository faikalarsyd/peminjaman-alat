# ERD

```text
roles 1---N users 1---N peminjaman 1---N detail_peminjaman N---1 alat N---1 kategori
                         |
                         1
                         |
                  pengembalian

users 1---N log_aktivitas
```

`pengaturan` menyimpan konfigurasi seperti `denda_per_jam`. Primary key, foreign key, index, dan constraint terdapat pada `peminjaman.sql`.
