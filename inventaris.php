<?php
session_start();
$today = date('Y-m-d');
// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Batasi akses hanya untuk admin, kepala ruangan, dan staff
$allowed_roles = ['admin', 'kepala', 'staff'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    // Jika role keuangan, redirect ke daftar pengajuan
    if ($_SESSION['role'] === 'keuangan') {
        header("Location: daftar_pengajuan.php");
        exit();
    } else {
        // Role lain, redirect ke dashboard
        header("Location: dashboard.php");
        exit();
    }
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$success = $error = '';

// Proses penyerahan barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['penyerahan'])) {
    $inventaris_id = $_POST['inventaris_id'];
    $divisi_tujuan = $_POST['divisi_tujuan'];
    $tanggal_penyerahan = $_POST['tanggal_penyerahan'];
    $catatan = $_POST['catatan'];
    $jumlah_serah = $_POST['jumlah_serah'];
    
    // Cek stok tersedia
    $sql = "SELECT stok FROM inventaris WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inventaris_id);
    $stmt->execute();
    $result_stok = $stmt->get_result();
    $row_stok = $result_stok->fetch_assoc();
    
    if ($jumlah_serah > $row_stok['stok']) {
        $error = "Jumlah yang diserahkan melebihi stok tersedia!";
    } else {
        // Mulai transaction
        $conn->begin_transaction();
        
        try {
            // Update stok inventaris
            $stok_baru = $row_stok['stok'] - $jumlah_serah;
            $status_baru = ($stok_baru > 0) ? 'Tersedia' : 'Diserahkan';
            
            $sql = "UPDATE inventaris SET stok = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $stok_baru, $status_baru, $inventaris_id);
            $stmt->execute();
            
            // Insert ke tabel penyerahan_barang
            $sql = "INSERT INTO penyerahan_barang (inventaris_id, divisi_tujuan, jumlah_serah, tanggal_penyerahan, catatan_penyerahan) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiss", $inventaris_id, $divisi_tujuan, $jumlah_serah, $tanggal_penyerahan, $catatan);
            $stmt->execute();
            
            // Update atau insert ke tabel distribusi_divisi
            $sql = "INSERT INTO distribusi_divisi (inventaris_id, divisi, stok_divisi) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE stok_divisi = stok_divisi + ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $inventaris_id, $divisi_tujuan, $jumlah_serah, $jumlah_serah);
            $stmt->execute();
            
            $conn->commit();
            $success = "Penyerahan barang berhasil dilakukan! Stok tersisa: " . $stok_baru . " unit";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal melakukan penyerahan: " . $e->getMessage();
        }
    }
}

// Handle input kerusakan
if (isset($_POST['input_kerusakan'])) {
    $inventaris_id = $_POST['inventaris_id'];
    $tanggal_kerusakan = $_POST['tanggal_kerusakan'];
    $jenis_kerusakan = $_POST['jenis_kerusakan'];
    $deskripsi_kerusakan = $_POST['deskripsi_kerusakan'];
    $divisi_target = $_POST['divisi_target'];
    
    // Cek stok di divisi target
    $sql = "SELECT stok_divisi FROM distribusi_divisi WHERE inventaris_id = ? AND divisi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $inventaris_id, $divisi_target);
    $stmt->execute();
    $result_divisi = $stmt->get_result();
    
    if ($result_divisi->num_rows == 0) {
        $error = "Divisi yang dipilih tidak memiliki stok barang ini!";
    } else {
        $row_divisi = $result_divisi->fetch_assoc();
        $stok_divisi = $row_divisi['stok_divisi'];
        
        // Insert ke tabel kerusakan_barang
        $sql = "INSERT INTO kerusakan_barang (inventaris_id, tanggal_kerusakan, jenis_kerusakan, deskripsi_kerusakan, divisi_target) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $inventaris_id, $tanggal_kerusakan, $jenis_kerusakan, $deskripsi_kerusakan, $divisi_target);
        
        if ($stmt->execute()) {
            // Perubahan sesuai permintaan: status hanya di tabel inventaris & stok tidak berubah
            if ($jenis_kerusakan == 'Tidak Dapat Diperbaiki') {
                $success = "Data kerusakan berhasil disimpan! Status diperbarui menjadi Rusak. Stok tidak berubah.";
            } else {
                $success = "Data kerusakan berhasil disimpan! Status tetap Tersedia. Stok tidak berubah.";
            }

            // Update status inventaris tanpa mengubah stok
            if ($jenis_kerusakan == 'Tidak Dapat Diperbaiki') {
                $sql = "UPDATE inventaris SET status = 'Rusak' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $inventaris_id);
                $stmt->execute();
            } else {
                $sql = "UPDATE inventaris SET status = 'Tersedia' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $inventaris_id);
                $stmt->execute();
            }
    } else {
        $error = "Gagal menyimpan data kerusakan!";
        }
    }
}

