<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Proses tambah meja
if (isset($_POST['tambah'])) {
    $no_meja = (int)$_POST['no_meja'];
    
    // Cek apakah nomor meja sudah ada
    $check_query = "SELECT * FROM meja WHERE no_meja = $no_meja";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Nomor meja sudah ada!";
    } else {
        $query = "INSERT INTO meja (no_meja) VALUES ($no_meja)";
        mysqli_query($conn, $query);
        header('Location: meja.php');
        exit();
    }
}

// Proses edit meja
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $no_meja = (int)$_POST['no_meja'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Cek apakah nomor meja sudah ada (kecuali untuk meja yang sedang diedit)
    $check_query = "SELECT * FROM meja WHERE no_meja = $no_meja AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Nomor meja sudah ada!";
    } else {
        $query = "UPDATE meja SET no_meja = $no_meja, status = '$status' WHERE id = $id";
        mysqli_query($conn, $query);
        header('Location: meja.php');
        exit();
    }
}

// Proses hapus meja
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = "DELETE FROM meja WHERE id = $id";
    mysqli_query($conn, $query);
    header('Location: meja.php');
    exit();
}

// Mengambil data meja
$query = "SELECT * FROM meja ORDER BY no_meja ASC";
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
                    <h4 class="text-slate-900 dark:text-slate-200 text-lg font-medium">Manajemen Meja</h4>

                    <button data-fc-type="modal" data-fc-target="#signupModal" type="button" class="md:flex hidden items-center gap-2.5 font-semibold">
                        <div class="btn bg-primary text-white flex items-center gap-2">
                        <i class="ri-add-fill"></i>
                        <p>Tambah Meja</p>
                        </div>
                    </button>
                </div>
                <!-- Page Title End -->
                <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="card">
                        <div class="p-6">
                            <h3 class="card-title mb-4">Data Meja</h3>

                            <div class="overflow-x-auto">
                                <div class="min-w-full inline-block align-middle">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No Meja</th>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Status</th>
                                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <?php 
                                            $no = 1;
                                            while ($row = mysqli_fetch_assoc($result)): 
                                            ?>
                                                <tr class="bg-gray-50 dark:bg-gray-900">
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $row['no_meja']; ?></td>
                                                    <td><span class="whitespace-nowrap text-sm btn bg-danger text-white dark:text-gray-200"><?php echo ucfirst($row['status']); ?></span></td>
                                                    <td class="px-4 py-4">
                                                        <div class="flex items-center justify-start space-x-3">
                                                            <button data-fc-type="modal" data-fc-target="#signupEdit" onclick="editMeja(<?php echo $row['id']; ?>, <?php echo $row['no_meja']; ?>, '<?php echo $row['status']; ?>')"><i class="ri-edit-box-line text-xl"></i></button>
                                                            <a  data-fc-type="modal" data-fc-target="#confirmModal" data-href="?hapus=<?php echo $row['id']; ?>" class="cursor-pointer"><i class="ri-delete-bin-line text-xl"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                        
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


                                        <!-- Modal Tambah Meja -->
                                        <div id="signupModal" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                                            <div class="p-4 overflow-y-auto">
                                                <div class="p-9 text-center text-lg">
                                                    Tambah Meja
                                                </div>
    
                                                <form class="px-6" method="POST" action="meja.php">
    
                                                    <div class="space-y-1 mb-6">
                                                        <label for="no_meja" class="font-semibold text-gray-500">Nomor Meja</label>
                                                        <input class="form-input" type="number" id="no_meja" name="no_meja" required min="1" placeholder="Cth. 1">
                                                    </div>
                                                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                                        <button name="tambah" class="btn bg-primary" type="submit">Simpan</button>
                                                        <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        </div>
                                        
                                        <div id="signupEdit" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                                            <div class="p-4 overflow-y-auto">
                                                <div class="p-9 text-center text-lg">
                                                    Edit Meja
                                                </div>
    
                                                <form class="px-6" method="POST" action="meja.php">
                                                <input type="hidden" id="edit_id" name="id">
                                                    <div class="space-y-1 mb-6">
                                                        <label for="edit_no_meja" class="font-semibold text-gray-500">Nomor Meja</label>
                                                        <input class="form-input" type="number" id="edit_no_meja" name="no_meja" required min="1" placeholder="Cth. 1">
                                                    </div>
                                                    <div>
                                                        <label class="mb-2" for="edit_status">Status</label>
                                                        <select class="form-input mb-6" id="edit_status" name="status" required>
                                                            <option value="kosong">Kosong</option>
                                                            <option value="terpakai">Terpakai</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-6 text-center text-white flex items-center gap-4 justify-center">
                                                        <button name="edit" class="btn bg-primary" type="submit">Simpan</button>
                                                        <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end card -->


               

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
        function modalTambah(){
            document.getElementById('modalTambah').classList.remove('hidden');
        }
        function editMeja(id, noMeja, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_no_meja').value = noMeja;
            document.getElementById('edit_status').value = status;
            document.getElementById('modalEdit').style.display = 'block';
        }

        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
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

  // Tutup modal saat klik "Batal"
  document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('confirmModal').classList.add('hidden');
    });
  });
</script>
                                           
</body>

</html>