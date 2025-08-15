<?php
require_once 'database.php';

echo "<h2>Update Database Inventaris Barang</h2>";

// Update tabel pengajuan_barang - tambah kolom jumlah
$sql = "ALTER TABLE pengajuan_barang ADD COLUMN IF NOT EXISTS jumlah INT(11) DEFAULT 1 AFTER perkiraan_harga";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Kolom 'jumlah' berhasil ditambahkan ke tabel pengajuan_barang</p>";
} else {
    echo "<p>❌ Error menambahkan kolom 'jumlah': " . $conn->error . "</p>";
}

// Update tabel inventaris - tambah kolom stok
$sql = "ALTER TABLE inventaris ADD COLUMN IF NOT EXISTS stok INT(11) DEFAULT 0 AFTER harga";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Kolom 'stok' berhasil ditambahkan ke tabel inventaris</p>";
} else {
    echo "<p>❌ Error menambahkan kolom 'stok': " . $conn->error . "</p>";
}

// Hapus kolom status di kerusakan_barang jika masih ada (kompatibel MariaDB 10.4)
$check = $conn->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'kerusakan_barang' AND COLUMN_NAME = 'status'");
$check->bind_param("s", $database);
$check->execute();
$res = $check->get_result()->fetch_assoc();
if ((int)$res['cnt'] > 0) {
    $sql = "ALTER TABLE kerusakan_barang DROP COLUMN status";
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Kolom 'status' di tabel kerusakan_barang dihapus</p>";
    } else {
        echo "<p>❌ Error menghapus kolom 'status': " . $conn->error . "</p>";
    }
} else {
    echo "<p>ℹ️ Kolom 'status' sudah tidak ada di tabel kerusakan_barang</p>";
}

$sql = "ALTER TABLE kerusakan_barang ADD COLUMN IF NOT EXISTS jumlah_rusak INT(11) DEFAULT 1 AFTER deskripsi_kerusakan";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Kolom 'jumlah_rusak' berhasil ditambahkan ke tabel kerusakan_barang</p>";
} else {
    echo "<p>❌ Error menambahkan kolom 'jumlah_rusak': " . $conn->error . "</p>";
}

// Update stok yang sudah ada (set default 1 jika belum ada)
$sql = "UPDATE inventaris SET stok = 1 WHERE stok = 0 OR stok IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Stok default berhasil diupdate untuk data yang sudah ada</p>";
} else {
    echo "<p>❌ Error update stok default: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<h3>Kelengkapan Tabel Tambahan</h3>";

// Tabel penyerahan_barang
$sql = "CREATE TABLE IF NOT EXISTS penyerahan_barang (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    inventaris_id INT(11) NOT NULL,
    divisi_tujuan VARCHAR(100) NOT NULL,
    jumlah_serah INT(11) NOT NULL,
    tanggal_penyerahan DATE NOT NULL,
    catatan_penyerahan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventaris_id) REFERENCES inventaris(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Tabel 'penyerahan_barang' tersedia</p>";
} else {
    echo "<p>❌ Error membuat tabel 'penyerahan_barang': " . $conn->error . "</p>";
}

// Tabel distribusi_divisi
$sql = "CREATE TABLE IF NOT EXISTS distribusi_divisi (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    inventaris_id INT(11) NOT NULL,
    divisi VARCHAR(100) NOT NULL,
    stok_divisi INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inventaris_id) REFERENCES inventaris(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inventaris_divisi (inventaris_id, divisi)
)";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Tabel 'distribusi_divisi' tersedia</p>";
} else {
    echo "<p>❌ Error membuat tabel 'distribusi_divisi': " . $conn->error . "</p>";
}

// Pastikan pemindahan_barang tidak punya jumlah_pindah dan punya distribusi_id + FK
$hasJumlahPindah = $conn->query("SHOW COLUMNS FROM pemindahan_barang LIKE 'jumlah_pindah'");
if ($hasJumlahPindah && $hasJumlahPindah->num_rows > 0) {
    if ($conn->query("ALTER TABLE pemindahan_barang DROP COLUMN jumlah_pindah") === TRUE) {
        echo "<p>✅ Kolom 'jumlah_pindah' dihapus dari pemindahan_barang</p>";
    } else {
        echo "<p>❌ Error menghapus kolom 'jumlah_pindah': " . $conn->error . "</p>";
    }
}

$hasDistribusiId = $conn->query("SHOW COLUMNS FROM pemindahan_barang LIKE 'distribusi_id'");
if ($hasDistribusiId && $hasDistribusiId->num_rows === 0) {
    if ($conn->query("ALTER TABLE pemindahan_barang ADD COLUMN distribusi_id INT(11) NULL AFTER inventaris_id") === TRUE) {
        echo "<p>✅ Kolom 'distribusi_id' ditambahkan</p>";
        $conn->query("ALTER TABLE pemindahan_barang ADD CONSTRAINT fk_pemindahan_distribusi FOREIGN KEY (distribusi_id) REFERENCES distribusi_divisi(id) ON DELETE SET NULL");
        echo "<p>✅ Relasi ke 'distribusi_divisi' dipastikan ada</p>";
    } else {
        echo "<p>❌ Error menambahkan kolom 'distribusi_id': " . $conn->error . "</p>";
    }
} else {
    echo "<p>ℹ️ Kolom 'distribusi_id' sudah ada pada pemindahan_barang</p>";
}

echo "<hr>";
echo "<h3>Update Database Selesai!</h3>";
echo "<p>Database telah berhasil diupdate (stok, jumlah, distribusi divisi, penyerahan, penyesuaian kerusakan, dan relasi pemindahan).</p>";
echo "<p><a href='../dashboard.php'>Kembali ke Dashboard</a></p>";
?>

