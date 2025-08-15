<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filter tahun
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Data inventaris per tahun
$sql_inventaris = "SELECT i.*, pb.nama_barang as nama_pengajuan, pb.spesifikasi as spesifikasi_pengajuan,
        GROUP_CONCAT(CONCAT(dd.divisi, ' (', dd.stok_divisi, ')') SEPARATOR ', ') as divisi_info
        FROM inventaris i 
        LEFT JOIN pengajuan_barang pb ON i.pengajuan_id = pb.id 
        LEFT JOIN distribusi_divisi dd ON i.id = dd.inventaris_id
        WHERE YEAR(i.created_at) = ?
        GROUP BY i.id
        ORDER BY i.created_at DESC";
$stmt_inventaris = $conn->prepare($sql_inventaris);
$stmt_inventaris->bind_param("i", $tahun);
$stmt_inventaris->execute();
$result_inventaris = $stmt_inventaris->get_result();

// Data kerusakan per tahun
$sql_kerusakan = "SELECT kb.*, i.nama_barang, i.nomor_seri
        FROM kerusakan_barang kb
        JOIN inventaris i ON kb.inventaris_id = i.id
        WHERE YEAR(kb.created_at) = ?
        ORDER BY kb.created_at DESC";
$stmt_kerusakan = $conn->prepare($sql_kerusakan);
$stmt_kerusakan->bind_param("i", $tahun);
$stmt_kerusakan->execute();
$result_kerusakan = $stmt_kerusakan->get_result();

// Data pemindahan per tahun
$sql_pemindahan = "SELECT pb.*, i.nama_barang, i.nomor_seri
        FROM pemindahan_barang pb
        JOIN inventaris i ON pb.inventaris_id = i.id
        WHERE YEAR(pb.created_at) = ?
        ORDER BY pb.created_at DESC";
$stmt_pemindahan = $conn->prepare($sql_pemindahan);
$stmt_pemindahan->bind_param("i", $tahun);
$stmt_pemindahan->execute();
$result_pemindahan = $stmt_pemindahan->get_result();

// Hitung total nilai inventaris
$sql_total = "SELECT SUM(i.harga * i.stok) as total_nilai, COUNT(*) as total_barang
        FROM inventaris i 
        WHERE YEAR(i.created_at) = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $tahun);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inventaris Barang IT - Tahun <?php echo $tahun; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
        }
        
        .letterhead {
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-right: 20px;
        }
        
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 50px auto 10px auto;
        }
        
        .table-responsive {
            margin-bottom: 30px;
        }
        
        .section-title {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 30px 0 20px 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="no-print">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar me-2"></i>Laporan Inventaris Barang IT</h2>
                <div>
                    <select class="form-select d-inline-block w-auto me-2" onchange="window.location.href='?tahun='+this.value">
                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $tahun ? 'selected' : ''; ?>>
                                Tahun <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak Laporan
                    </button>
                    <a href="inventaris.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Kop Surat -->
        <div class="letterhead">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="company-logo">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="col">
                    <h3 class="mb-1">PT. INVENTARIS IT INDONESIA</h3>
                    <p class="mb-1">Jl. Teknologi No. 123, Jakarta Selatan 12345</p>
                    <p class="mb-1">Telp: (021) 1234-5678 | Email: info@inventarisit.com</p>
                    <p class="mb-0">Website: www.inventarisit.com</p>
                </div>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-4">
            <h4>LAPORAN INVENTARIS BARANG IT</h4>
            <h5>Tahun <?php echo $tahun; ?></h5>
            <p class="text-muted">Periode: 1 Januari <?php echo $tahun; ?> - 31 Desember <?php echo $tahun; ?></p>
        </div>

        <!-- Ringkasan -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Barang</h5>
                        <h3 class="text-primary"><?php echo $row_total['total_barang'] ?: 0; ?></h3>
                        <p class="card-text">Item</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Nilai</h5>
                        <h3 class="text-success">Rp <?php echo number_format($row_total['total_nilai'] ?: 0, 0, ',', '.'); ?></h3>
                        <p class="card-text">Inventaris</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Kerusakan</h5>
                        <h3 class="text-warning"><?php echo $result_kerusakan->num_rows; ?></h3>
                        <p class="card-text">Laporan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Inventaris -->
        <div class="section-title">
            <h5><i class="fas fa-boxes me-2"></i>Data Inventaris Barang</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Nomor Seri</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Distribusi Divisi</th>
                        <th>Tanggal Pembelian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_inventaris->num_rows > 0):
                        $no = 1;
                        while ($row = $result_inventaris->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_seri'] ?: '-'); ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><span class="badge bg-primary"><?php echo $row['stok']; ?> Unit</span></td>
                        <td>
                            <?php
                            $status_class = '';
                            switch ($row['status']) {
                                case 'Tersedia': $status_class = 'bg-success'; break;
                                case 'Diserahkan': $status_class = 'bg-info'; break;
                                case 'Rusak': $status_class = 'bg-danger'; break;
                                case 'Dalam Perbaikan': $status_class = 'bg-warning'; break;
                                case 'Dipindahkan': $status_class = 'bg-primary'; break;
                                default: $status_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                        </td>
                        <td>
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
                                <span class="badge bg-secondary">Belum ada divisi</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['tanggal_pembelian'] ? date('d/m/Y', strtotime($row['tanggal_pembelian'])) : '-'; ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data inventaris untuk tahun <?php echo $tahun; ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Data Kerusakan -->
        <div class="section-title print-break">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Data Kerusakan Barang</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Nomor Seri</th>
                        <th>Jenis Kerusakan</th>
                        <th>Divisi Target</th>
                        <th>Tanggal Kerusakan</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_kerusakan->num_rows > 0):
                        $no = 1;
                        while ($row = $result_kerusakan->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_seri'] ?: '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $row['jenis_kerusakan'] == 'Dapat Diperbaiki' ? 'bg-warning' : 'bg-danger'; ?>">
                                <?php echo $row['jenis_kerusakan']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['divisi_target'] ?: '-'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_kerusakan'])); ?></td>
                        <td><?php echo htmlspecialchars($row['deskripsi_kerusakan']); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Tidak ada data kerusakan untuk tahun <?php echo $tahun; ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Data Pemindahan -->
        <div class="section-title print-break">
            <h5><i class="fas fa-exchange-alt me-2"></i>Data Pemindahan Barang</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Nomor Seri</th>
                        <th>Divisi Asal</th>
                        <th>Divisi Tujuan</th>
                        <th>Tanggal Pemindahan</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_pemindahan->num_rows > 0):
                        $no = 1;
                        while ($row = $result_pemindahan->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_seri'] ?: '-'); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($row['divisi_asal']); ?></span></td>
                        <td><span class="badge bg-success"><?php echo htmlspecialchars($row['divisi_tujuan']); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pemindahan'])); ?></td>
                        <td><?php echo htmlspecialchars($row['alasan_pemindahan']); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Tidak ada data pemindahan untuk tahun <?php echo $tahun; ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <p>Jakarta, <?php echo date('d F Y'); ?></p>
            <p>Kepala Ruangan IT,</p>
            <div class="signature-line"></div>
            <p><strong>Qhusnul Arienda</strong></p>
            <p>NIP. 123456789</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
