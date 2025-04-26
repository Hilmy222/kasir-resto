<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Mengambil data menu untuk ditampilkan di dashboard
$query_menu = "SELECT COUNT(*) as total_menu FROM menu";
$result_menu = mysqli_query($conn, $query_menu);
$total_menu = mysqli_fetch_assoc($result_menu)['total_menu'];

// Mengambil data meja
$query_meja = "SELECT COUNT(*) as total_meja FROM meja";
$result_meja = mysqli_query($conn, $query_meja);
$total_meja = mysqli_fetch_assoc($result_meja)['total_meja'];

// Mengambil data user
$query_user = "SELECT COUNT(*) as total_user FROM users";
$result_user = mysqli_query($conn, $query_user);
$total_user = mysqli_fetch_assoc($result_user)['total_user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard | Attex - Responsive Tailwind CSS 3 Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc., Tailwind, TailwindCSS, Tailwind CSS 3" name="description">
    <meta content="coderthemes" name="author">

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- plugin css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>
</head>

<body>

    <div class="flex wrapper">

        <!-- Sidenav Menu -->
        <div class="app-menu">

            <!-- App Logo -->
            <a href="index.html" class="logo-box">
                <!-- Light Logo -->
                <div class="logo-light">
                    <img src="assets/images/logo.png" class="logo-lg h-[22px]" alt="Light logo">
                    <img src="assets/images/logo-sm.png" class="logo-sm h-[22px]" alt="Small logo">
                </div>

                <!-- Dark Logo -->
                <div class="logo-dark">
                    <img src="assets/images/logo-dark.png" class="logo-lg h-[22px]" alt="Dark logo">
                    <img src="assets/images/logo-sm.png" class="logo-sm h-[22px]" alt="Small logo">
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
                        <a href="javascript:void(0)" data-fc-type="collapse" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-home-4-line"></i>
                            </span>
                            <span class="menu-text"> Dashboard </span>
                            <span class="badge bg-success rounded-full">2</span>
                        </a>

                        <ul class="sub-menu hidden">
                            <li class="menu-item">
                                <a href="dashboard-analytics.html" class="menu-link">
                                    <span class="menu-text">Analytics</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="index.html" class="menu-link">
                                    <span class="menu-text">Ecommerce</span>
                                </a>
                            </li>
                        </ul>
                    </li>


                    <li class="menu-item">
                        <a href="menu.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-calendar-event-line"></i>
                            </span>
                            <span class="menu-text"> Menu </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="meja.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-message-3-line"></i>
                            </span>
                            <span class="menu-text"> Meja </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="user.php" data-fc-type="collapse" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-mail-line"></i>
                            </span>
                            <span class="menu-text"> User </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="javascript:void(0)" data-fc-type="collapse" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-mail-line"></i>
                            </span>
                            <span class="menu-text"> Pesanan </span>
                        </a>
                    </li>


                    <li class="menu-item">
                        <a href="apps-kanban.html" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-list-check-3"></i>
                            </span>
                            <span class="menu-text">Generate Laporan</span>
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
                        <img src="assets/images/users/avatar-1.jpg" alt="user-image" class="rounded-full h-8">
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

                    <div class="md:flex hidden items-center gap-2.5 font-semibold">
                        <div class="flex items-center gap-2">
                            <a href="#" class="text-sm font-medium text-slate-700 dark:text-slate-400">Attex</a>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="ri-arrow-right-s-line text-base text-slate-400 rtl:rotate-180"></i>
                            <a href="#" class="text-sm font-medium text-slate-700 dark:text-slate-400">Menu</a>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="ri-arrow-right-s-line text-base text-slate-400 rtl:rotate-180"></i>
                            <a href="#" class="text-sm font-medium text-slate-700 dark:text-slate-400" aria-current="page">Dashboard</a>
                        </div>
                    </div>
                </div>
                <!-- Page Title End -->

                <div class="grid 2xl:grid-cols-5 lg:grid-cols-6 md:grid-cols-2 gap-6 mb-6">
                    <div class="2xl:col-span-1 lg:col-span-2">
                        <div class="card">
                            <div class="p-6">
                                <div class="flex justify-between">
                                    <div class="grow overflow-hidden">
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Number of Customers">Total Menu</h5>
                                        <h3 class="text-2xl my-6"><?php echo $total_menu; ?></h3>
                                    </div>
                                    <div class="shrink">
                                        <div id="widget-customers" class="apex-charts" data-colors="#47ad77,#e3e9ee"></div>
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
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Number of Orders">Total Meja</h5>
                                        <h3 class="text-2xl my-6"><?php echo $total_meja; ?></h3>
                                    </div>
                                    <div id="widget-orders" class="apex-charts" data-colors="#3e60d5,#e3e9ee"></div>
                                </div>
                            </div> <!-- end p-6-->
                        </div> <!-- end card-->
                    </div>

                    <div class="2xl:col-span-1 lg:col-span-2">
                        <div class="card">
                            <div class="p-6">
                                <div class="flex justify-between">
                                    <div class="grow overflow-hidden">
                                        <h5 class="text-base/3 text-gray-400 font-normal mt-0" title="Average Revenue">Total user</h5>
                                        <h3 class="text-2xl my-6"><?php echo $total_user; ?></h3>
                                    </div>
                                    <div id="widget-revenue" class="apex-charts" data-colors="#16a7e9,#e3e9ee"></div>
                                </div>

                            </div> <!-- end p-6-->
                        </div> <!-- end card-->
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
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/lucide/umd/lucide.min.js"></script>
    <script src="assets/libs/@frostui/tailwindcss/frostui.js"></script>

    <!-- App Js -->
    <script src="assets/js/app.js"></script>

    <!-- Apex Charts js -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map Js -->
    <script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
    <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>
    <script src="assets/libs/jsvectormap/maps/world.js"></script>

    <!-- Dashboard App js -->
    <script src="assets/js/pages/dashboard.js"></script>

</body>

</html>