<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level waiter
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'waiter') {
    header('Location: ../../index.php');
    exit();
}

// Proses tambah pesanan
if (isset($_POST['tambah'])) {
    $id_menu = $_POST['id_menu'];
    $id_pelanggan = $_POST['id_pelanggan'];
    $jumlah = $_POST['jumlah'];
    $id_user = $_SESSION['user_id'];
    $meja_id = $_POST['meja_id'];
    $kode_pesanan = 'PSN-' . date('YmdHis');

    $query = "CALL CreatePesanan(?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issiii', $id_menu, $kode_pesanan, $id_pelanggan, $jumlah, $id_user, $meja_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Pesanan berhasil ditambahkan!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
    mysqli_stmt_close($stmt);
}

// Mengambil data pesanan
$query = "SELECT 
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            GROUP_CONCAT(CONCAT(mn.nama_menu, ' (', p.jumlah, ')') SEPARATOR ', ') as detail_pesanan,
            MIN(p.created_at) as created_at,
            SUM(p.jumlah * mn.harga) as total_harga,
            MAX(CASE WHEN t.id_transaksi IS NULL THEN 'Belum Dibayar' ELSE 'Sudah Dibayar' END) as status_pembayaran
          FROM pesanan p
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
          GROUP BY p.kode_pesanan, pl.nama_pelanggan, m.no_meja, m.id
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Sistem Kasir Restoran</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f1e1;
            color: #4a4a4a;
        }

        .header {
            background-color: #6e4b3a;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
        }

        .nav {
            background-color: #4b3f32;
            padding: 0.75rem 2rem;
            display: flex;
            gap: 16px;
        }

        .nav a {
            color: #f1f5f9;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 5px;
            transition: background-color 0.2s ease;
            font-weight: 500;
        }

        .nav a:hover {
            background-color: #6a5b4c;
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

        .btn-danger {
            background-color: #a94442;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px 30px;
            background-color: #fffef9;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        th, td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #c7b198;
            color:rgb(5, 5, 5);
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9f6f1;
            transition: background-color 0.2s ease;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-belum {
            background-color: #dc3545;
            color: #fff;
        }

        .status-sudah {
            background-color: #28a745;
            color: #fff;
        }

        .detail-pesanan {
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
        }

        footer {
            text-align: center;
            padding: 1rem;
            margin-top: 40px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Pesanan</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Input Pesanan</a>
        <a href="pesanan.php">Daftar Pesanan</a>
    </div>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Detail Pesanan</th>
                    <th>Waktu Pesan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td>Meja <?php echo $row['no_meja']; ?></td>
                    <td class="detail-pesanan"><?php echo htmlspecialchars($row['detail_pesanan']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status_pembayaran'] == 'Belum Dibayar' ? 'status-belum' : 'status-sudah'; ?>">
                            <?php echo $row['status_pembayaran']; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Sistem Kasir Restoran Lin - All rights reserved.
    </footer>
</body>
</html>
