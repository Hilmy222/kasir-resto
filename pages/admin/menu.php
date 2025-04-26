<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Proses tambah menu
if (isset($_POST['tambah'])) {
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];

    $query = "INSERT INTO menu (nama_menu, harga) VALUES ('$nama_menu', $harga)";
    mysqli_query($conn, $query);
    header('Location: menu.php');
    exit();
}

// Proses edit menu
if (isset($_POST['edit'])) {
    $id_menu = (int)$_POST['id_menu'];
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];

    $query = "UPDATE menu SET nama_menu = '$nama_menu', harga = $harga WHERE id_menu = $id_menu";
    mysqli_query($conn, $query);
    header('Location: menu.php');
    exit();
}

// Proses hapus menu
if (isset($_GET['hapus'])) {
    $id_menu = (int)$_GET['hapus'];
    $query = "DELETE FROM menu WHERE id_menu = $id_menu";
    mysqli_query($conn, $query);
    header('Location: menu.php');
    exit();
}

// Mengambil data menu
$query = "SELECT * FROM menu ORDER BY nama_menu ASC";
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
                        <a href="index.php" data-fc-type="collapse" class="menu-link">
                            <span class="menu-icon">
                                <i class="ri-home-4-line"></i>
                            </span>
                            <span class="menu-text"> Dashboard </span>
                        </a>
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
                        <h4 class="text-slate-900 dark:text-slate-200 text-lg font-medium">Manajemen Menu</h4>

                        <button data-fc-type="modal" data-fc-target="#tambahMenu" type="button" class="md:flex hidden items-center gap-2.5 font-semibold">
                            <div class="btn bg-primary text-white flex items-center gap-2">
                            <i class="ri-add-fill"></i>
                            <p>Tambah Menu</p>
                            </div>
                        </button>
                    </div>

                    <div class="card">
                            <div class="p-6">
                                <h3 class="card-title mb-4">Data Meja</h3>

                                <div class="overflow-x-auto">
                                    <div class="min-w-full inline-block align-middle">
                                        <div class="overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No</th>
                                                        <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Nama Menu</th>
                                                        <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Harga</th>
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
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                                        <td class="px-4 py-4">
                                                            <div class="flex items-center justify-start space-x-3">
                                                                <button data-fc-type="modal" data-fc-target="#editMenu" onclick="editMenu(<?php echo $row['id_menu']; ?>, '<?php echo $row['nama_menu']; ?>', <?php echo $row['harga']; ?>)"><i class="ri-edit-box-line text-xl"></i></button>
                                                                <a  data-fc-type="modal" data-fc-target="#confirmModal" data-href="?hapus=<?php echo $row['id_menu']; ?>" class="cursor-pointer"><i class="ri-delete-bin-line text-xl"></i></a>
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

    <script>
        
        function editMenu(id, nama, harga) {
            document.getElementById('edit_id_menu').value = id;
            document.getElementById('edit_nama_menu').value = nama;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('modalEdit').style.display = 'block';
        }

        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
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
    <script>
  let deleteLink = '';

  // Simpan href dari tombol delete yang ditekan
  document.querySelectorAll('a[data-fc-type="modal"]').forEach(link => {
    link.addEventListener('click', function () {
      deleteLink = this.getAttribute('data-href');
    });
  });

  // Saat tombol konfirmasi ditekan, redirect ke URL yang disimpan
  document.getElementById('confirmDelete').addEventListener('click', function () {
    if (deleteLink) {
      window.location.href = deleteLink;
    }
  });

</script>
</body>
</html>