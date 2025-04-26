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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Sistem Kasir Restoran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            padding: 20px;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .nav {
            background-color: #444;
            padding: 10px;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            margin-right: 10px;
        }
        .nav a:hover {
            background-color: #555;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #4CAF50;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        .error {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .level-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .level-admin { background-color: #dc3545; color: white; }
        .level-waiter { background-color: #28a745; color: white; }
        .level-kasir { background-color: #17a2b8; color: white; }
        .level-owner { background-color: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manajemen User</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Dashboard</a>
        <a href="menu.php">Manajemen Menu</a>
        <a href="meja.php">Manajemen Meja</a>
        <a href="user.php">Manajemen User</a>
    </div>

    <div class="container">
        <button onclick="document.getElementById('modalTambah').style.display='block'" class="btn btn-primary">Tambah User</button>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td>
                        <span class="level-badge level-<?php echo $row['level']; ?>">
                            <?php echo ucfirst($row['level']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                            <button onclick="editUser(<?php echo $row['id_user']; ?>, '<?php echo $row['nama']; ?>', '<?php echo $row['level']; ?>')" class="btn btn-warning">Edit</button>
                            <a href="?hapus=<?php echo $row['id_user']; ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="btn btn-danger">Hapus</a>
                        <?php else: ?>
                            <em>Current User</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Modal Tambah User -->
        <div id="modalTambah" class="modal">
            <div class="modal-content">
                <h2>Tambah User</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="nama">Username:</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="level">Level:</label>
                        <select id="level" name="level" required>
                            <option value="admin">Admin</option>
                            <option value="waiter">Waiter</option>
                            <option value="kasir">Kasir</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn btn-danger">Batal</button>
                </form>
            </div>
        </div>

        <!-- Modal Edit User -->
        <div id="modalEdit" class="modal">
            <div class="modal-content">
                <h2>Edit User</h2>
                <form method="POST">
                    <input type="hidden" id="edit_id_user" name="id_user">
                    <div class="form-group">
                        <label for="edit_nama">Username:</label>
                        <input type="text" id="edit_nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Password: (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" id="edit_password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit_level">Level:</label>
                        <select id="edit_level" name="level" required>
                            <option value="admin">Admin</option>
                            <option value="waiter">Waiter</option>
                            <option value="kasir">Kasir</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" class="btn btn-danger">Batal</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editUser(id, nama, level) {
            document.getElementById('edit_id_user').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_level').value = level;
            document.getElementById('modalEdit').style.display = 'block';
        }

        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>