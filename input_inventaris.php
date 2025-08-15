<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Cek role (hanya staff dan admin yang bisa akses)
if (!in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';

$success = $error = '';
$pengajuan_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data pengajuan
$sql = "SELECT * FROM pengajuan_barang WHERE id = ? AND status = 'ACC Keuangan'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pengajuan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: daftar_pengajuan.php");
    exit();
}

$pengajuan = $result->fetch_assoc();
$canInput = isset($pengajuan['jumlah']) ? ((int)$pengajuan['jumlah'] > 0) : false;

// Proses form input inventaris
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$canInput) {
        $error = "Stok pengajuan kosong. Silakan melakukan pengajuan terlebih dahulu.";
    } else {
    $nomor_seri = $_POST['nomor_seri'];
    $tanggal_pembelian = $_POST['tanggal_pembelian'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $catatan = $_POST['catatan'];
    $jenis_barang = $_POST['jenis_barang'];
    
    // Validasi server-side stok > 0
    if ((int)$stok <= 0) {
        $error = "Stok tidak boleh kosong. Silakan melakukan pengajuan terlebih dahulu.";
    } else {
    
	// Insert ke tabel inventaris
	$sql = "INSERT INTO inventaris (pengajuan_id, nama_barang, spesifikasi, nomor_seri, tanggal_pembelian, harga, stok, catatan_penyerahan, jenis_barang) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		// Coba fallback jika kolom catatan_penyerahan tidak ada
        $sql = "INSERT INTO inventaris (pengajuan_id, nama_barang, spesifikasi, nomor_seri, tanggal_pembelian, harga, stok, jenis_barang) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Gagal menyiapkan query: " . $conn->error;
        } else {
            $harga = (float)$harga;
            $stok = (int)$stok;
            $stmt->bind_param("issssdis", $pengajuan_id, $pengajuan['nama_barang'], $pengajuan['spesifikasi'], $nomor_seri, $tanggal_pembelian, $harga, $stok, $jenis_barang);
        }
    } else {
        // Tipe parameter: i (int), s (string), d (double)
        // Urutan: id, nama, spesifikasi, nomor_seri, tanggal_pembelian, harga, stok, catatan_penyerahan, jenis_barang
        $harga = (float)$harga;
        $stok = (int)$stok;
        $stmt->bind_param("issssdiss", $pengajuan_id, $pengajuan['nama_barang'], $pengajuan['spesifikasi'], $nomor_seri, $tanggal_pembelian, $harga, $stok, $catatan, $jenis_barang);
    }
    
    if ($stmt && $stmt->execute()) {
        // Update status pengajuan menjadi "Diterima"
        $sql = "UPDATE pengajuan_barang SET status = 'Diterima' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pengajuan_id);
        $stmt->execute();
        
        $success = "Barang berhasil ditambahkan ke inventaris!";
	} else {
		$error = "Gagal menambahkan barang: " . ($stmt ? $stmt->error : $conn->error);
    }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Inventaris - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
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
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
                        
                        <a class="nav-link" href="pengajuan.php">
                            <i class="fas fa-plus-circle me-2"></i>Pengajuan Barang
                        </a>
                        
                        <a class="nav-link" href="daftar_pengajuan.php">
                            <i class="fas fa-list me-2"></i>Daftar Pengajuan
                        </a>
                        
                        <a class="nav-link active" href="inventaris.php">
                            <i class="fas fa-boxes me-2"></i>Data Inventaris
                        </a>
                        
                        <?php if (in_array($_SESSION['role'], ['admin', 'kepala'])): ?>
                        <a class="nav-link" href="laporan.php">
                            <i class="fas fa-chart-bar me-2"></i>Laporan
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['role'] == 'admin'): ?>
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
                        <h2><i class="fas fa-box me-2"></i>Input Barang ke Inventaris</h2>
                        <a href="daftar_pengajuan.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pengajuan
                        </a>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <a href="inventaris.php" class="btn btn-primary">
                                <i class="fas fa-boxes me-2"></i>Lihat Data Inventaris
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informasi Pengajuan -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pengajuan Barang</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nama Barang:</strong><br><?php echo htmlspecialchars($pengajuan['nama_barang']); ?></p>
                                        <p><strong>Spesifikasi:</strong><br><?php echo htmlspecialchars($pengajuan['spesifikasi'] ?: '-'); ?></p>
                                        <p><strong>Perkiraan Harga:</strong><br>Rp <?php echo number_format($pengajuan['perkiraan_harga'], 0, ',', '.'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Alasan Pengajuan:</strong></p>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo nl2br(htmlspecialchars($pengajuan['alasan_pengajuan'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Input Inventaris -->
                        <div class="form-container p-4">
                            <h5 class="mb-4"><i class="fas fa-edit me-2"></i>Form Input Data Inventaris</h5>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nomor_seri" class="form-label">
                                            <i class="fas fa-barcode me-2"></i>Nomor Seri <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="nomor_seri" name="nomor_seri" 
                                               placeholder="Contoh: SN123456789" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_pembelian" class="form-label">
                                            <i class="fas fa-calendar me-2"></i>Tanggal Pembelian <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="jenis_barang" class="form-label">
                                        <i class="fas fa-cubes me-2"></i>Jenis Barang <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_barang" name="jenis_barang" required>
                                        <option value="">-- Pilih Jenis Barang --</option>
                                        <option value="Komputer & Laptop">Komputer & Laptop</option>
                                        <option value="Komponen Komputer & Laptop">Komponen Komputer & Laptop</option>
                                        <option value="Printer & Scanner">Printer & Scanner</option>
                                        <option value="Komponen Printer & Scanner">Komponen Printer & Scanner</option>
                                        <option value="Komponen Network">Komponen Network</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="harga" class="form-label">Harga Pembelian</label>
                                    <input type="number" class="form-control" id="harga" name="harga" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stok" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="stok" name="stok" min="1" value="<?= max((int)$pengajuan['jumlah'], 0) ?>" <?= !$canInput ? 'disabled' : '' ?> required>
                                    <small class="form-text text-muted">
                                        <?php if ($canInput): ?>
                                            Stok otomatis sesuai jumlah pengajuan: <?= (int)$pengajuan['jumlah'] ?> unit
                                        <?php else: ?>
                                            Stok 0. Silakan lakukan pengajuan terlebih dahulu.
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="catatan" class="form-label">Catatan Tambahan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="daftar_pengajuan.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-success" <?= !$canInput ? 'disabled' : '' ?>>
                                        <i class="fas fa-save me-2"></i>Simpan ke Inventaris
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Informasi Status -->
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Status Barang</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-check fa-2x"></i>
                                            </div>
                                            <h6 class="mt-2">Pengajuan</h6>
                                            <small class="text-muted">Disetujui</small>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-box fa-2x"></i>
                                            </div>
                                            <h6 class="mt-2">Input Inventaris</h6>
                                            <small class="text-muted">Langkah Saat Ini</small>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-handshake fa-2x"></i>
                                            </div>
                                            <h6 class="mt-2">Penyerahan</h6>
                                            <small class="text-muted">Ke Divisi</small>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-tasks fa-2x"></i>
                                            </div>
                                            <h6 class="mt-2">Monitoring</h6>
                                            <small class="text-muted">Status Barang</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
