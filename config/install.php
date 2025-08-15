<?php
/**
 * File Installer untuk Sistem Inventaris Barang IT
 * Jalankan file ini sekali saja untuk setup awal
 */

echo "<h2>Installer Sistem Inventaris Barang IT</h2>";
echo "<p>Memulai proses instalasi...</p>";

// Include database config
require_once 'database.php';

echo "<p>✅ Database berhasil dibuat</p>";
echo "<p>✅ Tabel-tabel berhasil dibuat</p>";
echo "<p>✅ User demo berhasil ditambahkan</p>";

echo "<hr>";
echo "<h3>Kredensial Login Demo:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Role</th><th>Username</th><th>Password</th></tr>";
echo "<tr><td>Staff IT</td><td>staff</td><td>staff123</td></tr>";
echo "<tr><td>Kepala Ruangan</td><td>kepala</td><td>kepala123</td></tr>";
echo "<tr><td>Keuangan</td><td>keuangan</td><td>keuangan123</td></tr>";
echo "<tr><td>OB</td><td>ob</td><td>ob123</td></tr>";
echo "<tr><td>Admin</td><td>admin</td><td>admin123</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>Langkah Selanjutnya:</h3>";
echo "<ol>";
echo "<li>Hapus file <code>config/install.php</code> ini untuk keamanan</li>";
echo "<li>Buka <a href='../index.php'>halaman login</a></li>";
echo "<li>Login dengan salah satu kredensial di atas</li>";
echo "<li>Mulai menggunakan sistem</li>";
echo "</ol>";

echo "<p><strong>⚠️ PENTING:</strong> Hapus file ini setelah instalasi selesai!</p>";
echo "<p><a href='../index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Mulai Menggunakan Sistem</a></p>";
?>

