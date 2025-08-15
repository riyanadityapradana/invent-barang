<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Cek role (hanya admin dan kepala yang bisa akses)
if (!in_array($_SESSION['role'], ['admin', 'kepala'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filter periode
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Data untuk laporan
$sql = "SELECT COUNT(*) as total FROM pengajuan_barang WHERE MONTH(tanggal_pengajuan) = ? AND YEAR(tanggal_pengajuan) = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ss", $bulan, $tahun);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $total_pengajuan = $result->fetch_assoc()['total'];
    } else {
        $total_pengajuan = 0;
    }
} else {
    $total_pengajuan = 0;
}

// Status pengajuan
$sql = "SELECT status, COUNT(*) as count FROM pengajuan_barang WHERE MONTH(tanggal_pengajuan) = ? AND YEAR(tanggal_pengajuan) = ? GROUP BY status";
$stmt = $conn->prepare($sql);
$status_counts = [];
if ($stmt) {
    $stmt->bind_param("ss", $bulan, $tahun);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status_counts[$row['status']] = $row['count'];
        }
    }
}

// Total inventaris
$sql = "SELECT COUNT(*) as total FROM inventaris";
$result = $conn->query($sql);
$total_inventaris = $result ? $result->fetch_assoc()['total'] : 0;

// Total stok tersedia
$sql = "SELECT SUM(stok) as total_stok FROM inventaris WHERE status = 'Tersedia'";
$result = $conn->query($sql);
$total_stok = $result ? ($result->fetch_assoc()['total_stok'] ?: 0) : 0;

// Status inventaris
$sql = "SELECT status, COUNT(*) as count FROM inventaris GROUP BY status";
$result = $conn->query($sql);
$inventaris_status = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventaris_status[$row['status']] = $row['count'];
    }
}

// Total barang rusak
$sql = "SELECT COUNT(*) as total FROM inventaris WHERE status = 'Rusak'";
$result = $conn->query($sql);
$total_rusak = $result ? $result->fetch_assoc()['total'] : 0;

// Total nilai inventaris
$sql = "SELECT SUM(harga * stok) as total_nilai FROM inventaris";
$result = $conn->query($sql);
$total_nilai = $result ? ($result->fetch_assoc()['total_nilai'] ?: 0) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            min-height: 160vh;
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
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
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
                        
                        <a class="nav-link" href="inventaris.php">
                            <i class="fas fa-boxes me-2"></i>Data Inventaris
                        </a>
                        
                        <a class="nav-link active" href="laporan.php">
                            <i class="fas fa-chart-bar me-2"></i>Laporan
                        </a>
                        
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
                        <h2><i class="fas fa-chart-bar me-2"></i>Laporan & Analisis</h2>
                        <div>
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Cetak Laporan
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filter Periode -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bulan</label>
                                    <select name="bulan" class="form-select">
                                        <option value="01" <?php echo $bulan == '01' ? 'selected' : ''; ?>>Januari</option>
                                        <option value="02" <?php echo $bulan == '02' ? 'selected' : ''; ?>>Februari</option>
                                        <option value="03" <?php echo $bulan == '03' ? 'selected' : ''; ?>>Maret</option>
                                        <option value="04" <?php echo $bulan == '04' ? 'selected' : ''; ?>>April</option>
                                        <option value="05" <?php echo $bulan == '05' ? 'selected' : ''; ?>>Mei</option>
                                        <option value="06" <?php echo $bulan == '06' ? 'selected' : ''; ?>>Juni</option>
                                        <option value="07" <?php echo $bulan == '07' ? 'selected' : ''; ?>>Juli</option>
                                        <option value="08" <?php echo $bulan == '08' ? 'selected' : ''; ?>>Agustus</option>
                                        <option value="09" <?php echo $bulan == '09' ? 'selected' : ''; ?>>September</option>
                                        <option value="10" <?php echo $bulan == '10' ? 'selected' : ''; ?>>Oktober</option>
                                        <option value="11" <?php echo $bulan == '11' ? 'selected' : ''; ?>>November</option>
                                        <option value="12" <?php echo $bulan == '12' ? 'selected' : ''; ?>>Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-select">
                                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block w-100">
                                        <i class="fas fa-filter me-2"></i>Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Ringkasan -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $total_pengajuan; ?></h4>
                                            <small>Pengajuan <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $total_inventaris; ?></h4>
                                            <small>Total Inventaris</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-boxes fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $total_rusak; ?></h4>
                                            <small>Barang Rusak</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></h4>
                                            <small>Total Nilai</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-money-bill fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $total_stok ?></h4>
                                            <small>Total Stok Tersedia</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-boxes fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Pengajuan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="pengajuanChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status Inventaris</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="inventarisChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabel Detail -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detail Status Pengajuan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Jumlah</th>
                                                    <th>Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total = array_sum($status_counts);
                                                foreach ($status_counts as $status => $count): 
                                                    $persentase = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $status; ?></span>
                                                    </td>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo $persentase; ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-light">
                                                    <td><strong>Total</strong></td>
                                                    <td><strong><?php echo $total; ?></strong></td>
                                                    <td><strong>100%</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Detail Status Inventaris</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Jumlah</th>
                                                    <th>Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_inv = array_sum($inventaris_status);
                                                foreach ($inventaris_status as $status => $count): 
                                                    $persentase = $total_inv > 0 ? round(($count / $total_inv) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $status; ?></span>
                                                    </td>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo $persentase; ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-light">
                                                    <td><strong>Total</strong></td>
                                                    <td><strong><?php echo $total_inv; ?></strong></td>
                                                    <td><strong>100%</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
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
    <script>
        // Chart Pengajuan
        const pengajuanCtx = document.getElementById('pengajuanChart').getContext('2d');
        new Chart(pengajuanCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($status_counts)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($status_counts)); ?>,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart Inventaris
        const inventarisCtx = document.getElementById('inventarisChart').getContext('2d');
        new Chart(inventarisCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($inventaris_status)); ?>,
                datasets: [{
                    label: 'Jumlah Barang',
                    data: <?php echo json_encode(array_values($inventaris_status)); ?>,
                    backgroundColor: [
                        '#4BC0C0',
                        '#36A2EB',
                        '#FF6384',
                        '#FFCE56',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
