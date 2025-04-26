-- Membuat Database
USE kasir_imy;

-- Membuat Tabel menu
CREATE TABLE menu (
    id_menu BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama_menu VARCHAR(255) NOT NULL,
    harga INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat Tabel users
CREATE TABLE users (
    id_user BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    level ENUM('admin', 'waiter', 'kasir', 'owner') NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat Tabel pelanggan
CREATE TABLE pelanggan (
    id_pelanggan BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama_pelanggan VARCHAR(255) NOT NULL,
    jenis_kelamin ENUM('laki-laki', 'perempuan') NOT NULL,
    no_hp VARCHAR(255),
    alamat VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat Tabel meja
CREATE TABLE meja (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    no_meja INT NOT NULL,
    status ENUM('kosong', 'terpakai') DEFAULT 'kosong',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat Tabel pesanan
CREATE TABLE pesanan (
    id_pesanan BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    id_menu BIGINT UNSIGNED NOT NULL,
    kode_pesanan VARCHAR(25) NOT NULL,
    id_pelanggan BIGINT UNSIGNED NOT NULL,
    jumlah INT NOT NULL,
    id_user BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    meja_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (meja_id) REFERENCES meja(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Membuat Tabel transaksi
CREATE TABLE transaksi (
    id_transaksi BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    id_pesanan BIGINT UNSIGNED NOT NULL,
    total INT NOT NULL,
    bayar INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    kembalian BIGINT GENERATED ALWAYS AS (bayar - total) STORED,
    kurang BIGINT GENERATED ALWAYS AS (total - bayar) STORED,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Stored Procedures

-- Prosedur untuk membuat pesanan baru
DELIMITER //
CREATE PROCEDURE CreatePesanan(
    IN p_id_menu BIGINT,
    IN p_kode_pesanan VARCHAR(25),
    IN p_id_pelanggan BIGINT,
    IN p_jumlah INT,
    IN p_id_user BIGINT,
    IN p_meja_id BIGINT
)
BEGIN
    INSERT INTO pesanan (id_menu, kode_pesanan, id_pelanggan, jumlah, id_user, meja_id)
    VALUES (p_id_menu, p_kode_pesanan, p_id_pelanggan, p_jumlah, p_id_user, p_meja_id);
    
    UPDATE meja SET status = 'terpakai' WHERE id = p_meja_id;
END //
DELIMITER ;

-- Prosedur untuk memproses pembayaran
DELIMITER //
CREATE PROCEDURE ProcessPayment(
    IN p_id_pesanan BIGINT,
    IN p_total INT,
    IN p_bayar INT
)
BEGIN
    DECLARE v_meja_id BIGINT;
    DECLARE v_last_id BIGINT;
    
    -- Menyimpan transaksi
    INSERT INTO transaksi (id_pesanan, total, bayar)
    VALUES (p_id_pesanan, p_total, p_bayar);
    
    -- Mendapatkan ID transaksi yang baru dibuat
    SET v_last_id = LAST_INSERT_ID();
    
    -- Mengambil ID meja dari pesanan
    SELECT meja_id INTO v_meja_id FROM pesanan WHERE id_pesanan = p_id_pesanan;
    
    -- Mengupdate status meja menjadi kosong
    UPDATE meja SET status = 'kosong' WHERE id = v_meja_id;
    
    -- Mengembalikan data transaksi lengkap
    SELECT 
        t.id_transaksi,
        p.kode_pesanan,
        pl.nama_pelanggan,
        m.no_meja,
        GROUP_CONCAT(CONCAT(mn.nama_menu, ' (', p.jumlah, ' x ', mn.harga, ')') SEPARATOR '\n') as detail_pesanan,
        t.total,
        t.bayar,
        t.kembalian,
        t.created_at
    FROM transaksi t
    JOIN pesanan p ON t.id_pesanan = p.id_pesanan
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    JOIN meja m ON p.meja_id = m.id
    JOIN menu mn ON p.id_menu = mn.id_menu
    WHERE t.id_transaksi = v_last_id
    GROUP BY t.id_transaksi, p.kode_pesanan, pl.nama_pelanggan, m.no_meja, t.total, t.bayar, t.kembalian, t.created_at;
END //
DELIMITER ;

-- Prosedur untuk mendapatkan laporan pendapatan harian
DELIMITER //
CREATE PROCEDURE GetDailyReport(
    IN p_date DATE
)
BEGIN
    SELECT 
        COUNT(*) as total_transaksi,
        SUM(total) as total_pendapatan
    FROM transaksi 
    WHERE DATE(created_at) = p_date;
END //
DELIMITER ;

-- Prosedur untuk mendapatkan menu terlaris
DELIMITER //
CREATE PROCEDURE GetPopularMenu(
    IN p_limit INT
)
BEGIN
    SELECT 
        m.nama_menu,
        SUM(p.jumlah) as total_terjual,
        SUM(p.jumlah * m.harga) as total_pendapatan
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    JOIN transaksi t ON p.id_pesanan = t.id_pesanan
    GROUP BY m.id_menu, m.nama_menu
    ORDER BY total_terjual DESC
    LIMIT p_limit;
END //
DELIMITER ;