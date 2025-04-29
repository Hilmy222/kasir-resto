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
    
    // Get all unpaid orders with the same order code
    $get_related_orders = "SELECT id_pesanan FROM pesanan WHERE kode_pesanan = (SELECT kode_pesanan FROM pesanan WHERE id_pesanan = ?) AND id_pesanan NOT IN (SELECT id_pesanan FROM transaksi)";
    $stmt_related = mysqli_prepare($conn, $get_related_orders);
    mysqli_stmt_bind_param($stmt_related, 'i', $id_pesanan);
    mysqli_stmt_execute($stmt_related);
    $result_related = mysqli_stmt_get_result($stmt_related);
    
    // Process payment for all related orders
    $success = true;
    $receipt_data = null;
    
    while ($related_order = mysqli_fetch_assoc($result_related)) {
        // Free the previous result set if exists
        while (mysqli_next_result($conn)) {
            if ($res = mysqli_store_result($conn)) {
                mysqli_free_result($res);
            }
        }
        
        $query = "CALL ProcessPayment(?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'idd', $related_order['id_pesanan'], $total, $bayar);
        
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
            break;
        }
        
        // Get transaction details from the last processed order
        $result_receipt = mysqli_stmt_get_result($stmt);
        $receipt_data = mysqli_fetch_assoc($result_receipt);
        mysqli_stmt_close($stmt);
    }
    
    // Free the related orders statement and result
    mysqli_stmt_close($stmt_related);
    mysqli_free_result($result_related);
    
    if ($success && $receipt_data) {
        
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

// Fetch pending orders (unpaid) grouped by order code
$query = "SELECT 
            MIN(p.id_pesanan) as id_pesanan,
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            GROUP_CONCAT(DISTINCT CONCAT(mn.nama_menu, ' (', p.jumlah, ' x ', FORMAT(mn.harga, 0), ')') SEPARATOR ', ') as detail_pesanan,
            SUM(p.jumlah * mn.harga) as total_harga,
            m.id as meja_id,
            MIN(p.created_at) as created_at
          FROM pesanan p
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          WHERE NOT EXISTS (SELECT 1 FROM transaksi t WHERE t.id_pesanan = p.id_pesanan)
          GROUP BY p.kode_pesanan, pl.nama_pelanggan, m.no_meja, m.id
          ORDER BY MIN(p.created_at) DESC";

$result = mysqli_query($conn, $query);
?>  
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard | Attex - Responsive Tailwind CSS 3 Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc., Tailwind, TailwindCSS, Tailwind CSS 3" name="description">
    <meta content="coderthemes" name="author">

    <link rel="stylesheet" href="../../public/css/output.css">

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../../../assets/images/favicon.ico">

    <!-- plugin css -->
    <link href="../../assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css">
    
    <!-- Icons css -->
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css">

    <!-- Theme Config Js -->
    <script src="../../assets/js/config.js"></script>
</head>

<body>
    
    <div class="flex wrapper">
        <!-- Sidenav Menu -->
        <div class="app-menu">

            <!-- App Logo -->
            <a href="index.html" class="logo-box">
                <!-- Light Logo -->
                <div class="logo-light">
                    <img src="../../assets/images/logo.png" class="logo-lg h-[22px]" alt="Light logo">
                    <img src="../../assets/images/logo-sm.png" class="logo-sm h-[22px]" alt="Small logo">
                </div>

                <!-- Dark Logo -->
                <div class="logo-dark">
                    <img src="../../assets/images/logo-dark.png" class="logo-lg h-[22px]" alt="Dark logo">
                    <img src="../../assets/images/logo-sm.png" class="logo-sm h-[22px]" alt="Small logo">
                </div>
            </a>

            <!-- Sidenav Menu Toggle Button -->
            <button id="button-hover-toggle" class="absolute top-5 end-2 rounded-full p-1.5 z-50">
                <span class="sr-only">Menu Toggle Button</span>
                <i class="ri-checkbox-blank-circle-line text-xl"></i>
            </button>

            <!--- Menu -->
            <div class="scrollbar" data-simplebar>
                <ul class="menu" data-fc-type="accordion">


                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-calendar-event-line"></i>
                            </span>
                            <span class="menu-text"> Pembayaran </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="riwayat.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-message-3-line"></i>
                            </span>
                            <span class="menu-text"> Riwayat Pesanan </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="laporan.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-message-3-line"></i>
                            </span>
                            <span class="menu-text"> Generate Laporan </span>
                        </a>
                    </li>

                </ul>

            </div>
        </div>
        <!-- Sidenav Menu End  -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="page-content">

            <!-- Topbar Start -->
            <header class="app-header flex items-center px-4 gap-3.5">


                <!-- Sidenav Menu Toggle Button -->
                <button id="button-toggle-menu" class="nav-link p-2">
                    <span class="sr-only">Menu Toggle Button</span>
                    <span class="flex items-center justify-center">
                        <i class="ri-menu-2-fill text-2xl"></i>
                    </span>
                </button>

                <!-- Light/Dark Toggle Button -->
                <div class="lg:flex hidden">
                    <button id="light-dark-mode" type="button" class="nav-link p-2">
                        <span class="sr-only">Light/Dark Mode</span>
                        <span class="flex items-center justify-center">
                            <i class="ri-moon-line text-2xl block dark:hidden"></i>
                            <i class="ri-sun-line text-2xl hidden dark:block"></i>
                        </span>
                    </button>
                </div>

                <!-- Fullscreen Toggle Button -->
                <div class="md:flex hidden">
                    <button data-toggle="fullscreen" type="button" class="nav-link p-2">
                        <span class="sr-only">Fullscreen Mode</span>
                        <span class="flex items-center justify-center">
                            <i class="ri-fullscreen-line text-2xl"></i>
                        </span>
                    </button>
                </div>

                <!-- Profile Dropdown Button -->
                <div class="relative">
                    <button data-fc-type="dropdown" data-fc-placement="bottom-end" type="button" class="nav-link flex items-center gap-2.5 px-3">
                        <img src="../../assets/images/users/avatar-1.jpg" alt="user-image" class="rounded-full h-8">
                        <span class="md:flex flex-col gap-0.5 text-start hidden">
                            <h5 class="text-sm">Admin</h5>
                        </span>
                    </button>

                    <div class="fc-dropdown fc-dropdown-open:opacity-100 hidden opacity-0 w-44 z-50 transition-all duration-300 bg-white shadow-lg border rounded-lg py-2 border-gray-200 dark:border-gray-700 dark:bg-gray-800">

                        <!-- item-->
                        <a href="../../logout.php" class="flex items-center gap-2 py-1.5 px-4 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                            <i class="ri-logout-box-line text-lg align-middle"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </header>
            <!-- Topbar End -->
            <main class="p-6">

            <!-- Page Title Start -->
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-slate-900 dark:text-slate-200 text-lg font-medium">Pembayaran</h4>

            </div>

            <div class="card">
                    <div class="p-6">
                        <h3 class="card-title mb-4">List Pembayaran</h3>

                        <div class="overflow-x-auto">
                            <div class="min-w-full inline-block align-middle">
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Kode Pesanan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Pelanggan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Meja</th>
                                                <!-- <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Detail Pesanan</th> -->
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Total</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php 
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($result)): 
                                        ?>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $no++; ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><span>Meja </span><?php echo $row['no_meja']; ?></td>
                                                <!-- <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php 
                                                $menu_items = explode(', ', $row['detail_pesanan']);
                                                foreach ($menu_items as $item) {
                                                    echo "<span class='menu-item'>$item</span>";
                                                }
                                                ?></td> -->
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><span>Rp<?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center justify-start space-x-3">
                                                    <a href="view.php?kode_pesanan=<?php echo urlencode($row['kode_pesanan']); ?>" class="btn btn-success">View Detail</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>




    <div id="prosesKasir" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 overflow-y-auto">
        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
            <div class="p-4 overflow-y-auto">
                <div class="p-9 text-center text-lg">
                    Proses Pembayaran
                </div>

                <form id="paymentForm" action="process_payment.php" class="px-6" method="POST">
                    <input type="hidden" name="id_pesanan" id="id_pesanan">
                    <div class="space-y-1 mb-6">
                        <label class="font-semibold text-gray-500">Total Pembayaran</label>
                        <input class="form-input" type="text" id="total_display" readonly>
                    </div>
                    <div class="space-y-1 mb-6">
                        <label class="font-semibold text-gray-500">Jumlah Bayar</label>
                        <input class="form-input" type="number" id="bayar" name="bayar" required onkeyup="hitungKembalian()">
                    </div>
                    <div class="space-y-1 mb-6">
                        <label class="font-semibold text-gray-500">Kembalian</label>
                        <input class="form-input" type="text" id="kembalian" readonly>
                    </div>
                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                        <button class="btn bg-primary" type="submit">Proses</button>
                        <button type="button" onclick="document.getElementByI('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main> 

        </div> 
    </div>

    
     <!-- Plugin Js -->
     <script src="../../assets/libs/simplebar/simplebar.min.js"></script>
    <script src="../../assets/libs/lucide/umd/lucide.min.js"></script>
    <script src="../../assets/libs/@frostui/tailwindcss/frostui.js"></script>

    <!-- App Js -->
    <script src="../../assets/js/app.js"></script>

    <!-- Apex Charts js -->
    <script src="../../assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map Js -->
    <script src="../../assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
    <script src="../../assets/libs/jsvectormap/maps/world-merc.js"></script>
    <script src="../../assets/libs/jsvectormap/maps/world.js"></script>

    <!-- Dashboard App js -->
    <script src="../../assets/js/pages/dashboard.js"></script>
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