
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
                        <a href="index.php" data-fc-type="collapse" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-home-4-line"></i>
                            </span>
                            <span class="menu-text"> Pembayaran </span>
                        </a>
                    </li>


                    <li class="menu-item">
                        <a href="riwayat.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-calendar-event-line"></i>
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
            <div class="card w-3/4 mx-auto">
                <div class="p-6">
                    <h3 class="card-title mb-4">Detail Pesanan</h3>
                    <div class="flex flex-col gap-1">
                        <div class="grid grid-cols-3">
                            <div class="col-span-4">Kode Pesanan</div>
                            <div class="flex gap-2">
                                <span>:</span>
                                <span><?php echo htmlspecialchars($pesanan_info['kode_pesanan']); ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3">
                            <div class="col-span-4">Nama Pelanggan</div>
                            <div class="flex gap-2">
                                <span>:</span>
                                <span><?php echo htmlspecialchars($pesanan_info['nama_pelanggan']); ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3">
                            <div class="col-span-4">Nomor Meja</div>
                            <div class="flex gap-2">
                                <span>:</span>
                                <span><span>Meja </span><?php echo $pesanan_info['no_meja']; ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3">
                            <div class="col-span-4">Waktu Pesanan</div>
                            <div class="flex gap-2">
                                <span>:</span>
                                <span><?php echo date('d/m/Y H:i', strtotime($pesanan_info['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <div class="min-w-full inline-block align-middle">
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Menu</th>
                                            <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Harga</th>
                                            <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Jumlah</th>
                                            <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($detail_pesanan as $item): ?>
                                        <tr class="bg-gray-50 dark:bg-gray-900">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($item['nama_menu']); ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $item['jumlah']; ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="flex justify-center mt-10">
                                    <div class="flex gap-6 text-lg text-gray-800 font-semibold">
                                        <div>Total</div>
                                        <div class="flex gap-2">
                                            <span>:</span>
                                            <span><span>Rp </span><?php echo number_format($total_pesanan, 0, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center mt-4">
                                    <button data-fc-type="modal" data-fc-target="#pembayaran" class="btn bg-primary text-white" >Proses Pembayaran</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pembayaran" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                            <div class="p-4 overflow-y-auto">
                                <div class="p-9 text-center text-lg">
                                    Pembayaran
                                </div>

                                <form class="px-6" id="paymentForm" onsubmit="processPayment(event)">
                                <input type="hidden" name="id_pesanan" value="<?php echo $pesanan_info['id_pesanan']; ?>">
                                <input type="hidden" name="total" value="<?php echo $total_pesanan; ?>">

                                <div class="flex gap-6 text-gray-500 font-semibold">
                                    <div>Total Pembayaran</div>
                                    <div class="flex gap-2">
                                        <span>:</span>
                                        <span><span>Rp </span><?php echo number_format($total_pesanan, 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="space-y-1 mb-6">
                                    <label for="bayar" class="font-semibold text-gray-500">Jumlah Bayar</label>
                                    <input class="form-input" type="number" id="bayar" name="bayar" required min="<?php echo $total_pesanan; ?>">
                                </div>

                                <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                    <button class="btn bg-primary" type="submit">Simpan</button>
                                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </main>
            
                    
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
        </body>
    </html> 