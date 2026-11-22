# POS Toko Maju Bersama

Aplikasi Point of Sale (POS) sederhana untuk studi kasus UUK PPLG, dibuat menggunakan **PHP Native, PDO, MySQL, HTML, dan CSS**. Project ini merupakan bahan latihan dan dasar yang perlu disesuaikan lagi jika soal UUK memiliki kebutuhan berbeda.

## Fitur

- Login dan logout untuk administrator dan petugas.
- Pembuatan administrator pertama melalui halaman setup.
- Registrasi pengguna oleh administrator.
- Kategori awal dari `database.sql` (Sembako dan ATK).
- CRUD produk: tambah, tampil, edit, hapus.
- Pencarian produk berdasarkan nama produk atau kategori.
- CRUD transaksi: tambah, tampil/detail, edit, hapus.
- Pencarian transaksi berdasarkan nomor transaksi atau nama pelanggan.
- Perhitungan subtotal dan total.
- Stok berkurang saat transaksi dibuat, disesuaikan saat transaksi diedit, dan dikembalikan saat transaksi dihapus.
- Prepared statement PDO, validasi input dasar, escaping output HTML, dan token CSRF pada form perubahan data.

## Persyaratan

- Laragon dengan Apache dan MySQL.
- PHP 8.1 atau lebih baru.
- Ekstensi `pdo_mysql` aktif.
- Browser.

## Cara menjalankan di Laragon

1. Salin folder project dengan nama **`pos-toko-maju`** ke `C:\laragon\www\`.
2. Jalankan Apache dan MySQL dari Laragon.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, lalu impor file `database.sql` dari folder project. File ini membuat database `pos_db`, tabel, relasi, dan data kategori awal.
5. Periksa pengaturan koneksi di `config/database.php`. Pengaturan default Laragon adalah host `127.0.0.1`, database `pos_db`, user `root`, password kosong, dan port `3306`.
6. Buka `http://localhost/pos-toko-maju/setup_admin.php`.
7. Buat akun administrator pertama dengan email dan password milikmu. Password minimal 8 karakter.
8. Buka `http://localhost/pos-toko-maju/login.php`, lalu login.
9. Tambahkan beberapa produk sebelum mencoba membuat transaksi.

> Catatan: URL project menggunakan folder `/pos-toko-maju`. Jika nama folder diubah, sesuaikan path `/pos-toko-maju` pada file PHP dan CSS/JS.

## Struktur folder

```text
pos-toko-maju/
├── app/
│   ├── bootstrap.php
│   └── helpers.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── config/database.php
├── layouts/
│   ├── header.php
│   └── footer.php
├── products/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── delete.php
├── transactions/
│   ├── index.php
│   ├── create.php
│   ├── view.php
│   ├── edit.php
│   └── delete.php
├── database.sql
├── index.php
├── login.php
├── logout.php
├── setup_admin.php
└── register.php
```

## Relasi database

- `categories.id` → `products.category_id` (satu kategori memiliki banyak produk).
- `transactions.id` → `transaction_details.transaction_id` (satu transaksi memiliki banyak detail).
- `products.id` → `transaction_details.product_id` (satu produk dapat muncul pada banyak detail transaksi).

Tabel utama: `categories`, `products`, `transactions`, `transaction_details`, dan `users`.

## Hak akses

| Fitur | Administrator | Petugas |
|---|---:|---:|
| Login / logout | Ya | Ya |
| Registrasi pengguna | Ya | Tidak |
| Melihat kategori dari data awal | Ya | Ya |
| CRUD produk dan pencarian | Ya | Ya |
| CRUD transaksi dan pencarian | Ya | Ya |

## Pengujian yang disarankan

1. Login dengan email/password yang salah harus ditolak.
2. Login dengan akun administrator dan petugas.
3. Administrator dapat mendaftarkan akun petugas.
4. Tambah produk, edit produk, cari produk, dan hapus produk yang belum dipakai transaksi.
5. Buat transaksi dengan beberapa produk. Periksa total dan stok setelah transaksi.
6. Coba membeli barang melebihi stok. Transaksi harus ditolak dan stok tidak berubah.
7. Edit transaksi. Stok lama dikembalikan kemudian stok baru dikurangi.
8. Hapus transaksi. Detail terhapus dan stok dikembalikan.
9. Logout, lalu buka dashboard. Pengguna harus diarahkan kembali ke login.

Contoh uji: stok awal 10, transaksi 3 unit → stok menjadi 7. Hapus transaksi → stok kembali menjadi 10.

## Catatan penting untuk latihan UUK

- Baca soal dan gambar kerja terlebih dahulu. Jangan menganggap semua soal baru pasti sama dengan POS.
- Gunakan project ini sebagai referensi dasar, lalu sesuaikan nama tabel, kolom, relasi, validasi, dan alur bisnis sesuai soal aktual.
- Pada edit transaksi, detail dihitung ulang menggunakan harga produk saat ini. Untuk aplikasi dengan kebutuhan akuntansi yang lebih ketat, aturan harga historis perlu ditentukan sesuai soal.
- Kategori sengaja disediakan melalui SQL awal (seed sederhana), bukan CRUD kategori.
- Untuk penggunaan nyata, tambahkan konfigurasi deployment, pembatasan percobaan login, pencatatan audit, dan pengelolaan environment yang sesuai.

## Upload ke GitHub

Buka terminal di folder project, lalu jalankan:

```bash
git init
git add .
git commit -m "Buat aplikasi POS Toko Maju Bersama"
git branch -M main
git remote add origin https://github.com/USERNAME/REPOSITORY.git
git push -u origin main
```

Ganti `USERNAME/REPOSITORY` sesuai repository GitHub milikmu. Jangan mengunggah password database atau data pribadi.
