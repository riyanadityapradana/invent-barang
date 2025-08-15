<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Cek role (hanya admin yang bisa akses)
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';

$success = $error = '';

// Proses tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    
    // Cek username sudah ada atau belum
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $sql = "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $password, $nama, $role);
        
        if ($stmt->execute()) {
            $success = "User berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan user: " . $conn->error;
        }
    }
}

// Proses edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $sql = "UPDATE users SET nama = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama, $role, $password, $user_id);
    } else {
        $sql = "UPDATE users SET nama = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        $success = "User berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate user: " . $conn->error;
    }
}

// Proses hapus user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_user'])) {
    $user_id = $_POST['user_id'];
    
    // Cek apakah user sedang digunakan
    $sql = "SELECT COUNT(*) as count FROM pengajuan_barang WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $error = "User tidak dapat dihapus karena masih memiliki data pengajuan!";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "User berhasil dihapus!";
        } else {
            $error = "Gagal menghapus user: " . $conn->error;
        }
    }
}

// Ambil data users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Sistem Inventaris Barang IT</title>
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
        .role-badge {
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
                        
                        <a class="nav-link" href="pengajuan.php">
                            <i class="fas fa-plus-circle me-2"></i>Pengajuan Barang
                        </a>
                        
                        <a class="nav-link" href="daftar_pengajuan.php">
                            <i class="fas fa-list me-2"></i>Daftar Pengajuan
                        </a>
                        
                        <a class="nav-link" href="inventaris.php">
                            <i class="fas fa-boxes me-2"></i>Data Inventaris
                        </a>
                        
                        <a class="nav-link" href="laporan.php">
                            <i class="fas fa-chart-bar me-2"></i>Laporan
                        </a>
                        
                        <a class="nav-link active" href="users.php">
                            <i class="fas fa-users me-2"></i>Manajemen User
                        </a>
                        
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
                        <h2><i class="fas fa-users me-2"></i>Manajemen User</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                            <i class="fas fa-plus me-2"></i>Tambah User
                        </button>
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
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Username</th>
                                                <th>Nama</th>
                                                <th>Role</th>
                                                <th>Tanggal Dibuat</th>
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
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td>
                                                    <?php
                                                    $role_class = '';
                                                    switch ($row['role']) {
                                                        case 'admin':
                                                            $role_class = 'bg-danger';
                                                            break;
                                                        case 'kepala':
                                                            $role_class = 'bg-warning';
                                                            break;
                                                        case 'keuangan':
                                                            $role_class = 'bg-success';
                                                            break;
                                                        case 'staff':
                                                            $role_class = 'bg-primary';
                                                            break;
                                                        case 'ob':
                                                            $role_class = 'bg-info';
                                                            break;
                                                        default:
                                                            $role_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $role_class; ?> role-badge"><?php echo ucfirst($row['role']); ?></span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editUserModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#hapusUserModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Edit User -->
                                            <div class="modal fade" id="editUserModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Username</label>
                                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>" readonly>
                                                                    <small class="text-muted">Username tidak dapat diubah</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nama Lengkap</label>
                                                                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($row['nama']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Role</label>
                                                                    <select name="role" class="form-select" required>
                                                                        <option value="staff" <?php echo $row['role'] == 'staff' ? 'selected' : ''; ?>>Staff IT</option>
                                                                        <option value="kepala" <?php echo $row['role'] == 'kepala' ? 'selected' : ''; ?>>Kepala Ruangan</option>
                                                                        <option value="keuangan" <?php echo $row['role'] == 'keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                                                                        <option value="ob" <?php echo $row['role'] == 'ob' ? 'selected' : ''; ?>>OB</option>
                                                                        <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                                                    <input type="password" name="password" class="form-control" placeholder="Password baru">
                                                                </div>
                                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="edit_user" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-warning">Update User</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal Hapus User -->
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <div class="modal fade" id="hapusUserModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Konfirmasi Hapus User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah Anda yakin ingin menghapus user <strong><?php echo htmlspecialchars($row['nama']); ?></strong>?</p>
                                                                <p class="text-danger"><small>User yang sudah memiliki data pengajuan tidak dapat dihapus.</small></p>
                                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="hapus_user" value="1">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Hapus User</button>
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
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada user</h5>
                                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                                        <i class="fas fa-plus me-2"></i>Tambah User Pertama
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="tambahUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Pilih Role</option>
                                <option value="staff">Staff IT</option>
                                <option value="kepala">Kepala Ruangan</option>
                                <option value="keuangan">Keuangan</option>
                                <option value="ob">OB</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <input type="hidden" name="tambah_user" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

