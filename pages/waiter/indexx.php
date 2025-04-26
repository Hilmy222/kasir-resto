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
                            <span class="menu-text"> Input Pesanan </span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="pesanan.php" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-message-3-line"></i>
                            </span>
                            <span class="menu-text"> Daftar Pesanan </span>
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

                    <div class="card">
                            <div class="p-6">
                                <h3 class="card-title mb-4">Pilih Menu</h3>

                                <div class="overflow-x-auto">
                                    <div class="min-w-full inline-block align-middle">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="px-4 py-4 text-center text-sm font-medium text-gray-500">No</th>
                                            <th scope="col" class="px-4 py-4 text-center text-sm font-medium text-gray-500">Nama</th>
                                            <th scope="col" class="px-4 py-4 text-center text-sm font-medium text-gray-500">Harga</th>
                                            <th scope="col" class="px-4 py-4 text-center text-sm font-medium text-gray-500">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        
                                        <div class="text-black flex flex-col gap-4">
                                            <?php mysqli_data_seek($result_menu, 0); ?>
                                            <?php while ($menu = mysqli_fetch_assoc($result_menu)): ?>
                                                <tr class="bg-gray-50 dark:bg-gray-900">
                                                        <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                                                        <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">(Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?>)</td>
                                                        <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                        <button type="button"
                                                            onclick="decreaseQty('<?php echo $menu['id_menu']; ?>')"
                                                            class="px-3 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300">
                                                            −
                                                        </button>
                                                        <input type="number"
                                                            id="qty-<?php echo $menu['id_menu']; ?>"
                                                            name="menu[<?php echo $menu['id_menu']; ?>]"
                                                            value="0"
                                                            min="0"
                                                            class="w-20  text-center text-sm border-x border-gray-300 focus:outline-none" />
                                                        <button type="button"
                                                            onclick="increaseQty('<?php echo $menu['id_menu']; ?>')"
                                                            class="px-3 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300">
                                                            +
                                                        </button>
                                                    </td>
                                              </tr>
                                            <?php endwhile; ?>
                                        </div>
                                    </tbody>
                                    </table>
                                        <div class="form-group" style="margin-top: 20px;">
                                            <button type="submit" name="tambah_pesanan" class="btn bg-primary text-white">Simpan Pesanan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>

            

            
                        <div id="tambahMenu" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 overflow-y-auto">
                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                            <div class="p-4 overflow-y-auto">
                                <div class="p-9 text-center text-lg">
                                    Tambah Menu
                                </div>

                                <form class="px-6" method="POST">

                                    <div class="space-y-1 mb-6">
                                        <label for="nama_menu" class="font-semibold text-gray-500">Nama Menu</label>
                                        <input class="form-input" type="text" id="nama_menu" name="nama_menu" required>
                                    </div>
                                    <div class="space-y-1 mb-6">
                                        <label for="harga" class="font-semibold text-gray-500">Harga</label>
                                        <input class="form-input" type="number" id="harga" name="harga" required>
                                    </div>
                                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                        <button name="tambah" class="btn bg-primary" type="submit">Simpan</button>
                                        <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </div>

                        <div id="editMenu" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 overflow-y-auto">
                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                            <div class="p-4 overflow-y-auto">
                                <div class="p-9 text-center text-lg">
                                    Edit Menu
                                </div>

                                <form class="px-6" method="POST">
                                    <input type="hidden" id="edit_id_menu" name="id_menu">
                                    <div class="space-y-1 mb-6">
                                        <label for="edit_nama_menu" class="font-semibold text-gray-500">Nama Menu</label>
                                        <input class="form-input" type="text" id="edit_nama_menu" name="nama_menu" required>
                                    </div>
                                    <div class="space-y-1 mb-6">
                                        <label for="edit_harga" class="font-semibold text-gray-500">Harga</label>
                                        <input class="form-input" type="number" id="edit_harga" name="harga" required>
                                    </div>
                                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                        <button name="edit" class="btn bg-primary" type="submit">Simpan</button>
                                        <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </div>
                        <div id="confirmModal" class="w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                            <div class="sm:max-w-xs -translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:w-full m-3 sm:mx-auto flex flex-col bg-danger shadow-sm rounded">
                                <div class="p-9 overflow-y-auto">
                                    <div class="text-center text-white">
                                        <i class="ri-close-circle-line text-4xl"></i>
                                        <h4 class="text-xl font-medium mt-3 mb-2.5">Apakah Kamu Yakin Ingin Menghapus?</h4>
                                        <p class="mt-6 mb-4">Data yang sudah dihapus tidak bisa dikembalikan.</p>
                                        <div class="flex justify-center gap-4">
                                            <button id="confirmDelete" type="button" class="btn bg-light text-danger my-2" data-bs-dismiss="modal">Ya, Hapus</button>
                                            <button type="button" class="btn bg-light text-black  my-2" data-fc-dismiss>Batal</button>
                                        </div>                                                
                                    </div>
                                </div>
                            </div>
                        </div>

            </main> 
        </div> 
    </div>
    <script>
function increaseQty(id) {
    const input = document.getElementById('qty-' + id);
    input.value = parseInt(input.value || 0) + 1;
}

function decreaseQty(id) {
    const input = document.getElementById('qty-' + id);
    if (parseInt(input.value) > 0) {
        input.value = parseInt(input.value) - 1;
    }
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