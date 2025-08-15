<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

// Ambil data untuk dashboard
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Batasi akses: hanya admin, kepala, staff yang boleh melihat dashboard
$allowed_roles = ['admin', 'kepala', 'staff'];
if (!in_array($role, $allowed_roles)) {
    header("Location: inventaris.php");
    exit();
}

// Hitung total pengajuan
$sql = "SELECT COUNT(*) as total FROM pengajuan_barang";
$result = $conn->query($sql);
$total_pengajuan = $result->fetch_assoc()['total'];

// Hitung pengajuan berdasarkan status
$sql = "SELECT status, COUNT(*) as count FROM pengajuan_barang GROUP BY status";
$result = $conn->query($sql);
$status_counts = [];
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Total inventaris
$sql = "SELECT COUNT(*) as total FROM inventaris";
$result = $conn->query($sql);
$total_inventaris = $result->fetch_assoc()['total'];

// Total stok tersedia
$sql = "SELECT SUM(stok) as total_stok FROM inventaris WHERE status = 'Tersedia'";
$result = $conn->query($sql);
$total_stok = $result->fetch_assoc()['total_stok'] ?: 0;

// Total barang rusak
$sql = "SELECT COUNT(*) as total FROM inventaris WHERE status = 'Rusak'";
$result = $conn->query($sql);
$total_rusak = $result->fetch_assoc()['total'];

// Total nilai inventaris = harga * (stok pusat + total stok per divisi)
$sql = "SELECT SUM(i.harga * (i.stok + IFNULL(dd.total_divisi, 0))) AS total_nilai
        FROM inventaris i
        LEFT JOIN (
            SELECT inventaris_id, SUM(stok_divisi) AS total_divisi
            FROM distribusi_divisi
            GROUP BY inventaris_id
        ) dd ON dd.inventaris_id = i.id";
$result = $conn->query($sql);
$rowNilai = $result->fetch_assoc();
$total_nilai = $rowNilai && $rowNilai['total_nilai'] ? $rowNilai['total_nilai'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
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
                        <a class="nav-link active" href="dashboard.php">
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
                        <a class="nav-link" href="inventaris.php">
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
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                        <div class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d F Y'); ?>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $total_pengajuan; ?></h4>
                                            <small>Total Pengajuan</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x"></i>
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
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Stok Tersedia
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_stok ?> Unit</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Barang Rusak
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_rusak ?> Item</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Total Nilai Inventaris
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($total_nilai, 0, ',', '.') ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Pengajuan -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Pengajuan</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($status_counts)): ?>
                                        <p class="text-muted">Belum ada data pengajuan</p>
                                    <?php else: ?>
                                        <?php foreach ($status_counts as $status => $count): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-secondary"><?php echo $status; ?></span>
                                            <span class="fw-bold"><?php echo $count; ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Aktivitas Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <?php
                                        $sql = "SELECT pb.nama_barang, pb.status, pb.tanggal_pengajuan, u.nama as staff_name 
                                                FROM pengajuan_barang pb 
                                                JOIN users u ON pb.staff_id = u.id 
                                                ORDER BY pb.tanggal_pengajuan DESC LIMIT 5";
                                        $result = $conn->query($sql);
                                        
                                        if ($result->num_rows > 0):
                                            while ($row = $result->fetch_assoc()):
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <small class="text-muted"><?php echo $row['staff_name']; ?></small><br>
                                                <strong><?php echo $row['nama_barang']; ?></strong>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $row['status']; ?></span>
                                        </div>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <p class="text-muted">Belum ada aktivitas</p>
                                        <?php endif; ?>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        $(document).ready(function() {
        $('#example1').DataTable({
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
    </script>
</body>
</html>
