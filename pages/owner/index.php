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
                    <h4 class="text-slate-900 dark:text-slate-200 text-lg font-medium">Dashboard</h4>
                    <button onclick="printReport()" class="btn bg-success text-white" style="background-color: #28a745; margin-right: 10px;">Cetak Laporan</button>

                </div>
                <!-- Page Title End -->

                <div class="grid 2xl:grid-cols-5 lg:grid-cols-6 md:grid-cols-2 gap-6 mb-6">
                    <div class="2xl:col-span-1 lg:col-span-2">
                        <div class="card">
                            <div class="p-6">
                                <div class="flex justify-between">
                                    <div class="grow overflow-hidden">
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Number of Customers">Pendapatan Hari Ini</h5>
                                        <h3 class="text-2xl my-6">Rp<?php echo number_format($today_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
                                        <h3>Total Transaksi : <?php echo $today_stats['total_transaksi'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div> <!-- end p-6-->
                        </div> <!-- end card-->
                    </div>

                    <div class="2xl:col-span-1 lg:col-span-2">
                        <div class="card">
                            <div class="p-6">
                                <div class="flex justify-between">
                                    <div class="grow overflow-hidden">
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Number of Orders">Pendapatan Minggu Ini</h5>
                                        <h3 class="text-2xl my-6">Rp<?php echo number_format($week_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
                                        <h3>Total Transaksi : <?php echo $week_stats['total_transaksi'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div> <!-- end p-6-->
                        </div> <!-- end card-->
                    </div>

                    <div class="2xl:col-span-1 lg:col-span-2">
                        <div class="card">
                            <div class="p-6">
                                <div class="flex justify-between">
                                    <div class="grow overflow-hidden">
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Average Revenue">Pendapatan Bulan Ini</h5>
                                        <h3 class="text-2xl my-6">Rp<?php echo number_format($month_stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
                                        <h3>Total Transaksi : <?php echo $month_stats['total_transaksi'] ?? 0; ?></h3>
                                    </div>
                                </div>

                            </div> 
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="p-6">
                        <h3 class="card-title mb-4">Menu Terlaris</h3>
                        <div class="overflow-x-auto">
                                <div class="min-w-full inline-block align-middle">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Menu</th>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Total Terjual</th>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Total Pendapatan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <?php foreach ($popular_menu as $menu): ?>
                                                <tr class="bg-gray-50 dark:bg-gray-900">
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $menu['total_terjual']; ?></td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp<?php echo number_format($menu['total_pendapatan'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>

                


               

            </main>

            <!-- Footer Start -->
            <footer class="footer h-16 flex items-center px-6 bg-white shadow dark:bg-gray-800 mt-auto">
                <div class="flex md:justify-between justify-center w-full gap-4">
                    <div>
                        <script>document.write(new Date().getFullYear())</script> © Attex - <a href="https://coderthemes.com/" target="_blank">Coderthemes</a>
                    </div>
                    <div class="md:flex hidden gap-4 item-center md:justify-end">
                        <a href="javascript: void(0);" class="text-sm leading-5 text-zinc-600 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">About</a>
                        <a href="javascript: void(0);" class="text-sm leading-5 text-zinc-600 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">Support</a>
                        <a href="javascript: void(0);" class="text-sm leading-5 text-zinc-600 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">Contact Us</a>
                    </div>
                </div>
            </footer>
            <!-- Footer End -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>

    <!-- Theme Settings Offcanvas -->
    

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