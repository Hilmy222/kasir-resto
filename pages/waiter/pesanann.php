<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level waiter
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'waiter') {
    header('Location: ../../index.php');
    exit();
}

// Proses pengurangan item pesanan
if (isset($_POST['kurangi_item'])) {
    $kode_pesanan = $_POST['kode_pesanan'];
    $id_menu = $_POST['id_menu'];
    
    // Ambil jumlah item saat ini
    $query_jumlah = "SELECT id_pesanan, jumlah FROM pesanan WHERE kode_pesanan = ? AND id_menu = ? LIMIT 1";
    $stmt_jumlah = mysqli_prepare($conn, $query_jumlah);
    mysqli_stmt_bind_param($stmt_jumlah, 'si', $kode_pesanan, $id_menu);
    mysqli_stmt_execute($stmt_jumlah);
    $result_jumlah = mysqli_stmt_get_result($stmt_jumlah);
    $data_jumlah = mysqli_fetch_assoc($result_jumlah);
    
    if ($data_jumlah) {
        $jumlah_baru = $data_jumlah['jumlah'] - 1;
        $id_pesanan = $data_jumlah['id_pesanan'];
        
        if ($jumlah_baru > 0) {
            // Update jumlah item
            $query_update = "UPDATE pesanan SET jumlah = ? WHERE id_pesanan = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, 'ii', $jumlah_baru, $id_pesanan);
            
            if (mysqli_stmt_execute($stmt_update)) {
                echo "<script>alert('Item berhasil dikurangi!');</script>";
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            // Hapus item jika jumlah 0
            $query_delete = "DELETE FROM pesanan WHERE id_pesanan = ?";
            $stmt_delete = mysqli_prepare($conn, $query_delete);
            mysqli_stmt_bind_param($stmt_delete, 'i', $id_pesanan);
            
            if (mysqli_stmt_execute($stmt_delete)) {
                echo "<script>alert('Item berhasil dihapus!');</script>";
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
            mysqli_stmt_close($stmt_delete);
        }
    }
    mysqli_stmt_close($stmt_jumlah);
}

// Proses tambah pesanan baru atau tambah item
if (isset($_POST['tambah']) || isset($_POST['tambah_item'])) {
    $id_menu = $_POST['id_menu'];
    $id_pelanggan = isset($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : null;
    $jumlah = $_POST['jumlah'];
    $id_user = $_SESSION['user_id'];
    $meja_id = isset($_POST['meja_id']) ? $_POST['meja_id'] : null;
    
    if (isset($_POST['tambah_item'])) {
        // Menggunakan kode pesanan yang sudah ada untuk tambah item
        $kode_pesanan = $_POST['kode_pesanan'];
        
        // Ambil data pelanggan dan meja dari pesanan yang sudah ada
        $get_order_data = "SELECT id_pelanggan, meja_id FROM pesanan WHERE kode_pesanan = ? LIMIT 1";
        $stmt_data = mysqli_prepare($conn, $get_order_data);
        mysqli_stmt_bind_param($stmt_data, 's', $kode_pesanan);
        mysqli_stmt_execute($stmt_data);
        $result_data = mysqli_stmt_get_result($stmt_data);
        $order_data = mysqli_fetch_assoc($result_data);
        mysqli_stmt_close($stmt_data);
        
        $id_pelanggan = $order_data['id_pelanggan'];
        $meja_id = $order_data['meja_id'];
    } else {
        // Membuat kode pesanan baru
        $kode_pesanan = 'PSN-' . date('YmdHis');
    }

    $query = "CALL CreatePesanan(?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issiii', $id_menu, $kode_pesanan, $id_pelanggan, $jumlah, $id_user, $meja_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Pesanan berhasil ditambahkan!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
    mysqli_stmt_close($stmt);
}

// Mengambil data pesanan
$query = "SELECT 
            p.kode_pesanan,
            pl.nama_pelanggan,
            m.no_meja,
            GROUP_CONCAT(CONCAT(mn.nama_menu, ' (', p.jumlah, ' x ', mn.harga, ')') SEPARATOR ', ') as detail_pesanan,
            GROUP_CONCAT(CONCAT(mn.id_menu, ':', p.jumlah) SEPARATOR ',') as item_details,
            SUM(p.jumlah * mn.harga) as total_harga,
            MIN(p.created_at) as created_at,
            MAX(CASE WHEN t.id_transaksi IS NULL THEN 'Belum Dibayar' ELSE 'Sudah Dibayar' END) as status_pembayaran
          FROM pesanan p
          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
          JOIN meja m ON p.meja_id = m.id
          JOIN menu mn ON p.id_menu = mn.id_menu
          LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
          GROUP BY p.kode_pesanan, pl.nama_pelanggan, m.no_meja
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
                            <span class="menu-text"> Riwayat Transaksi </span>
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
                        <h3 class="card-title mb-4">Data Meja</h3>

                        <div class="w-full overflow-x-auto">
                            <div class="min-w-full inline-block align-middle">
                                <div class="overflow-hidden">
                                    <table class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Kode Pesanan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Pelanggan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Meja </th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Detail Pesanan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Total</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Waktu Pesanan</th>
                                                <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Status</th>
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
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><span>Meja</span><?php echo $row['no_meja']; ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['detail_pesanan']); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><span>Rp</span><?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td class="px-4 py-4">
                                                <?php if ($row['status_pembayaran'] == 'Belum Dibayar'): ?>
                                                    <button class="btn btn-primary" onclick="showAddItemModal('<?php echo htmlspecialchars($row['kode_pesanan']); ?>')">Tambah Item</button>
                                                    <button class="btn btn-warning" onclick="showKurangiItemModal('<?php echo htmlspecialchars($row['kode_pesanan']); ?>', '<?php echo htmlspecialchars($row['item_details']); ?>')">Kurangi Item</button>
                                                <?php else: ?>
                                                    <span class="status-badge status-sudah">
                                                        <?php echo $row['status_pembayaran']; ?>
                                                    </span>
                                                <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card -->




            </main>

        </div> 
    </div>
    <script>
    function showAddItemModal(kodePesanan) {
        document.getElementById('addItemModal').style.display = 'flex';
        document.getElementById('kode_pesanan').value = kodePesanan;
    }

    function closeAddItemModal() {
        document.getElementById('addItemModal').style.display = 'none';
    }

    function showKurangiItemModal(kodePesanan, itemDetails) {
        document.getElementById('kurangiItemModal').style.display = 'flex';
        document.getElementById('kurangi_kode_pesanan').value = kodePesanan;
        
        // Parse item details and populate select
        const items = itemDetails.split(',');
        const select = document.getElementById('kurangiItemSelect');
        select.innerHTML = '';
        
        const menuData = {};
        <?php
        $menu_query = "SELECT id_menu, nama_menu FROM menu";
        $menu_result = mysqli_query($conn, $menu_query);
        while ($menu = mysqli_fetch_assoc($menu_result)) {
            echo "menuData[" . $menu['id_menu'] . "] = '" . addslashes($menu['nama_menu']) . "';";
        }
        ?>
        
        items.forEach(item => {
            const [menuId, quantity] = item.split(':');
            const menuName = menuData[menuId];
            if (menuName) {
                const option = document.createElement('option');
                option.value = menuId;
                option.textContent = `${menuName} (${quantity})`;
                select.appendChild(option);
            }
        });
    }

    function closeKurangiItemModal() {
        document.getElementById('kurangiItemModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const addItemModal = document.getElementById('addItemModal');
        const kurangiItemModal = document.getElementById('kurangiItemModal');
        if (event.target == addItemModal) {
            closeAddItemModal();
        } else if (event.target == kurangiItemModal) {
            closeKurangiItemModal();
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