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

// Proses form pengajuan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = $_POST['nama_barang'];
    $spesifikasi = $_POST['spesifikasi'];
    $alasan_pengajuan = $_POST['alasan_pengajuan'];
    $perkiraan_harga = $_POST['perkiraan_harga'];
    $jumlah = $_POST['jumlah'];
    $staff_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO pengajuan_barang (nama_barang, spesifikasi, alasan_pengajuan, perkiraan_harga, jumlah, staff_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddi", $nama_barang, $spesifikasi, $alasan_pengajuan, $perkiraan_harga, $jumlah, $staff_id);
    
    if ($stmt->execute()) {
        $success = "Pengajuan barang berhasil dikirim!";
        // Reset form
        $_POST = array();
    } else {
        $error = "Gagal mengirim pengajuan: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 130vh;
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
                        
                        <a class="nav-link active" href="pengajuan.php">
                            <i class="fas fa-plus-circle me-2"></i>Pengajuan Barang
                        </a>
                        
                        <a class="nav-link" href="daftar_pengajuan.php">
                            <i class="fas fa-list me-2"></i>Daftar Pengajuan
                        </a>
                        
                        <a class="nav-link" href="inventaris.php">
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
                        <h2><i class="fas fa-plus-circle me-2"></i>Pengajuan Barang</h2>
                        <a href="daftar_pengajuan.php" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Lihat Daftar Pengajuan
                        </a>
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
                    
                    <div class="form-container p-4">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_barang" class="form-label">
                                        <i class="fas fa-box me-2"></i>Nama Barang <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                           value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="perkiraan_harga" class="form-label">
                                        <i class="fas fa-money-bill me-2"></i>Perkiraan Harga <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="perkiraan_harga" name="perkiraan_harga" 
                                               value="<?php echo isset($_POST['perkiraan_harga']) ? htmlspecialchars($_POST['perkiraan_harga']) : ''; ?>" 
                                               min="0" step="1000" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="jumlah" class="form-label">Jumlah</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="1" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="spesifikasi" class="form-label">
                                    <i class="fas fa-info-circle me-2"></i>Spesifikasi Barang
                                </label>
                                <textarea class="form-control" id="spesifikasi" name="spesifikasi" rows="3" 
                                          placeholder="Contoh: Processor Intel i5, RAM 8GB, SSD 256GB, dll."><?php echo isset($_POST['spesifikasi']) ? htmlspecialchars($_POST['spesifikasi']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alasan_pengajuan" class="form-label">
                                    <i class="fas fa-comment me-2"></i>Alasan Pengajuan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="alasan_pengajuan" name="alasan_pengajuan" rows="4" 
                                          placeholder="Jelaskan alasan dan urgensi pengajuan barang ini..." required><?php echo isset($_POST['alasan_pengajuan']) ? htmlspecialchars($_POST['alasan_pengajuan']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pengajuan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Informasi Proses -->
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Proses Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-user fa-2x"></i>
                                        </div>
                                        <h6 class="mt-2">1. Staff IT</h6>
                                        <small class="text-muted">Mengajukan barang</small>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-user-tie fa-2x"></i>
                                        </div>
                                        <h6 class="mt-2">2. Kepala Ruangan</h6>
                                        <small class="text-muted">Verifikasi pengajuan</small>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                        </div>
                                        <h6 class="mt-2">3. Keuangan</h6>
                                        <small class="text-muted">Persetujuan anggaran</small>
                                    </div>
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-shopping-cart fa-2x"></i>
                                        </div>
                                        <h6 class="mt-2">4. OB</h6>
                                        <small class="text-muted">Proses pembelian</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
