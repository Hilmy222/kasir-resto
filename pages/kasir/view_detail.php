
<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level kasir
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'kasir') {
    header('Location: ../../index.php');
    exit();
}

// Cek apakah kode pesanan ada
if (!isset($_GET['kode_pesanan'])) {
    header('Location: index.php');
    exit();
}

$kode_pesanan = $_GET['kode_pesanan'];

// Ambil detail pesanan
$query = "SELECT 
            p.id_pesanan,
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            mn.nama_menu,
            mn.harga,
            p.jumlah,
            (p.jumlah * mn.harga) as subtotal,
            p.created_at
          FROM pesanan p
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
          WHERE p.kode_pesanan = ? AND t.id_transaksi IS NULL";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $kode_pesanan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

// Hitung total pesanan
$total_pesanan = 0;
$detail_pesanan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $detail_pesanan[] = $row;
    $total_pesanan += $row['subtotal'];
}

// Ambil data pesanan pertama untuk informasi umum
$pesanan_info = $detail_pesanan[0];
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Sistem Kasir Restoran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            background-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }
        #paymentModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            float: right;
            cursor: pointer;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover {
            color: #000;
        }
        #paymentForm {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detail Pesanan</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Pembayaran</a>
        <a href="riwayat.php">Riwayat Transaksi</a>
    </div>

    <div class="container">
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Kode Pesanan:</div>
                <div><?php echo htmlspecialchars($pesanan_info['kode_pesanan']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama Pelanggan:</div>
                <div><?php echo htmlspecialchars($pesanan_info['nama_pelanggan']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nomor Meja:</div>
                <div>Meja <?php echo $pesanan_info['no_meja']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Waktu Pesanan:</div>
                <div><?php echo date('d/m/Y H:i', strtotime($pesanan_info['created_at'])); ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail_pesanan as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nama_menu']); ?></td>
                    <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $item['jumlah']; ?></td>
                    <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            Total: Rp <?php echo number_format($total_pesanan, 0, ',', '.'); ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-primary" onclick="openPaymentModal()">Proses Pembayaran</button>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div id="paymentModal">
        <div class="modal-content">
            <span class="close" onclick="closePaymentModal()">&times;</span>
            <h2>Form Pembayaran</h2>
            <form id="paymentForm" onsubmit="processPayment(event)">
                <input type="hidden" name="id_pesanan" value="<?php echo $pesanan_info['id_pesanan']; ?>">
                <input type="hidden" name="total" value="<?php echo $total_pesanan; ?>">
                <div class="form-group">
                    <label>Total Pembayaran:</label>
                    <div>Rp <?php echo number_format($total_pesanan, 0, ',', '.'); ?></div>
                </div>
                <div class="form-group">
                    <label for="bayar">Jumlah Bayar:</label>
                    <div class="flex">
                    <input type="number" id="bayar" name="bayar" required   >
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal() {
            document.getElementById('paymentModal').style.display = 'block';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function processPayment(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Pembayaran berhasil!');
                    window.location.href = 'index.php';
                } else {
                    alert('Gagal memproses pembayaran: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran');
            });
        }
    </script>
</body>
</html>
