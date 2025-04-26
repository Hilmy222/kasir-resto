# Dokumentasi Aplikasi Kasir Restoran

## Daftar Isi
1. [Arsitektur Aplikasi](#arsitektur-aplikasi)
2. [Sistem Autentikasi](#sistem-autentikasi)
3. [Modul-modul Aplikasi](#modul-modul-aplikasi)
4. [Stored Procedure](#stored-procedure)
5. [Alur Bisnis](#alur-bisnis)

## Arsitektur Aplikasi

Aplikasi kasir restoran ini dibangun menggunakan arsitektur PHP tradisional dengan basis data MySQL. Berikut adalah struktur utama aplikasi:

```
/
├── config/
│   └── database.php     # Konfigurasi koneksi database
├── database/
│   ├── kasir_imy.sql    # Skema database dan stored procedures
│   └── dummy_data.sql   # Data contoh untuk testing
├── pages/
│   ├── admin/          # Halaman untuk administrator
│   ├── kasir/          # Halaman untuk kasir
│   ├── owner/          # Halaman untuk pemilik
│   └── waiter/         # Halaman untuk pelayan
└── index.php           # Halaman login utama
```

## Sistem Autentikasi

Sistem autentikasi diimplementasikan di `index.php` (baris 1-44) dengan fitur:

- Manajemen sesi menggunakan `session_start()`
- Validasi level akses (admin, waiter, kasir, owner)
- Enkripsi password menggunakan `password_hash()` dan `password_verify()`
- Redirect otomatis ke halaman sesuai level pengguna

Kode autentikasi utama terdapat di `index.php` yang menangani:
- Validasi kredensial login (baris 24-39)
- Pengecekan level akses (baris 6-19)
- Manajemen sesi pengguna (baris 34-37)

## Modul-modul Aplikasi

### 1. Modul Admin (pages/admin/)

Implementasi di `pages/admin/user.php` dan `index.php`:
- Manajemen pengguna (CRUD users)
- Dashboard admin dengan statistik (total menu, meja, pengguna)
- Validasi duplikasi username (baris 16-29 di user.php)

### 2. Modul Kasir (pages/kasir/)

Implementasi di `pages/kasir/riwayat.php`:
- Pencatatan transaksi pembayaran
- Riwayat transaksi dengan detail lengkap
- Query kompleks untuk menampilkan riwayat (baris 11-24)

### 3. Modul Owner (pages/owner/)

Implementasi di `pages/owner/index.php`:
- Dashboard dengan statistik pendapatan
- Laporan menu terlaris
- Integrasi dengan stored procedure untuk laporan

## Stored Procedure

Aplikasi menggunakan 4 stored procedure utama yang diimplementasikan di `database/kasir_imy.sql`:

### 1. CreatePesanan (Baris 89-102)
Membuat pesanan baru dan mengupdate status meja:
```sql
CREATE PROCEDURE CreatePesanan(
    IN p_id_menu BIGINT,
    IN p_kode_pesanan VARCHAR(25),
    IN p_id_pelanggan BIGINT,
    IN p_jumlah INT,
    IN p_id_user BIGINT,
    IN p_meja_id BIGINT
)
```

### 2. ProcessPayment (Baris 105-146)
Memproses pembayaran dan mengupdate status meja:
- Mencatat transaksi pembayaran
- Mengupdate status meja menjadi kosong
- Mengembalikan detail transaksi lengkap

### 3. GetDailyReport (Baris 149-159)
Menghasilkan laporan pendapatan harian:
```sql
CREATE PROCEDURE GetDailyReport(
    IN p_date DATE
)
```

### 4. GetPopularMenu (Baris 162-178)
Menampilkan daftar menu terlaris:
- Menghitung total penjualan per menu
- Menghitung total pendapatan per menu
- Mengurutkan berdasarkan jumlah terjual

## Alur Bisnis

1. **Proses Pemesanan**:
   - Pelayan (waiter) mencatat pesanan pelanggan
   - Sistem mencatat detail pesanan dan mengupdate status meja
   - Menggunakan stored procedure `CreatePesanan`

2. **Proses Pembayaran**:
   - Kasir memproses pembayaran
   - Sistem mencatat transaksi dan mengupdate status meja
   - Menggunakan stored procedure `ProcessPayment`

3. **Pelaporan**:
   - Owner dapat melihat laporan harian/mingguan/bulanan
   - Sistem menggunakan stored procedure `GetDailyReport` dan `GetPopularMenu`
   - Data ditampilkan dalam format yang mudah dibaca

4. **Manajemen Data**:
   - Admin mengelola data pengguna, menu, dan meja
   - Sistem memvalidasi input dan mencegah duplikasi data
   - Menggunakan foreign key untuk menjaga integritas data

Setiap modul aplikasi dirancang dengan mempertimbangkan keamanan dan kemudahan penggunaan, dengan implementasi validasi input dan manajemen sesi yang konsisten di seluruh aplikasi.