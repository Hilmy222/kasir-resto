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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Sistem Kasir Restoran</title>
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
        .btn-danger {
            background-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-belum {
            background-color: #dc3545;
            color: white;
        }
        .status-sudah {
            background-color: #28a745;
            color: white;
        }
        .detail-pesanan {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Pesanan</h1>
        <a href="../../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="nav">
        <a href="index.php">Input Pesanan</a>
        <a href="pesanan.php">Daftar Pesanan</a>
    </div>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Meja    </th>
                    <th>Detail Pesanan</th>
                    <th>Total</th>
                    <th>Waktu Pesan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td>Meja titid  <?php echo $row['no_meja']; ?></td>
                    <td class="detail-pesanan"><?php echo htmlspecialchars($row['detail_pesanan']); ?></td>
                    <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>
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

    <!-- Modal Tambah Item -->
    <div id="addItemModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Tambah Item Pesanan</h2>
            <form id="addItemForm" method="POST">
                <input type="hidden" name="kode_pesanan" id="kode_pesanan">
                <input type="hidden" name="tambah_item" value="1">
                
                <div class="form-group">
                    <label>Menu:</label>
                    <select name="id_menu" required class="form-control">
                        <?php
                        $menu_query = "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu";
                        $menu_result = mysqli_query($conn, $menu_query);
                        while ($menu = mysqli_fetch_assoc($menu_result)) {
                            echo "<option value='". $menu['id_menu'] ."'>". htmlspecialchars($menu['nama_menu']) ." - Rp ". number_format($menu['harga'], 0, ',', '.') ."</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah:</label>
                    <input type="number" name="jumlah" required min="1" class="form-control">
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-danger" onclick="closeAddItemModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kurangi Item -->
    <div id="kurangiItemModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Kurangi Item Pesanan</h2>
            <form id="kurangiItemForm" method="POST">
                <input type="hidden" name="kode_pesanan" id="kurangi_kode_pesanan">
                <input type="hidden" name="kurangi_item" value="1">
                
                <div class="form-group">
                    <label>Pilih Item:</label>
                    <select name="id_menu" required class="form-control" id="kurangiItemSelect">
                    </select>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-danger" onclick="closeKurangiItemModal()">Batal</button>
                    <button type="submit" class="btn btn-warning">Kurangi</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .btn-primary {
        background-color: #007bff;
        margin-right: 5px;
    }
    .btn-warning {
        background-color: #ffc107;
        color: #000;
    }
    </style>

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
</body>
</html>