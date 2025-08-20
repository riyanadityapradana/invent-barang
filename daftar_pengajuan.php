<?php
session_start();
$today = date('Y-m-d');
// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$success = $error = '';

// Proses verifikasi oleh Kepala Ruangan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verifikasi']) && in_array($role, ['kepala', 'admin'])) {
    $pengajuan_id = $_POST['pengajuan_id'];
    $status = $_POST['status'];
    $catatan = $_POST['catatan'];
    
    $sql = "UPDATE pengajuan_barang SET status = ?, catatan_verifikasi = ?, tanggal_verifikasi = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $catatan, $pengajuan_id);
    
    if ($stmt->execute()) {
        $success = "Verifikasi berhasil dilakukan!";
    } else {
        $error = "Gagal melakukan verifikasi: " . $conn->error;
    }
}

// Proses persetujuan oleh Keuangan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acc_keuangan']) && in_array($role, ['keuangan', 'admin'])) {
    $pengajuan_id = $_POST['pengajuan_id'];
    $status = $_POST['status'];
    $catatan = $_POST['catatan'];
    
    $sql = "UPDATE pengajuan_barang SET status = ?, catatan_keuangan = ?, tanggal_acc_keuangan = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $catatan, $pengajuan_id);
    
    if ($stmt->execute()) {
        $success = "Persetujuan keuangan berhasil dilakukan!";
    } else {
        $error = "Gagal melakukan persetujuan: " . $conn->error;
    }
}

// Ambil data pengajuan (tanpa pencarian & pagination)
$where_clause = "";
if ($role == 'staff') {
    $where_clause = "WHERE pb.staff_id = $user_id";
} elseif ($role == 'kepala') {
    $where_clause = "WHERE pb.status IN ('Diajukan', 'Diverifikasi')";
} elseif ($role == 'keuangan') {
    $where_clause = "WHERE pb.status = 'Diverifikasi'";
}