// Proses pemindahan barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pemindahan'])) {
    $inventaris_id = $_POST['inventaris_id'];
    $divisi_asal = $_POST['divisi_asal'];
    $divisi_tujuan = $_POST['divisi_tujuan'];
    $tanggal_pemindahan = $_POST['tanggal_pemindahan'];
    $alasan = $_POST['alasan'];
    
    // Ambil baris distribusi asal (id dan stok)
    $sql = "SELECT id, stok_divisi FROM distribusi_divisi WHERE inventaris_id = ? AND divisi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $inventaris_id, $divisi_asal);
    $stmt->execute();
    $result_asal = $stmt->get_result();
    
    if ($result_asal->num_rows == 0) {
        $error = "Divisi asal tidak memiliki stok barang ini!";
    } else {
        $row_asal = $result_asal->fetch_assoc();
        $distribusi_id_asal = (int)$row_asal['id'];
        $stok_asal = (int)$row_asal['stok_divisi'];

        // Cek apakah sudah ada baris untuk divisi tujuan
        $sql = "SELECT id, stok_divisi FROM distribusi_divisi WHERE inventaris_id = ? AND divisi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $inventaris_id, $divisi_tujuan);
        $stmt->execute();
        $result_tujuan = $stmt->get_result();

        // Mulai transaction
        $conn->begin_transaction();
        try {
            if ($result_tujuan->num_rows == 0) {
                // Tidak ada baris tujuan: cukup update kolom divisi pada baris asal
                $sql = "UPDATE distribusi_divisi SET divisi = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $divisi_tujuan, $distribusi_id_asal);
                $stmt->execute();

                // Catat pemindahan dan relasikan ke baris distribusi yang sama (id tidak berubah)
                $sql = "INSERT INTO pemindahan_barang (inventaris_id, distribusi_id, divisi_asal, divisi_tujuan, tanggal_pemindahan, alasan_pemindahan)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissss", $inventaris_id, $distribusi_id_asal, $divisi_asal, $divisi_tujuan, $tanggal_pemindahan, $alasan);
                $stmt->execute();
            } else {
                // Sudah ada baris untuk divisi tujuan: gabungkan stok -> update tujuan, hapus baris asal
                $row_tujuan = $result_tujuan->fetch_assoc();
                $distribusi_id_tujuan = (int)$row_tujuan['id'];

                $sql = "UPDATE distribusi_divisi SET stok_divisi = stok_divisi + ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $stok_asal, $distribusi_id_tujuan);
                $stmt->execute();

                // Hapus baris asal untuk menghindari duplikat unique (inventaris_id, divisi)
                $sql = "DELETE FROM distribusi_divisi WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $distribusi_id_asal);
                $stmt->execute();

                // Catat pemindahan dan relasikan ke baris distribusi tujuan
                $sql = "INSERT INTO pemindahan_barang (inventaris_id, distribusi_id, divisi_asal, divisi_tujuan, tanggal_pemindahan, alasan_pemindahan)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissss", $inventaris_id, $distribusi_id_tujuan, $divisi_asal, $divisi_tujuan, $tanggal_pemindahan, $alasan);
                $stmt->execute();
            }

            $conn->commit();
            $success = "Pemindahan berhasil: $divisi_asal ➜ $divisi_tujuan";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal melakukan pemindahan: " . $e->getMessage();
        }
    }
}

