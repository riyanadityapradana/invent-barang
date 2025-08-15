-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2025 at 03:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `invent_barang`
--

-- --------------------------------------------------------

--
-- Table structure for table `distribusi_divisi`
--

CREATE TABLE `distribusi_divisi` (
  `id` int(11) NOT NULL,
  `inventaris_id` int(11) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `stok_divisi` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) DEFAULT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `nomor_seri` varchar(100) DEFAULT NULL,
  `tanggal_pembelian` date DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `status` enum('Tersedia','Diserahkan','Rusak','Dalam Perbaikan','Dipindahkan','Disposisi') DEFAULT 'Tersedia',
  `tanggal_penyerahan` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stok` int(11) NOT NULL,
  `catatan_penyerahan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kerusakan_barang`
--

CREATE TABLE `kerusakan_barang` (
  `id` int(11) NOT NULL,
  `inventaris_id` int(11) DEFAULT NULL,
  `tanggal_kerusakan` date NOT NULL,
  `jenis_kerusakan` enum('Dapat Diperbaiki','Tidak Dapat Diperbaiki') NOT NULL,
  `deskripsi_kerusakan` text DEFAULT NULL,
  `divisi_target` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemindahan_barang`
--

CREATE TABLE `pemindahan_barang` (
  `id` int(11) NOT NULL,
  `inventaris_id` int(11) DEFAULT NULL,
  `distribusi_id` int(11) DEFAULT NULL,
  `divisi_asal` varchar(100) NOT NULL,
  `divisi_tujuan` varchar(100) NOT NULL,
  `tanggal_pemindahan` date NOT NULL,
  `alasan_pemindahan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_barang`
--

CREATE TABLE `pengajuan_barang` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `alasan_pengajuan` text NOT NULL,
  `jumlah` int(11) NOT NULL,
  `perkiraan_harga` decimal(15,2) DEFAULT NULL,
  `status` enum('Diajukan','Diverifikasi','Ditolak','ACC Keuangan','Ditolak Keuangan','Dibeli','Diterima') DEFAULT 'Diajukan',
  `staff_id` int(11) DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_verifikasi` timestamp NULL DEFAULT NULL,
  `tanggal_acc_keuangan` timestamp NULL DEFAULT NULL,
  `catatan_verifikasi` text DEFAULT NULL,
  `catatan_keuangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penyerahan_barang`
--

CREATE TABLE `penyerahan_barang` (
  `id` int(11) NOT NULL,
  `inventaris_id` int(11) NOT NULL,
  `divisi_tujuan` varchar(100) NOT NULL,
  `jumlah_serah` int(11) NOT NULL,
  `tanggal_penyerahan` date NOT NULL,
  `catatan_penyerahan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('staff','kepala','keuangan','ob','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `created_at`) VALUES
(1, 'staff', 'staff123', 'Staff IT Demo', 'staff', '2025-08-12 02:02:45'),
(2, 'kepala', 'kepala123', 'Kepala Ruangan IT', 'kepala', '2025-08-12 02:02:45'),
(3, 'keuangan', 'keuangan123', 'Bagian Keuangan', 'keuangan', '2025-08-12 02:02:45'),
(4, 'ob', 'ob123', 'Office Boy', 'ob', '2025-08-12 02:02:45'),
(5, 'admin', 'admin123', 'Administrator', 'admin', '2025-08-12 02:02:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `distribusi_divisi`
--
ALTER TABLE `distribusi_divisi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inventaris_divisi` (`inventaris_id`,`divisi`);

--
-- Indexes for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indexes for table `kerusakan_barang`
--
ALTER TABLE `kerusakan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaris_id` (`inventaris_id`);

--
-- Indexes for table `pemindahan_barang`
--
ALTER TABLE `pemindahan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaris_id` (`inventaris_id`),
  ADD KEY `fk_pemindahan_distribusi` (`distribusi_id`);

--
-- Indexes for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `penyerahan_barang`
--
ALTER TABLE `penyerahan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaris_id` (`inventaris_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `distribusi_divisi`
--
ALTER TABLE `distribusi_divisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kerusakan_barang`
--
ALTER TABLE `kerusakan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pemindahan_barang`
--
ALTER TABLE `pemindahan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `penyerahan_barang`
--
ALTER TABLE `penyerahan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `distribusi_divisi`
--
ALTER TABLE `distribusi_divisi`
  ADD CONSTRAINT `distribusi_divisi_ibfk_1` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `inventaris_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_barang` (`id`);

--
-- Constraints for table `kerusakan_barang`
--
ALTER TABLE `kerusakan_barang`
  ADD CONSTRAINT `kerusakan_barang_ibfk_1` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`);

--
-- Constraints for table `pemindahan_barang`
--
ALTER TABLE `pemindahan_barang`
  ADD CONSTRAINT `fk_pemindahan_distribusi` FOREIGN KEY (`distribusi_id`) REFERENCES `distribusi_divisi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pemindahan_barang_ibfk_1` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`);

--
-- Constraints for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  ADD CONSTRAINT `pengajuan_barang_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `penyerahan_barang`
--
ALTER TABLE `penyerahan_barang`
  ADD CONSTRAINT `penyerahan_barang_ibfk_1` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
