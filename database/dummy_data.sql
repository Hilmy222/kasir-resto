-- Menambahkan data dummy untuk tabel users
INSERT INTO users (nama, password, level) VALUES
('Admin Utama', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Waiter 1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter'),
('Waiter 2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter'),
('Kasir 1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir'),
('Owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner');

-- Menambahkan data dummy untuk tabel menu
INSERT INTO menu (nama_menu, harga) VALUES
('Nasi Goreng Spesial', 25000),
('Mie Goreng', 20000),
('Ayam Bakar', 30000),
('Sate Ayam', 25000),
('Es Teh Manis', 5000),
('Es Jeruk', 7000),
('Juice Alpukat', 12000),
('Air Mineral', 4000);

-- Menambahkan data dummy untuk tabel meja
INSERT INTO meja (no_meja, status) VALUES
(1, 'kosong'),
(2, 'terpakai'),
(3, 'kosong'),
(4, 'terpakai'),
(5, 'kosong'),
(6, 'kosong');

-- Menambahkan data dummy untuk tabel pelanggan
INSERT INTO pelanggan (nama_pelanggan, jenis_kelamin, no_hp, alamat) VALUES
('Budi Santoso', 'laki-laki', '08123456789', 'Jl. Merdeka No. 10'),
('Siti Rahayu', 'perempuan', '08234567890', 'Jl. Pahlawan No. 15'),
('Ahmad Hidayat', 'laki-laki', '08345678901', 'Jl. Sudirman No. 20'),
('Dewi Lestari', 'perempuan', '08456789012', 'Jl. Gatot Subroto No. 25');

-- Menambahkan data dummy untuk tabel pesanan
INSERT INTO pesanan (id_menu, kode_pesanan, id_pelanggan, jumlah, id_user, meja_id) VALUES
(1, 'PSN-001', 1, 2, 2, 2),  -- Nasi Goreng untuk Budi oleh Waiter 1
(3, 'PSN-001', 1, 1, 2, 2),  -- Ayam Bakar untuk Budi oleh Waiter 1
(5, 'PSN-001', 1, 2, 2, 2),  -- Es Teh untuk Budi oleh Waiter 1
(2, 'PSN-002', 2, 1, 3, 4),  -- Mie Goreng untuk Siti oleh Waiter 2
(7, 'PSN-002', 2, 1, 3, 4);  -- Juice Alpukat untuk Siti oleh Waiter 2

-- Menambahkan data dummy untuk tabel transaksi
INSERT INTO transaksi (id_pesanan, total, bayar) VALUES
(1, 85000, 100000),  -- Transaksi untuk pesanan Budi (PSN-001)
(4, 32000, 50000);   -- Transaksi untuk pesanan Siti (PSN-002)