// Ambil data inventaris (tanpa pencarian & pagination)
$sql = "SELECT i.*, pb.nama_barang as nama_pengajuan, pb.spesifikasi as spesifikasi_pengajuan,
        GROUP_CONCAT(CONCAT(dd.divisi, ' (', dd.stok_divisi, ')') SEPARATOR ', ') as divisi_info,
        COUNT(dd.id) as jumlah_divisi,
        kb_last.divisi_target AS divisi_kerusakan_terakhir,
        kb_div.status_divisi_summary,
        p_last.tanggal_penyerahan AS tanggal_penyerahan,
        p_last.catatan_penyerahan AS catatan_penyerahan
        FROM inventaris i 
        LEFT JOIN pengajuan_barang pb ON i.pengajuan_id = pb.id 
        LEFT JOIN distribusi_divisi dd ON i.id = dd.inventaris_id
        LEFT JOIN (
            SELECT k1.inventaris_id, k1.divisi_target
            FROM kerusakan_barang k1
            JOIN (
                SELECT inventaris_id, MAX(created_at) AS last_created
                FROM kerusakan_barang
                GROUP BY inventaris_id
            ) k2 ON k1.inventaris_id = k2.inventaris_id AND k1.created_at = k2.last_created
        ) kb_last ON kb_last.inventaris_id = i.id
        LEFT JOIN (
            SELECT t.inventaris_id,
                   GROUP_CONCAT(CONCAT(t.divisi_target, '|', t.jenis_kerusakan) SEPARATOR ',') AS status_divisi_summary
            FROM (
                SELECT k1.inventaris_id, k1.divisi_target, k1.jenis_kerusakan, k1.created_at
                FROM kerusakan_barang k1
                JOIN (
                    SELECT inventaris_id, divisi_target, MAX(created_at) AS last_created
                    FROM kerusakan_barang
                    GROUP BY inventaris_id, divisi_target
                ) k2 ON k1.inventaris_id = k2.inventaris_id AND k1.divisi_target = k2.divisi_target AND k1.created_at = k2.last_created
            ) t
            GROUP BY t.inventaris_id
        ) kb_div ON kb_div.inventaris_id = i.id
        LEFT JOIN (
            SELECT p1.inventaris_id, p1.tanggal_penyerahan, p1.catatan_penyerahan
            FROM penyerahan_barang p1
            JOIN (
                SELECT inventaris_id, MAX(tanggal_penyerahan) AS last_date
                FROM penyerahan_barang
                GROUP BY inventaris_id
            ) p2 ON p1.inventaris_id = p2.inventaris_id AND p1.tanggal_penyerahan = p2.last_date
        ) p_last ON p_last.inventaris_id = i.id
        GROUP BY i.id
        ORDER BY i.created_at DESC";