$sql = "SELECT pb.*, u.nama as staff_name 
        FROM pengajuan_barang pb 
        JOIN users u ON pb.staff_id = u.id 
        $where_clause 
        ORDER BY pb.tanggal_pengajuan DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan - Sistem Inventaris Barang IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar {
            min-height: 215vh;
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
                        
                        <a class="nav-link active" href="daftar_pengajuan.php">
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
                        <h2><i class="fas fa-list me-2"></i>Daftar Pengajuan Barang</h2>
                        <?php if (in_array($role, ['staff', 'admin'])): ?>
                        <a href="pengajuan.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Buat Pengajuan Baru
                        </a>
                        <?php endif; ?>
                        <?php if (in_array($role, ['kepala'])): ?>
                        <form method="get" class="form-inline" style="display:inline-block; margin-right:10px;">
                            <a href="surat_pengajuan.php?dari=<?= $today ?>&sampai=<?= $today ?>" target="_blank" class="btn btn-sm btn-info mx-1"><i class="fa fa-print"></i> Print Hari Ini</a>
                        </form>
                        <?php endif; ?>
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
                                                <th>Spesifikasi</th>
                                                <th>Jumlah</th>
                                                <th>Perkiraan Harga</th>
                                                <th>Status</th>
                                                <th>Tanggal Pengajuan</th>
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
                                                </td>
                                                <td><?php echo htmlspecialchars($row['spesifikasi']); ?></td>
                                                <td><span class="badge bg-info"><?php echo $row['jumlah']; ?> Unit</span></td>
                                                <td>Rp <?php echo number_format($row['perkiraan_harga'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($row['status']) {
                                                        case 'Diajukan':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'Diverifikasi':
                                                            $status_class = 'bg-info';
                                                            break;
                                                        case 'ACC Keuangan':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'Ditolak':
                                                        case 'Ditolak Keuangan':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                        default:
                                                            $status_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?> status-badge"><?php echo $row['status']; ?></span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <!-- Tombol WhatsApp untuk Staff -->
                                                        <?php if ($role == 'staff' && $row['status'] == 'Diajukan'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="openWhatsAppModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_barang']); ?>', '<?php echo addslashes($row['staff_name']); ?>', '<?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?>', 'Kepala Ruangan IT')">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Tombol WhatsApp untuk Kepala Ruangan -->
                                                        <?php if (in_array($role, ['kepala', 'admin']) && $row['status'] == 'Diverifikasi'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="openWhatsAppModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_barang']); ?>', '<?php echo addslashes($row['staff_name']); ?>', '<?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?>', 'Bagian Keuangan')">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (in_array($role, ['kepala', 'admin']) && $row['status'] == 'Diajukan'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#verifikasiModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (in_array($role, ['keuangan', 'admin']) && $row['status'] == 'Diverifikasi'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#keuanganModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-dollar-sign"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($row['status'] == 'ACC Keuangan' && in_array($role, ['staff', 'admin'])): ?>
                                                        <a href="input_inventaris.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-box"></i>
                                                        </a>
                                                        <?php endif; ?>

                                                        <?php if (in_array($role, ['kepala', 'admin']) && $row['status'] == 'Diverifikasi'): ?>
                                                            <a href="surat_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                                <i class="fas fa-print me-1"></i>Cetak Surat
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Pengajuan Barang</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong>Nama Barang:</strong><br><?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                    <p><strong>Spesifikasi:</strong><br><?php echo htmlspecialchars($row['spesifikasi'] ?: '-'); ?></p>
                                                                    <p><strong>Perkiraan Harga:</strong><br>Rp <?php echo number_format($row['perkiraan_harga'], 0, ',', '.'); ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Staff:</strong><br><?php echo htmlspecialchars($row['staff_name']); ?></p>
                                                                    <p><strong>Status:</strong><br><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></p>
                                                                    <p><strong>Tanggal Pengajuan:</strong><br><?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <p><strong>Alasan Pengajuan:</strong></p>
                                                                    <div class="border rounded p-3 bg-light">
                                                                        <?php echo nl2br(htmlspecialchars($row['alasan_pengajuan'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php if ($row['catatan_verifikasi']): ?>
                                                            <div class="row mt-3">
                                                                <div class="col-12">
                                                                    <p><strong>Catatan Verifikasi:</strong></p>
                                                                    <div class="border rounded p-3 bg-warning bg-opacity-10">
                                                                        <?php echo nl2br(htmlspecialchars($row['catatan_verifikasi'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                            <?php if ($row['catatan_keuangan']): ?>
                                                            <div class="row mt-3">
                                                                <div class="col-12">
                                                                    <p><strong>Catatan Keuangan:</strong></p>
                                                                    <div class="border rounded p-3 bg-info bg-opacity-10">
                                                                        <?php echo nl2br(htmlspecialchars($row['catatan_keuangan'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal Verifikasi -->
                                            <?php if (in_array($role, ['kepala', 'admin']) && $row['status'] == 'Diajukan'): ?>
                                            <div class="modal fade" id="verifikasiModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Verifikasi Pengajuan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Verifikasi</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="Diverifikasi">Setujui</option>
                                                                        <option value="Ditolak">Tolak</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan</label>
                                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Berikan catatan verifikasi..."></textarea>
                                                                </div>
                                                                <input type="hidden" name="pengajuan_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="verifikasi" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-warning">Verifikasi</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Modal Keuangan -->
                                            <?php if (in_array($role, ['keuangan', 'admin']) && $row['status'] == 'Diverifikasi'): ?>
                                            <div class="modal fade" id="keuanganModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Persetujuan Keuangan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                                                <p><strong>Perkiraan Harga:</strong> Rp <?php echo number_format($row['perkiraan_harga'], 0, ',', '.'); ?></p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Persetujuan</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="ACC Keuangan">Setujui</option>
                                                                        <option value="Ditolak Keuangan">Tolak</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan</label>
                                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Berikan catatan persetujuan..."></textarea>
                                                                </div>
                                                                <input type="hidden" name="pengajuan_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="acc_keuangan" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">Setujui</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada pengajuan barang</h5>
                                    <?php if (in_array($role, ['staff', 'admin'])): ?>
                                    <a href="pengajuan.php" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-2"></i>Buat Pengajuan Pertama
                                    </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include WhatsApp Modal -->
    <?php include 'includes/whatsapp_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script type="text/javascript">
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
        });
    </script>
</body>
</html>
