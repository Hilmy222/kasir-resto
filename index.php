<?php
session_start();
require_once 'config/database.php';

// Redirect ke halaman sesuai level user jika sudah login
if (isset($_SESSION['user_level'])) {
    switch ($_SESSION['user_level']) {
        case 'admin':
            header('Location: pages/admin/');
            break;
        case 'waiter':
            header('Location: pages/waiter/');
            break;
        case 'kasir':
            header('Location: pages/kasir/');
            break;
        case 'owner':
            header('Location: pages/owner/');
            break;
    }
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE nama = '$nama' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_level'] = $user['level'];

            header('Location: index.php');
            exit();
        }
    }
    $error = 'Username atau password salah';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login | Attex - Responsive Tailwind CSS 3 Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc., Tailwind, TailwindCSS, Tailwind CSS 3" name="description">
    <meta content="coderthemes" name="author">

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>
</head>

<body class="relative flex flex-col">

    <!-- Svg Background -->
    <div class="absolute inset-0 h-screen w-screen">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 1920 1028">
            <g mask="url(&quot;#SvgjsMask1166&quot;)" fill="none">
                <use xlink:href="#SvgjsSymbol1173" x="0" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="0" y="720"></use>
                <use xlink:href="#SvgjsSymbol1173" x="720" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="720" y="720"></use>
                <use xlink:href="#SvgjsSymbol1173" x="1440" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="1440" y="720"></use>
            </g>
            <defs>
                <mask id="SvgjsMask1166">
                    <rect width="1920" height="1028" fill="#ffffff"></rect>
                </mask>
                <path d="M-1 0 a1 1 0 1 0 2 0 a1 1 0 1 0 -2 0z" id="SvgjsPath1171"></path>
                <path d="M-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0z" id="SvgjsPath1170"></path>
                <path d="M-5 0 a5 5 0 1 0 10 0 a5 5 0 1 0 -10 0z" id="SvgjsPath1169"></path>
                <path d="M2 -2 L-2 2z" id="SvgjsPath1168"></path>
                <path d="M6 -6 L-6 6z" id="SvgjsPath1167"></path>
                <path d="M30 -30 L-30 30z" id="SvgjsPath1172"></path>
            </defs>
            <symbol id="SvgjsSymbol1173">
                <use xlink:href="#SvgjsPath1167" x="30" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="30" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="30" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="30" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="90" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="90" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="90" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="150" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="150" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="150" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="150" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="210" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="210" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="210" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="210" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="270" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="270" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="270" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="270" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="270" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1172" x="270" y="630" class="stroke-primary/20" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1171" x="270" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="330" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="330" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="330" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="390" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="390" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="390" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="390" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="390" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="450" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="450" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="450" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="450" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="450" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="450" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1172" x="450" y="630" class="stroke-primary/20" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1168" x="450" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="510" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1172" x="510" y="150" class="stroke-primary/20" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1171" x="510" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="510" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="570" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="570" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="630" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="630" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="630" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="690" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="690" y="30" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="90" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1170" x="690" y="150" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="210" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="270" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="330" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="390" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="450" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="690" y="510" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1169" x="690" y="570" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1168" x="690" y="630" class="stroke-primary/20"></use>
                <use xlink:href="#SvgjsPath1171" x="690" y="690" class="stroke-primary/20"></use>
            </symbol>
        </svg>
    </div>

    <!-- Login Card -->
    <div class="relative flex flex-col items-center justify-center h-screen">
        <div class="flex justify-center">
            <div class="max-w-md px-4 mx-auto">
                <div class="card overflow-hidden">

                    <!-- Logo -->
                    <div class="p-9 bg-primary">
                        <a href="index.html" class="flex justify-center">
                            <img src="assets/images/logo.png" alt="logo" class="h-6 block dark:hidden">
                            <img src="assets/images/logo-dark.png" alt="logo" class="h-6 hidden dark:block">
                        </a>
                    </div>

                    <div class="p-9">
                        <div class="text-center mx-auto w-3/4">
                            <h4 class="text-dark/70 text-center text-lg font-bold dark:text-light/80 mb-2">Login</h4>
                            <p class="text-gray-400 mb-9">Masukkan Username dan Password anda!.</p>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">

                            <div class="mb-6 space-y-2">
                                <label for="nama" class="font-semibold text-gray-500">Username</label>
                                <input class="form-input" type="text" id="nama" name="nama" required placeholder="Masukkan Username Anda">
                            </div>

                                <div class="mb-6 space-y-2">
                                    <label for="password" class="font-semibold text-gray-500">Password</label>
                                    <input type="password" id="password" name="password" class="form-input rounded-e-none" placeholder="Enter your password">
                                </div>

                            <div class="text-center mb-6">
                                <button type="submit" class="btn bg-primary text-white" type="submit"> Log In </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="absolute bottom-0 inset-x-0">
        <p class="font-medium text-center p-6">
            <script>document.write(new Date().getFullYear())</script> © Attex - Coderthemes.com
        </p>
    </footer>

    <!-- Plugin Js -->
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/lucide/umd/lucide.min.js"></script>
    <script src="assets/libs/@frostui/tailwindcss/frostui.js"></script>

    <!-- App Js -->
    <script src="assets/js/app.js"></script>

</body>

</html>