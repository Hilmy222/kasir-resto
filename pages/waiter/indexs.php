<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level waiter
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'waiter') {
    header('Location: ../../index.php');
    exit();
}

// Mengambil data meja yang tersedia
$query_meja = "SELECT * FROM meja ORDER BY no_meja ASC";
$result_meja = mysqli_query($conn, $query_meja);

// Mengambil data menu
$query_menu = "SELECT * FROM menu ORDER BY nama_menu ASC";
$result_menu = mysqli_query($conn, $query_menu);

// Proses tambah pesanan
if (isset($_POST['tambah_pesanan'])) {
    // Generate kode pesanan (format: PSN-YYYYMMDD-XXXX)
    $kode_pesanan = 'PSN-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
    
    // Data pelanggan
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // Insert data pelanggan
    $query_pelanggan = "INSERT INTO pelanggan (nama_pelanggan, jenis_kelamin, no_hp, alamat) 
                        VALUES ('$nama_pelanggan', '$jenis_kelamin', '$no_hp', '$alamat')";
    mysqli_query($conn, $query_pelanggan);
    $id_pelanggan = mysqli_insert_id($conn);
    
    // Update status meja
    $meja_id = (int)$_POST['meja_id'];
    $query_update_meja = "UPDATE meja SET status = 'terpakai' WHERE id = $meja_id";
    mysqli_query($conn, $query_update_meja);
    
    // Cek apakah ada pesanan yang belum dibayar untuk meja ini
    $query_check_existing = "SELECT DISTINCT kode_pesanan FROM pesanan p 
                            LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan 
                            WHERE p.meja_id = $meja_id AND t.id_transaksi IS NULL";
    $result_check = mysqli_query($conn, $query_check_existing);
    $existing_order = mysqli_fetch_assoc($result_check);
    
    // Gunakan kode pesanan yang sudah ada jika ada, jika tidak buat baru
    if ($existing_order) {
        $kode_pesanan = $existing_order['kode_pesanan'];
    }
    
    // Insert pesanan untuk setiap menu yang dipilih
    foreach ($_POST['menu'] as $id_menu => $jumlah) {
        if ($jumlah > 0) {
            $query_pesanan = "INSERT INTO pesanan (id_menu, kode_pesanan, id_pelanggan, jumlah, id_user, meja_id) 
                              VALUES ($id_menu, '$kode_pesanan', $id_pelanggan, $jumlah, {$_SESSION['user_id']}, $meja_id)";
            mysqli_query($conn, $query_pesanan);
        }
    }
    
    header('Location: pesanan.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Waiter - Sistem Kasir Restoran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            padding: 20px;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .nav {
            background-color: #444;
            padding: 10px;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            margin-right: 10px;
        }
        .nav a:hover {
            background-color: #555;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #4CAF50;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .menu-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .menu-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .menu-item label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .menu-item input[type="number"] {
            width: 80px;
        }
        .status-kosong {
            color: #28a745;
        }
        .status-terpakai {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Waiter</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Input Pesanan</a>
        <a href="pesanan.php">Daftar Pesanan</a>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Input Pesanan Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="meja_id">Pilih Meja:</label>
                    <select id="meja_id" name="meja_id" required>
                        <option value="">Pilih Meja</option>
                        <?php while ($meja = mysqli_fetch_assoc($result_meja)): ?>
                            <?php if ($meja['status'] == 'kosong'): ?>
                                <option value="<?php echo $meja['id']; ?>">
                                    Meja <?php echo $meja['no_meja']; ?>
                                    (<?php echo ucfirst($meja['status']); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nama_pelanggan">Nama Pelanggan:</label>
                    <input type="text" id="nama_pelanggan" name="nama_pelanggan" required>
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="laki-laki">Laki-laki</option>
                        <option value="perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP:</label>
                    <input type="text" id="no_hp" name="no_hp">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <input type="text" id="alamat" name="alamat">
                </div>

                <h3>Pilih Menu:</h3>
                <div class="menu-list">
                    <?php mysqli_data_seek($result_menu, 0); ?>
                    <?php while ($menu = mysqli_fetch_assoc($result_menu)): ?>
                        <div class="menu-item">
                            <label>
                                <?php echo htmlspecialchars($menu['nama_menu']); ?>
                                (Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?>)
                            </label>
                            <input type="number" name="menu[<?php echo $menu['id_menu']; ?>]" value="0" min="0">
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" name="tambah_pesanan" class="btn btn-primary">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>