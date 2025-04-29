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
    $meja_id = (int)$_POST['meja_id'];
    
    // Cek apakah meja sudah memiliki pesanan aktif
    $query_cek_pesanan = "SELECT DISTINCT kode_pesanan FROM pesanan p 
                         LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan 
                         WHERE meja_id = $meja_id AND t.id_transaksi IS NULL";
    $result_cek = mysqli_query($conn, $query_cek_pesanan);
    $pesanan_aktif = mysqli_fetch_assoc($result_cek);
    
    if ($pesanan_aktif) {
        $kode_pesanan = $pesanan_aktif['kode_pesanan'];
        $query_pelanggan = "SELECT id_pelanggan FROM pesanan WHERE kode_pesanan = '$kode_pesanan' LIMIT 1";
        $result_pelanggan = mysqli_query($conn, $query_pelanggan);
        $data_pelanggan = mysqli_fetch_assoc($result_pelanggan);
        $id_pelanggan = $data_pelanggan['id_pelanggan'];
    } else {
        $kode_pesanan = 'PSN-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
        
        $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
        $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
        $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

        $query_pelanggan = "INSERT INTO pelanggan (nama_pelanggan, jenis_kelamin, no_hp, alamat) 
                            VALUES ('$nama_pelanggan', '$jenis_kelamin', '$no_hp', '$alamat')";
        mysqli_query($conn, $query_pelanggan);
        $id_pelanggan = mysqli_insert_id($conn);

        $query_update_meja = "UPDATE meja SET status = 'terpakai' WHERE id = $meja_id";
        mysqli_query($conn, $query_update_meja);
    }

    foreach ($_POST['menu'] as $id_menu => $jumlah) {
        if ($jumlah > 0) {
            // Cek apakah menu sudah ada di pesanan yang sama
            $query_cek_menu = "SELECT id_pesanan, jumlah FROM pesanan 
                               WHERE kode_pesanan = '$kode_pesanan' 
                               AND id_menu = $id_menu";
            $result_cek_menu = mysqli_query($conn, $query_cek_menu);
            
            if ($menu_existing = mysqli_fetch_assoc($result_cek_menu)) {
                // Update jumlah menu yang sudah ada
                $jumlah_baru = $menu_existing['jumlah'] + $jumlah;
                $id_pesanan = $menu_existing['id_pesanan'];
                $query_update = "UPDATE pesanan SET jumlah = $jumlah_baru 
                                 WHERE id_pesanan = $id_pesanan";
                mysqli_query($conn, $query_update);
            } else {
                // Tambah menu baru ke pesanan
                $query_pesanan = "INSERT INTO pesanan (id_menu, kode_pesanan, id_pelanggan, jumlah, id_user, meja_id) 
                                  VALUES ($id_menu, '$kode_pesanan', $id_pelanggan, $jumlah, {$_SESSION['user_id']}, $meja_id)";
                mysqli_query($conn, $query_pesanan);
            }
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
                            <h5 class="text-sm">Waiter</h5>
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
                <div class="overflow-x-auto">
                    <div class="flex gap-10 min-w-full align-middle">
                        <div class="card">
                            <h3 class="card-title p-4">Pilih Menu</h3>

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
                                    <form method="POST">
                                        <div class="text-black flex flex-col gap-4">
                                            <?php 
                                                mysqli_data_seek($result_menu, 0); 
                                                $no = 1;
                                            ?>
                                            <?php while ($menu = mysqli_fetch_assoc($result_menu)): ?>
                                                <tr class="bg-gray-50 dark:bg-gray-900">
                                                    <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                        <?php echo $no++; ?>
                                                    </td>
                                                    <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                        <?php echo htmlspecialchars($menu['nama_menu']); ?>
                                                    </td>
                                                    <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                        Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?>
                                                    </td>
                                                    <td class="text-center px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                        <input type="number" name="menu[<?php echo $menu['id_menu']; ?>]" value="0" min="0" class="form-input w-20">
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </div>
                                </tbody>
                            </table>
                        </div>

                        <div class="card mb-6 w-1/2">
                            <div class="p-6">
                                <h4 class="card-title mb-4">Input Pesanan Baru</h4>

                                <div class="grid lg:grid-cols-1 gap-6">
                                    <div>
                                        <div>
                                            <label class="mb-2" for="meja_id">Pilih Meja</label>
                                            <select class="form-input mb-6" id="meja_id" name="meja_id" required>
                                                <option value="">Pilih Meja</option>
                                                <?php while ($meja = mysqli_fetch_assoc($result_meja)): ?>
                                                    <?php if ($meja['status'] == 'kosong'): ?>
                                                        <option value="<?php echo $meja['id']; ?>">
                                                            Meja <?php echo $meja['no_meja']; ?>
                                                            (<?php echo ucfirst($meja['status']); ?>)
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="mb-2" for="nama_pelanggan">Nama Pelanggan</label>
                                            <input type="text" id="nama_pelanggan" name="nama_pelanggan" required class="form-input">
                                        </div>
                                        <div>
                                            <label class="mb-2" for="jenis_kelamin">Jenis Kelamin</label>
                                            <select class="form-input mb-6" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="laki-laki">Laki-Laki</option>
                                                <option value="perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="mb-2" for="no_hp">Nomor HP</label>
                                            <input type="number" id="no_hp" name="no_hp" required class="form-input">
                                        </div>
                                        <div class="mb-3">
                                            <label class="mb-2" for="alamat">Alamat</label>
                                            <input type="text" id="alamat" name="alamat" required class="form-input">
                                        </div>
                                        <div class="form-group" style="margin-top: 20px;">
                                            <button type="submit" name="tambah_pesanan" class="btn bg-primary text-white">Simpan Pesanan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>