<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login dan memiliki level admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Proses tambah user
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM users WHERE nama = '$nama'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $query = "INSERT INTO users (nama, password, level) VALUES ('$nama', '$password', '$level')";
        mysqli_query($conn, $query);
        header('Location: user.php');
        exit();
    }
}

// Proses edit user
if (isset($_POST['edit'])) {
    $id_user = (int)$_POST['id_user'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    
    // Cek apakah username sudah ada (kecuali untuk user yang sedang diedit)
    $check_query = "SELECT * FROM users WHERE nama = '$nama' AND id_user != $id_user";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $query = "UPDATE users SET nama = '$nama', level = '$level'";
        
        // Update password hanya jika diisi
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query .= ", password = '$password'";
        }
        
        $query .= " WHERE id_user = $id_user";
        mysqli_query($conn, $query);
        header('Location: user.php');
        exit();
    }
}

// Proses hapus user
if (isset($_GET['hapus'])) {
    $id_user = (int)$_GET['hapus'];
    // Mencegah admin menghapus dirinya sendiri
    if ($id_user != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id_user = $id_user";
        mysqli_query($conn, $query);
    }
    header('Location: user.php');
    exit();
}

// Mengambil data user
$query = "SELECT * FROM users ORDER BY nama ASC";
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
    <h4 class="text-slate-900 dark:text-slate-200 text-lg font-medium">Manajemen User</h4>

    <button data-fc-type="modal" data-fc-target="#tambahUser" type="button" class="md:flex hidden items-center gap-2.5 font-semibold">
        <div class="btn bg-primary text-white flex items-center gap-2">
        <i class="ri-add-fill"></i>
        <p>Tambah User</p>
        </div>
    </button>
</div>
<!-- Page Title End -->
<?php if (isset($error)): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<div class="card">
        <div class="p-6">
            <h3 class="card-title mb-4">Data User</h3>

            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">No</th>
                                    <th scope="col" class="px-4 py-4 text-start text-sm font-medium text-gray-500">Username</th>
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
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo $no++; ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-200"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><span class="whitespace-nowrap text-sm btn bg-info text-white dark:text-gray-200 level-<?php echo $row['level']; ?>"><?php echo ucfirst($row['level']); ?></span></td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-start space-x-3">
                                            <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                                                <button data-fc-type="modal" data-fc-target="#editUser" onclick="editUser(<?php echo $row['id_user']; ?>, '<?php echo $row['nama']; ?>', '<?php echo $row['level']; ?>')"><i class="ri-edit-box-line text-xl"></i></button>
                                                <a  data-fc-type="modal" data-fc-target="#confirmModal" data-href="?hapus=<?php echo $row['id_user']; ?>" class="cursor-pointer"><i class="ri-delete-bin-line text-xl"></i></a>
                                            <?php else: ?>
                                                <em>Current User</em>
                                            <?php endif; ?>
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


                        <!-- Modal Tambah User -->
                        <div id="tambahUser" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                            <div class="p-4 overflow-y-auto">
                                <div class="p-9 text-center text-lg">
                                    Tambah User
                                </div>

                                <form class="px-6" method="POST">

                                    <div class="space-y-1 mb-6">
                                        <label for="nama" class="font-semibold text-gray-500">Username</label>
                                        <input class="form-input" type="text" id="nama" name="nama" required>
                                    </div>
                                    <div class="space-y-1 mb-6">
                                        <label for="password" class="font-semibold text-gray-500">Password</label>
                                        <input class="form-input" type="password" id="password" name="password" required>
                                    </div>
                                    <div class="space-y-1 mb-6space-y-1 mb-6"> 
                                        <label for="level" class="font-semibold text-gray-500">Status</label>
                                        <select class="form-input mb-6" id="level" name="level" required>
                                            <option value="admin">Admin</option>
                                            <option value="waiter">Waiter</option>
                                            <option value="kasir">Kasir</option>
                                            <option value="owner">Owner</option>
                                        </select>
                                    </div>
                                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                        <button name="tambah" class="btn bg-primary" type="submit">Simpan</button>
                                        <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn bg-danger" data-fc-dismiss>Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </div>

                        <!-- Modal Edit User -->
                        <div id="editUser" class="hidden w-full h-full fixed top-0 left-0 z-50 transition-all duration-500 hidden overflow-y-auto">
                        <div class="-translate-y-5 fc-modal-open:translate-y-0 fc-modal-open:opacity-100 opacity-0 duration-300 ease-in-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto flex flex-col bg-white shadow-sm rounded dark:bg-gray-800">
                            <div class="p-4 overflow-y-auto">
                                <div class="p-9 text-center text-lg">
                                    Edit User
                                </div>

                                <form class="px-6" method="POST">
                                    <input type="hidden" id="edit_id_user" name="id_user">
                                    <div class="space-y-1 mb-6">
                                        <label for="edit_nama" class="font-semibold text-gray-500">Username</label>
                                        <input class="form-input" type="text" id="edit_nama" name="edit_nama" required>
                                    </div>
                                    <div class="space-y-1 mb-6">
                                        <label for="edit_password" class="font-semibold text-gray-500">Password</label>
                                        <input class="form-input" type="password" id="edit_password" name="password" required>
                                    </div>
                                    <div class="space-y-1 mb-6space-y-1 mb-6"> 
                                        <label for="edit_level" class="font-semibold text-gray-500">Status</label>
                                        <select class="form-input mb-6" id="edit_level" name="edit_level" required>
                                            <option value="admin">Admin</option>
                                            <option value="waiter">Waiter</option>
                                            <option value="kasir">Kasir</option>
                                            <option value="owner">Owner</option>
                                        </select>
                                    </div>
                                    <div class="mb-6 text-center text-white  flex items-center gap-4 justify-center">
                                        <button name="edit" class="btn bg-primary" type="submit">Simpan</button>
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
            </div>
        </div>
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

</body>
</html>