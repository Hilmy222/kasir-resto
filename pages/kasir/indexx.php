<?php
session_start();
require_once '../../config/database.php';

// Process payment using stored procedure
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pesanan'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $bayar = $_POST['bayar'];
    $total = $_POST['total'];
    
    // Validasi pembayaran negatif
    if ($bayar < 0) {
        echo 'Jumlah pembayaran tidak boleh negatif';
        exit;
    }
    
    $kembalian = $bayar - $total;
    
    // Call stored procedure for transaction
    $query = "CALL ProcessPayment(?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'idd', $id_pesanan, $total, $bayar);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get transaction details directly from stored procedure result
        $result_receipt = mysqli_stmt_get_result($stmt);
        $receipt_data = mysqli_fetch_assoc($result_receipt);
        
        // Format receipt data as JSON
        echo json_encode([
            'status' => 'success',
            'receipt' => $receipt_data
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan transaksi']);
        exit;
    }
}

// Check if user is logged in and has cashier level access
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'kasir') {
    header('Location: ../../index.php');
    exit();
}

// Fetch pending orders (unpaid)
$query = "SELECT 
            p.id_pesanan,
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            GROUP_CONCAT(CONCAT(mn.nama_menu, ' (', p.jumlah, ' x ', mn.harga, ')') SEPARATOR ', ') as detail_pesanan,
            SUM(p.jumlah * mn.harga) as total_harga
          FROM pesanan p
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
          WHERE t.id_transaksi IS NULL
          GROUP BY p.id_pesanan
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Sistem Kasir Restoran</title>
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
            background-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .detail-pesanan {
            max-width: 300px;
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
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
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
        <h1>Pembayaran</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Pembayaran</a>
        <a href="riwayat.php">Riwayat Transaksi</a>
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
                    <th>Total</th>
                    <th>Aksi</th>
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
                    <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td>
                        <button class="btn btn-primary" onclick="showPaymentModal('<?php echo $row['id_pesanan']; ?>', <?php echo $row['total_harga']; ?>)">Proses Pembayaran</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Pembayaran -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <h2>Proses Pembayaran</h2>
            <form id="paymentForm" action="process_payment.php" method="POST">
                <input type="hidden" name="id_pesanan" id="id_pesanan">
                <div class="form-group">
                    <label>Total Pembayaran:</label>
                    <input type="text" id="total_display" readonly>
                </div>
                <div class="form-group">
                    <label>Jumlah Bayar:</label>
                    <input type="number" name="bayar" id="bayar" required onkeyup="hitungKembalian()">
                </div>
                <div class="form-group">
                    <label>Kembalian:</label>
                    <input type="text" id="kembalian" readonly>
                </div>
                <div style="text-align: right;">
                    <button type="button" class="btn btn-danger" onclick="closePaymentModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPaymentModal(idPesanan, total) {
            document.getElementById('paymentModal').style.display = 'block';
            document.getElementById('id_pesanan').value = idPesanan;
            document.getElementById('total_display').value = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('bayar').value = '';
            document.getElementById('kembalian').value = '';
            window.totalPembayaran = total;
        }

        function printReceipt(receiptData) {
            // Validasi data transaksi
            if (!receiptData || !receiptData.id_transaksi) {
                alert('Data transaksi tidak valid atau belum tersedia');
                return;
            }

            try {
                const printWindow = window.open('', '', 'width=300,height=600');
                if (!printWindow) {
                    alert('Popup diblokir! Mohon izinkan popup untuk mencetak struk.');
                    return;
                }

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Struk Pembayaran</title>
                        <style>
                            body { font-family: monospace; font-size: 12px; margin: 0; padding: 10px; }
                            .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
                            .detail { margin: 10px 0; }
                            .menu-item { margin-left: 10px; }
                            .total { margin-top: 10px; border-top: 1px dashed #000; padding-top: 10px; }
                            .footer { text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; }
                            .align-right { text-align: right; }
                            .bold { font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2 style="margin: 0;">RESTORAN IMY</h2>
                            <p style="margin: 5px 0;">Jl. Contoh No. 123, Kota</p>
                            <p style="margin: 5px 0;">Telp: (021) 1234567</p>
                            <p style="margin: 5px 0;">NPWP: 12.345.678.9-012.345</p>
                        </div>
                        <div class="detail">
                            <p style="margin: 3px 0;">No. Transaksi: ${receiptData.id_transaksi}</p>
                            <p style="margin: 3px 0;">Kode Pesanan: ${receiptData.kode_pesanan || '-'}</p>
                            <p style="margin: 3px 0;">Pelanggan: ${receiptData.nama_pelanggan || '-'}</p>
                            <p style="margin: 3px 0;">Meja: ${receiptData.no_meja || '-'}</p>
                            <p style="margin: 3px 0;">Tanggal: ${new Date(receiptData.created_at).toLocaleString('id-ID')}</p>
                            <p style="margin: 10px 0 5px;">Detail Pesanan:</p>
                            <pre style="margin: 0;">${receiptData.detail_pesanan || '-'}</pre>
                        </div>
                        <div class="total">
                            <p class="bold" style="margin: 3px 0;">Total: Rp ${parseInt(receiptData.total || 0).toLocaleString('id-ID')}</p>
                            <p style="margin: 3px 0;">Bayar: Rp ${parseInt(receiptData.bayar || 0).toLocaleString('id-ID')}</p>
                            <p style="margin: 3px 0;">Kembalian: Rp ${parseInt(receiptData.kembalian || 0).toLocaleString('id-ID')}</p>
                        </div>
                        <div class="footer">
                            <p style="margin: 5px 0;">Terima kasih atas kunjungan Anda</p>
                            <p style="margin: 5px 0;">Selamat menikmati hidangan kami</p>
                            <p style="margin: 5px 0; font-size: 10px;">* Struk ini merupakan bukti pembayaran yang sah *</p>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            } catch (error) {
                alert('Terjadi kesalahan saat mencetak struk: ' + error.message);
            }
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function hitungKembalian() {
            const bayar = parseFloat(document.getElementById('bayar').value) || 0;
            
            if (bayar < 0) {
                alert('Jumlah pembayaran tidak boleh negatif!');
                document.getElementById('bayar').value = '';
                document.getElementById('kembalian').value = '';
                const submitBtn = document.querySelector('#paymentForm button[type="submit"]');
                submitBtn.disabled = true;
                return;
            }
            
            const kembalian = bayar - window.totalPembayaran;
            document.getElementById('kembalian').value = 'Rp ' + kembalian.toLocaleString('id-ID');
            
            // Enable/disable submit button based on payment amount
            const submitBtn = document.querySelector('#paymentForm button[type="submit"]');
            submitBtn.disabled = bayar < window.totalPembayaran;
        }

        // Handle form submission
        document.getElementById('paymentForm').onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('id_pesanan', document.getElementById('id_pesanan').value);
            formData.append('bayar', document.getElementById('bayar').value);
            formData.append('total', window.totalPembayaran);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert('Pembayaran berhasil!');
                    printReceipt(result.receipt);
                    window.location.reload();
                } else {
                    alert('Pembayaran gagal! ' + result.message);
                }
                closePaymentModal();
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
                closePaymentModal();
            });
        };

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target == modal) {
                closePaymentModal();
            }
        }
    </script>
</body>
</html>