$result = $conn->query($sql);
// ...existing code...
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Inventaris - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar {
            min-height: 180vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><i class="fas fa-boxes me-2"></i>Inventaris IT</h4>
                        <small class="text-white-50">Selamat datang, <?php echo $_SESSION['nama']; ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        
                        <?php if (in_array($role, ['staff', 'admin'])): ?>
                        <a class="nav-link" href="pengajuan.php">
                            <i class="fas fa-plus-circle me-2"></i>Pengajuan Barang
                        </a>
                        <?php endif; ?>
                        
                        <a class="nav-link" href="daftar_pengajuan.php">
                            <i class="fas fa-list me-2"></i>Daftar Pengajuan
                        </a>
                        
                        <?php if (!in_array($role, ['keuangan'])): ?>
                        <a class="nav-link active" href="inventaris.php">
                            <i class="fas fa-boxes me-2"></i>Data Inventaris
                        </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($role, ['admin', 'kepala'])): ?>
                        <a class="nav-link" href="laporan.php">
                            <i class="fas fa-chart-bar me-2"></i>Laporan
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($role == 'admin'): ?>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Manajemen User
                        </a>
                        <?php endif; ?>
                        
                        <hr class="text-white-50">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-boxes me-2"></i>Data Inventaris</h2>
                        <div>
                            <button class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <?php if (!in_array($role, ['keuangan'])): ?>
                            <a href="laporan_inventaris.php" class="btn btn-outline-success me-2">
                                <i class="fas fa-chart-bar me-2"></i>Laporan
                            </a>
                            <?php endif; ?>
                            <a href="daftar_pengajuan.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Lihat Pengajuan
                            </a>
                            <!-- Tombol Laporan PDF -->
                            <button class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#laporanPdfModal">
                                <i class="fas fa-file-pdf me-2"></i>Laporan PDF
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            
                            
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="example1">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Barang</th>
                                                <th>Nomor Seri</th>
                                                <th>Jenis Barang</th>
                                                <th>Stok</th>
                                                <th>Status</th>
                                                <th>Divisi Sekarang</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while ($row = $result->fetch_assoc()): 
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['nama_barang']); ?></strong>
                                                    <?php if ($row['spesifikasi']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['spesifikasi']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['nomor_seri'] ?: '-'); ?></td>
                                                 <td><?php echo htmlspecialchars($row['jenis_barang'] ?: '-'); ?></td>
                                                <!-- <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td> -->
                                                <td><span class="badge bg-primary"><?php echo $row['stok']; ?> Unit</span></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($row['status']) {
                                                        case 'Tersedia':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'Diserahkan':
                                                            $status_class = 'bg-info';
                                                            break;
                                                        case 'Rusak':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                        case 'Dalam Perbaikan':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'Dipindahkan':
                                                            $status_class = 'bg-primary';
                                                            break;
                                                        default:
                                                            $status_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <?php
                                                    // Kumpulkan semua divisi dari distribusi
                                                    $allDivisions = [];
                                                    if (!empty($row['divisi_info'])) {
                                                        $divisi_array = explode(', ', $row['divisi_info']);
                                                        foreach ($divisi_array as $divisi_item) {
                                                            $divisi_parts = explode(' (', $divisi_item);
                                                            $nama_divisi = trim($divisi_parts[0]);
                                                            if ($nama_divisi !== '') {
                                                                $allDivisions[$nama_divisi] = true;
                                                            }
                                                        }
                                                    }

                                                    // Ambil status kerusakan terakhir per divisi
                                                    $lastDamagePerDiv = [];
                                                    if (!empty($row['status_divisi_summary'])) {
                                                        $pairs = explode(',', $row['status_divisi_summary']);
                                                        foreach ($pairs as $pair) {
                                                            $parts = explode('|', $pair);
                                                            $divisi = trim($parts[0] ?? '');
                                                            $jenis  = trim($parts[1] ?? '');
                                                            if ($divisi !== '') {
                                                                $lastDamagePerDiv[$divisi] = $jenis;
                                                            }
                                                        }
                                                    }

                                                    // Bagi menjadi rusak vs tersedia per divisi
                                                    $rusakDiv = [];
                                                    $tersediaDiv = [];
                                                    foreach (array_keys($allDivisions) as $divisiNama) {
                                                        $jenis = $lastDamagePerDiv[$divisiNama] ?? '';
                                                        if ($jenis === 'Tidak Dapat Diperbaiki') {
                                                            $rusakDiv[] = htmlspecialchars($divisiNama);
                                                        } else {
                                                            $tersediaDiv[] = htmlspecialchars($divisiNama);
                                                        }
                                                    }

                                                    // Render badge per kategori
                                                    if (!empty($rusakDiv)) {
                                                        echo '<span class="badge bg-danger status-badge">Rusak (' . implode(', ', $rusakDiv) . ')</span> ';
                                                    }
                                                    if (!empty($tersediaDiv)) {
                                                        echo '<span class="badge bg-success status-badge">Tersedia (' . implode(', ', $tersediaDiv) . ')</span>';
                                                    }
                                                    if (empty($rusakDiv) && empty($tersediaDiv)) {
                                                        // Fallback jika tidak ada distribusi divisi
                                                        echo '<span class="badge ' . $status_class . ' status-badge">' . htmlspecialchars($row['status']) . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                        <?php if ($row['divisi_info']): ?>
                                                            <?php 
                                                            $divisi_array = explode(', ', $row['divisi_info']);
                                                            $divisi_stok = [];
                                                            foreach ($divisi_array as $divisi_item) {
                                                                $divisi_parts = explode(' (', $divisi_item);
                                                                $nama_divisi = trim($divisi_parts[0]);
                                                                $stok_divisi = isset($divisi_parts[1]) ? (int) rtrim($divisi_parts[1], ')') : 0;
                                                                if ($stok_divisi > 0) {
                                                                    $divisi_stok[$nama_divisi] = $stok_divisi;
                                                                }
                                                            }
                                                            if (!empty($divisi_stok)) {
                                                                foreach ($divisi_stok as $nama_divisi => $stok_divisi) {
                                                                    echo '<span class="badge bg-info me-1 mb-1">' . htmlspecialchars($nama_divisi) . ' (' . $stok_divisi . ')</span> ';
                                                                }
                                                            } else {
                                                                echo '<span class="badge bg-secondary">Belum ada divisi</span>';
                                                            }
                                                            ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Belum ada divisi</span>
                                                        <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <?php if ($row['status'] == 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#penyerahanModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-handshake"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#kerusakanModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                        </button>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#pemindahanModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Barang Inventaris</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong>No Inventaris:</strong><br><?php echo htmlspecialchars($row['no_inventaris']); ?></p>
                                                                    <p><strong>Nama Barang:</strong><br><?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                    <p><strong>Jenis Barang:</strong><br><?php echo htmlspecialchars($row['jenis_barang'] ?: '-'); ?></p>
                                                                    <p><strong>Spesifikasi:</strong><br><?php echo htmlspecialchars($row['spesifikasi'] ?: '-'); ?></p>
                                                                    <p><strong>Nomor Seri:</strong><br><?php echo htmlspecialchars($row['nomor_seri'] ?: '-'); ?></p>
                                                                    <p><strong>Harga:</strong><br>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Status:</strong><br>
                                                                        <?php
                                                                        // Kumpulkan semua divisi dari distribusi
                                                                        $allDivisions = [];
                                                                        if (!empty($row['divisi_info'])) {
                                                                            $divisi_array = explode(', ', $row['divisi_info']);
                                                                            foreach ($divisi_array as $divisi_item) {
                                                                                $divisi_parts = explode(' (', $divisi_item);
                                                                                $nama_divisi = trim($divisi_parts[0]);
                                                                                if ($nama_divisi !== '') {
                                                                                    $allDivisions[$nama_divisi] = true;
                                                                                }
                                                                            }
                                                                        }

                                                                        // Ambil status kerusakan terakhir per divisi
                                                                        $lastDamagePerDiv = [];
                                                                        if (!empty($row['status_divisi_summary'])) {
                                                                            $pairs = explode(',', $row['status_divisi_summary']);
                                                                            foreach ($pairs as $pair) {
                                                                                $parts = explode('|', $pair);
                                                                                $divisi = trim($parts[0] ?? '');
                                                                                $jenis  = trim($parts[1] ?? '');
                                                                                if ($divisi !== '') {
                                                                                    $lastDamagePerDiv[$divisi] = $jenis;
                                                                                }
                                                                            }
                                                                        }

                                                                        // Bagi menjadi rusak vs tersedia per divisi
                                                                        $rusakDiv = [];
                                                                        $tersediaDiv = [];
                                                                        foreach (array_keys($allDivisions) as $divisiNama) {
                                                                            $jenis = $lastDamagePerDiv[$divisiNama] ?? '';
                                                                            if ($jenis === 'Tidak Dapat Diperbaiki') {
                                                                                $rusakDiv[] = htmlspecialchars($divisiNama);
                                                                            } else {
                                                                                $tersediaDiv[] = htmlspecialchars($divisiNama);
                                                                            }
                                                                        }

                                                                        // Render badge per kategori
                                                                        if (!empty($rusakDiv)) {
                                                                            echo '<span class="badge bg-danger">Rusak (' . implode(', ', $rusakDiv) . ')</span> ';
                                                                        }
                                                                        if (!empty($tersediaDiv)) {
                                                                            echo '<span class="badge bg-success">Tersedia (' . implode(', ', $tersediaDiv) . ')</span>';
                                                                        }
                                                                        if (empty($rusakDiv) && empty($tersediaDiv)) {
                                                                            // Fallback jika tidak ada distribusi divisi
                                                                            echo '<span class="badge ' . $status_class . '">' . htmlspecialchars($row['status']) . '</span>';
                                                                        }
                                                                        ?>
                                                                    </p>
                                                                    <p><strong>Distribusi Divisi:</strong><br>
                                                                        <?php if ($row['divisi_info']): ?>
                                                                            <?php 
                                                                            $divisi_array = explode(', ', $row['divisi_info']);
                                                                            foreach ($divisi_array as $divisi_item): 
                                                                                $divisi_parts = explode(' (', $divisi_item);
                                                                                $nama_divisi = $divisi_parts[0];
                                                                                $stok_divisi = rtrim($divisi_parts[1], ')');
                                                                            ?>
                                                                                <span class="badge bg-info me-1 mb-1">
                                                                                    <?php echo htmlspecialchars($nama_divisi); ?> (<?php echo $stok_divisi; ?>)
                                                                                </span>
                                                                            <?php endforeach; ?>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">Belum ada divisi</span>
                                                                        <?php endif; ?>
                                                                    </p>
                                                                    <p><strong>Tanggal Pembelian:</strong><br><?php echo $row['tanggal_pembelian'] ? date('d/m/Y', strtotime($row['tanggal_pembelian'])) : '-'; ?></p>
                                                                    <p><strong>Tanggal Penyerahan:</strong><br><?php echo $row['tanggal_penyerahan'] ? date('d/m/Y', strtotime($row['tanggal_penyerahan'])) : '-'; ?></p>
                                                                </div>
                                                            </div>
                                                            <?php if ($row['catatan_penyerahan']): ?>
                                                            <div class="row mt-3">
                                                                <div class="col-12">
                                                                    <p><strong>Catatan Penyerahan:</strong></p>
                                                                    <div class="border rounded p-3 bg-light">
                                                                        <?php echo nl2br(htmlspecialchars($row['catatan_penyerahan'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal Penyerahan -->
                                            <?php if ($row['status'] == 'Tersedia'): ?>
                                            <div class="modal fade" id="penyerahanModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Penyerahan Barang</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                <p><strong>Stok Tersedia:</strong> <span class="badge bg-primary"><?php echo $row['stok']; ?> Unit</span></p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jumlah yang Diserahkan</label>
                                                                    <input type="number" name="jumlah_serah" class="form-control" value="1" min="1" max="<?php echo $row['stok']; ?>" required>
                                                                    <small class="form-text text-muted">Maksimal: <?php echo $row['stok']; ?> unit</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Divisi Tujuan</label>
                                                                    <input type="text" name="divisi_tujuan" class="form-control" placeholder="Contoh: IT, Keuangan, SDM, dll." required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tanggal Penyerahan</label>
                                                                    <input type="date" name="tanggal_penyerahan" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan</label>
                                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan penyerahan..."></textarea>
                                                                </div>
                                                                <input type="hidden" name="inventaris_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="penyerahan" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">Serahkan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Modal Kerusakan -->
                                            <div class="modal fade" id="kerusakanModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Input Kerusakan Barang</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tanggal Kerusakan</label>
                                                                    <input type="date" name="tanggal_kerusakan" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jenis Kerusakan</label>
                                                                    <select name="jenis_kerusakan" class="form-select" required>
                                                                        <option value="">Pilih Jenis Kerusakan</option>
                                                                        <option value="Dapat Diperbaiki">Dapat Diperbaiki</option>
                                                                        <option value="Tidak Dapat Diperbaiki">Tidak Dapat Diperbaiki</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Deskripsi Kerusakan</label>
                                                                    <textarea name="deskripsi_kerusakan" class="form-control" rows="3" placeholder="Jelaskan kerusakan yang terjadi..." required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Divisi Target</label>
                                                                    <select name="divisi_target" class="form-select" required>
                                                                        <option value="">Pilih Divisi</option>
                                                                        <?php
                                                                        // Ambil divisi yang memiliki stok barang ini
                                                                        $sql_divisi = "SELECT divisi, stok_divisi FROM distribusi_divisi WHERE inventaris_id = ? AND stok_divisi > 0";
                                                                        $stmt_divisi = $conn->prepare($sql_divisi);
                                                                        $stmt_divisi->bind_param("i", $row['id']);
                                                                        $stmt_divisi->execute();
                                                                        $result_divisi = $stmt_divisi->get_result();
                                                                        
                                                                        if ($result_divisi->num_rows > 0):
                                                                            while ($divisi_row = $result_divisi->fetch_assoc()):
                                                                        ?>
                                                                            <option value="<?php echo htmlspecialchars($divisi_row['divisi']); ?>">
                                                                                <?php echo htmlspecialchars($divisi_row['divisi']); ?> (Stok: <?php echo $divisi_row['stok_divisi']; ?>)
                                                                            </option>
                                                                        <?php 
                                                                            endwhile;
                                                                        else:
                                                                        ?>
                                                                            <option value="" disabled>Barang belum diserahkan ke divisi manapun</option>
                                                                        <?php endif; ?>
                                                                    </select>
                                                                    <small class="form-text text-muted">Pilih divisi yang memiliki barang yang akan dilaporkan kerusakannya</small>
                                                                </div>
                                                                <input type="hidden" name="inventaris_id" value="<?= $row['id']; ?>">
                                                                <input type="hidden" name="input_kerusakan" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-warning">Input Kerusakan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal Pemindahan -->
                                            <div class="modal fade" id="pemindahanModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Pemindahan Barang</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                <p><strong>Catatan:</strong> Semua stok di divisi asal akan dipindahkan ke divisi tujuan</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Divisi Asal</label>
                                                                    <select name="divisi_asal" class="form-select" required>
                                                                        <option value="">Pilih Divisi Asal</option>
                                                                        <?php
                                                                        // Ambil divisi yang memiliki stok barang ini
                                                                        $sql_divisi = "SELECT divisi, stok_divisi FROM distribusi_divisi WHERE inventaris_id = ? AND stok_divisi > 0";
                                                                        $stmt_divisi = $conn->prepare($sql_divisi);
                                                                        $stmt_divisi->bind_param("i", $row['id']);
                                                                        $stmt_divisi->execute();
                                                                        $result_divisi = $stmt_divisi->get_result();
                                                                        
                                                                        if ($result_divisi->num_rows > 0):
                                                                            while ($divisi_row = $result_divisi->fetch_assoc()):
                                                                        ?>
                                                                            <option value="<?php echo htmlspecialchars($divisi_row['divisi']); ?>">
                                                                                <?php echo htmlspecialchars($divisi_row['divisi']); ?> (Stok: <?php echo $divisi_row['stok_divisi']; ?>)
                                                                            </option>
                                                                        <?php 
                                                                            endwhile;
                                                                        else:
                                                                        ?>
                                                                            <option value="" disabled>Barang belum diserahkan ke divisi manapun</option>
                                                                        <?php endif; ?>
                                                                    </select>
                                                                    <small class="form-text text-muted">Pilih divisi yang akan memindahkan barang</small>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Divisi Tujuan</label>
                                                                    <input type="text" name="divisi_tujuan" class="form-control" placeholder="Contoh: IT, Keuangan, SDM, dll." required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tanggal Pemindahan</label>
                                                                    <input type="date" name="tanggal_pemindahan" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Alasan Pemindahan</label>
                                                                    <textarea name="alasan" class="form-control" rows="3" placeholder="Alasan pemindahan barang..." required></textarea>
                                                                </div>
                                                                <input type="hidden" name="inventaris_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="pemindahan" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Pindahkan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada data inventaris</h5>
                                    <p class="text-muted">Barang akan muncul di sini setelah pengajuan disetujui dan barang diterima</p>
                                    <a href="daftar_pengajuan.php" class="btn btn-primary mt-2">
                                        <i class="fas fa-list me-2"></i>Lihat Daftar Pengajuan
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Data Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Filter Berdasarkan</label>
                        <select class="form-select" id="filterType">
                            <option value="">-- Pilih Filter --</option>
                            <option value="jenis_barang">Jenis Barang</option>
                            <option value="status">Status Barang</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="filterJenisBarang">
                        <label class="form-label">Jenis Barang</label>
                        <select class="form-select" id="jenisBarangSelect">
                            <option value="">Semua Jenis Barang</option>
                            <option value="Komputer & Laptop">Komputer & Laptop</option>
                            <option value="Komponen Komputer & Laptop">Komponen Komputer & Laptop</option>
                            <option value="Printer & Scanner">Printer & Scanner</option>
                            <option value="Komponen Printer & Scanner">Komponen Printer & Scanner</option>
                            <option value="Komponen Network">Komponen Network</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="filterStatus">
                        <label class="form-label">Status Barang</label>
                        <select class="form-select" id="statusSelect">
                            <option value="">Semua Status</option>
                            <option value="Tersedia">Tersedia</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Dalam Perbaikan">Dalam Perbaikan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal Laporan PDF -->
        <div class="modal fade" id="laporanPdfModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="get" action="laporan-barangstatus_pdf.php" target="_blank">
                        <div class="modal-header">
                            <h5 class="modal-title">Laporan Data Barang PDF</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Status Barang</label>
                                <select class="form-select" name="status" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="Tersedia">Tersedia</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tahun</label>
                                <input type="number" class="form-control" name="tahun" min="2015" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-file-pdf me-2"></i>Download & Print PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Validasi jumlah penyerahan tidak melebihi stok
        document.addEventListener('DOMContentLoaded', function() {
            const jumlahInputs = document.querySelectorAll('input[name="jumlah_serah"]');
            
            jumlahInputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    const max = parseInt(this.getAttribute('max'));
                    const value = parseInt(this.value);
                    
                    if (value > max) {
                        alert('Jumlah yang diserahkan tidak boleh melebihi stok tersedia!');
                        this.value = max;
                    }
                    
                    if (value < 1) {
                        this.value = 1;
                    }
                });
            });
        });

        $(document).ready(function() {
            var table = $('#example1').DataTable({
                "lengthChange": false,
                "paging":true,
                "pagingType":"numbers",
                "scrollCollapse": true,
                "ordering":true,
                "info":true,
                "language":{
                    "decimal":       "",
                    "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
                    "sProcessing":   "Sedang memproses...",
                    "sLengthMenu":   "Tampilkan _MENU_ entri",
                    "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                    "sInfo":         "Showing _START_ to _END_ of _TOTAL_ entri",
                    "sInfoEmpty":    "Showing 0 to 0 of 0 entries",
                    "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "sInfoPostFix":  "",
                    "sSearch":       "",
                    "searchPlaceholder": "Cari Data..",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    "Pertama",
                        "sPrevious": "Sebelumnya",
                        "sNext":     "Selanjutnya",
                        "sLast":     "Terakhir"
                    }
                }
            });

            // Filter modal dinamis
            $('#filterType').on('change', function() {
                var val = $(this).val();
                $('#filterJenisBarang').addClass('d-none');
                $('#filterStatus').addClass('d-none');
                if (val === 'jenis_barang') {
                    $('#filterJenisBarang').removeClass('d-none');
                } else if (val === 'status') {
                    $('#filterStatus').removeClass('d-none');
                }
            });

            // Filter Jenis Barang
            $('#jenisBarangSelect').on('change', function() {
                var val = $(this).val();
                // Kolom Jenis Barang ada di kolom ke-3 (index 3, karena 0-based: No, Nama Barang, Nomor Seri, Jenis Barang)
                if (val) {
                    table.column(3).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                } else {
                    table.column(3).search('', true, false).draw();
                }
            });

            // Filter Status
            $('#statusSelect').on('change', function() {
                var val = $(this).val();
                if (val) {
                    table.column(5).search(val, true, false).draw();
                } else {
                    table.column(5).search('').draw();
                }
            });
        });
    </script>
</body>
</html>