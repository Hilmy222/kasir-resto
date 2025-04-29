<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level kasir
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'kasir') {
    header('Location: ../../index.php');
    exit();
}

// Mengambil data transaksi
$query = "SELECT 
            t.id_transaksi,
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            GROUP_CONCAT(CONCAT(mn.nama_menu, ' (', p.jumlah, ' x ', mn.harga, ')') SEPARATOR ', ') as detail_pesanan,
            t.total,
            t.bayar,
            t.kembalian,
            t.created_at
          FROM transaksi t
          JOIN pesanan p ON t.id_pesanan = p.id_pesanan
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          GROUP BY t.id_transaksi
          ORDER BY t.created_at DESC";

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
                            <span class="menu-text"> Riwayat Transaksi </span>
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
            <!-- Page Title End -->
            <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="card">
                    <div class="p-6">
                        <h3 class="card-title mb-4">Riwayat Transaksi</h3>

                        <div class="w-full overflow-x-auto">
                            <div class="min-w-full inline-block align-middle">
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No</th>
                                                <!-- <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">ID Transaksi</th> -->
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Kode Pesanan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Pelanggan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Meja</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Total</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Bayar</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Kembalian</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Tanggal</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php 
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($result)): 
                                        ?>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $no++; ?></td>
                                                <!-- <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $row['id_transaksi']; ?></td> -->
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Meja <?php echo $row['no_meja']; ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp<?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp<?php echo number_format($row['bayar'], 0, ',', '.'); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp<?php echo number_format($row['kembalian'], 0, ',', '.'); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td class="px-4 py-4">
                                                    <button onclick="printReceipt(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="btn bg-primary text-white">Cetak Struk</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    <script>
                                        function printReceipt(receiptData) {
                                            if (!receiptData || !receiptData.id_transaksi) {
                                                alert('Data transaksi tidak valid atau belum tersedia');
                                                return;
                                            }

                                            try {
                                                const printWindow = window.open('', '', 'width=1920,height=1080');
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
                                                        </div>
                                                        <div class="menu-items">
                                                            <p class="bold">Detail Pesanan:</p>
                                                            ${receiptData.detail_pesanan.split(', ').map(item => `<p class="menu-item">${item}</p>`).join('')}
                                                        </div>
                                                        <div class="total">
                                                            <p class="bold">Total: Rp${Number(receiptData.total).toLocaleString('id-ID')}</p>
                                                            <p>Bayar: Rp${Number(receiptData.bayar).toLocaleString('id-ID')}</p>
                                                            <p>Kembalian: Rp${Number(receiptData.kembalian).toLocaleString('id-ID')}</p>
                                                        </div>
                                                        <div class="footer">
                                                            <p>Terima kasih atas kunjungan Anda</p>
                                                            <p>Silakan datang kembali</p>
                                                        </div>
                                                    </body>
                                                    </html>
                                                `);

                                                printWindow.document.close();
                                                printWindow.focus();
                                                printWindow.print();
                                                printWindow.close();
                                            } catch (error) {
                                                console.error('Error printing receipt:', error);
                                                alert('Terjadi kesalahan saat mencetak struk');
                                            }
                                        }
                                    </script>
                                    </table>
                                    
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card -->




            </main>

        </div> 
    </div>
    </body>
</html>