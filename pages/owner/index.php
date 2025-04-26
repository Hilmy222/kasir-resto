<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level owner
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'owner') {
    header('Location: ../../index.php');
    exit();
}

// Mengambil data transaksi hari ini menggunakan stored procedure
$query_today = "CALL GetDailyReport(CURDATE())";
if ($result_today = mysqli_query($conn, $query_today)) {
    $today_stats = mysqli_fetch_assoc($result_today);
    mysqli_free_result($result_today);
    // Bersihkan result set yang tersisa
    while (mysqli_next_result($conn)) {
        if ($res = mysqli_store_result($conn)) {
            mysqli_free_result($res);
        }
    }
}

// Mengambil data transaksi minggu ini
$query_week = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total) as total_pendapatan
FROM transaksi 
WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
if ($result_week = mysqli_query($conn, $query_week)) {
    $week_stats = mysqli_fetch_assoc($result_week);
    mysqli_free_result($result_week);
}

// Mengambil data transaksi bulan ini
$query_month = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total) as total_pendapatan
FROM transaksi 
WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
if ($result_month = mysqli_query($conn, $query_month)) {
    $month_stats = mysqli_fetch_assoc($result_month);
    mysqli_free_result($result_month);
}

// Mengambil data menu terlaris menggunakan stored procedure
$query_popular = "CALL GetPopularMenu(5)";
if ($result_popular = mysqli_query($conn, $query_popular)) {
    // Simpan result set untuk digunakan nanti dalam HTML
    $popular_menu = [];
    while ($row = mysqli_fetch_assoc($result_popular)) {
        $popular_menu[] = $row;
    }
    mysqli_free_result($result_popular);
    // Bersihkan result set yang tersisa
    while (mysqli_next_result($conn)) {
        if ($res = mysqli_store_result($conn)) {
            mysqli_free_result($res);
        }
    }
} else {
    die('Error executing stored procedure: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - Sistem Kasir Restoran</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        @media print {
            body {
                background-color: white;
                padding: 20px;
            }
            .header {
                position: fixed;
                top: 0;
                width: 100%;
                background-color: white !important;
                color: black !important;
                padding: 15px 0;
                border-bottom: 2px solid #333;
            }
            .btn-print {
                display: none;
            }
            .btn {
                display: none;
            }
            .container {
                margin-top: 80px;
            }
            .stat-card {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 1px solid #ddd;
            }
            .popular-menu {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 1px solid #ddd;
            }
            @page {
                margin: 0.5cm;
            }
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
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.2rem;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .popular-menu {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Owner</h1>
        <div>
            <button onclick="printReport()" class="btn btn-print" style="background-color: #28a745; margin-right: 10px;">Cetak Laporan</button>
            <a href="../../logout.php" class="btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-container">
            <div class="stat-card">
                <h2>Pendapatan Hari Ini</h2>
                <div class="stat-value">Rp <?php echo number_format($today_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                <div>Total Transaksi: <?php echo $today_stats['total_transaksi'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h2>Pendapatan Minggu Ini</h2>
                <div class="stat-value">Rp <?php echo number_format($week_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                <div>Total Transaksi: <?php echo $week_stats['total_transaksi'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h2>Pendapatan Bulan Ini</h2>
                <div class="stat-value">Rp <?php echo number_format($month_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                <div>Total Transaksi: <?php echo $month_stats['total_transaksi'] ?? 0; ?></div>
            </div>
        </div>

        <div class="popular-menu">
            <h2>Menu Terlaris</h2>
            <table>
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Total Terjual</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popular_menu as $menu): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                        <td><?php echo $menu['total_terjual']; ?></td>
                        <td>Rp <?php echo number_format($menu['total_pendapatan'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function printReport() {
            // Tambahkan kelas print-mode ke body saat mencetak
            document.body.classList.add('print-mode');
            window.print();
            // Hapus kelas setelah selesai mencetak
            window.onafterprint = function() {
                document.body.classList.remove('print-mode');
            };
        }
    </script>
</body>
</html>