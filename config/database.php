<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'invent_barang';

// Buat koneksi
$conn = new mysqli($host, $username, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // echo "Database berhasil dibuat atau sudah ada";
} else {
    echo "Error membuat database: " . $conn->error;
}

// Pilih database
$conn->select_db($database);

// Buat tabel users jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('staff', 'kepala', 'keuangan', 'ob', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    echo "Error membuat tabel users: " . $conn->error;
}

// Buat tabel pengajuan_barang jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS pengajuan_barang (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(200) NOT NULL,
    spesifikasi TEXT,
    alasan_pengajuan TEXT NOT NULL,
    perkiraan_harga DECIMAL(15,2),
    jumlah INT(11) DEFAULT 1,
    status ENUM('Diajukan', 'Diverifikasi', 'Ditolak', 'ACC Keuangan', 'Ditolak Keuangan', 'Dibeli', 'Diterima') DEFAULT 'Diajukan',
    staff_id INT(11),
    tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_verifikasi TIMESTAMP NULL,
    tanggal_acc_keuangan TIMESTAMP NULL,
    catatan_verifikasi TEXT,
    catatan_keuangan TEXT,
    FOREIGN KEY (staff_id) REFERENCES users(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error membuat tabel pengajuan_barang: " . $conn->error;
}

// Buat tabel inventaris jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS inventaris (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    pengajuan_id INT(11),
    nama_barang VARCHAR(200) NOT NULL,
    spesifikasi TEXT,
    nomor_seri VARCHAR(100),
    tanggal_pembelian DATE,
    harga DECIMAL(15,2),
    stok INT(11) DEFAULT 0,
    status ENUM('Tersedia', 'Diserahkan', 'Rusak', 'Dalam Perbaikan', 'Dipindahkan', 'Disposisi') DEFAULT 'Tersedia',
    divisi_tujuan VARCHAR(100),
    tanggal_penyerahan DATE,
    catatan_penyerahan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_barang(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error membuat tabel inventaris: " . $conn->error;
}

// Buat tabel kerusakan_barang jika belum ada (tanpa kolom status)
$sql = "CREATE TABLE IF NOT EXISTS kerusakan_barang (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    inventaris_id INT(11),
    tanggal_kerusakan DATE NOT NULL,
    jenis_kerusakan ENUM('Dapat Diperbaiki', 'Tidak Dapat Diperbaiki') NOT NULL,
    deskripsi_kerusakan TEXT,
    jumlah_rusak INT(11) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventaris_id) REFERENCES inventaris(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error membuat tabel kerusakan_barang: " . $conn->error;
}

// Buat tabel pemindahan_barang jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS pemindahan_barang (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    inventaris_id INT(11),
    divisi_asal VARCHAR(100) NOT NULL,
    divisi_tujuan VARCHAR(100) NOT NULL,
    tanggal_pemindahan DATE NOT NULL,
    alasan_pemindahan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventaris_id) REFERENCES inventaris(id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error membuat tabel pemindahan_barang: " . $conn->error;
}

// Insert data demo users jika belum ada
$sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $users = [
        ['staff', 'staff123', 'Staff IT Demo', 'staff'],
        ['kepala', 'kepala123', 'Kepala Ruangan IT', 'kepala'],
        ['keuangan', 'keuangan123', 'Bagian Keuangan', 'keuangan'],
        ['ob', 'ob123', 'Office Boy', 'ob'],
        ['admin', 'admin123', 'Administrator', 'admin']
    ];
    
    foreach ($users as $user) {
        $sql = "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $user[0], $user[1], $user[2], $user[3]);
        $stmt->execute();
    }
}
?>
