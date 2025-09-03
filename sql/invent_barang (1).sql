-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 07:46 AM
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

--
-- Dumping data for table `distribusi_divisi`
--

INSERT INTO `distribusi_divisi` (`id`, `inventaris_id`, `divisi`, `stok_divisi`, `created_at`, `updated_at`) VALUES
(1, 1, 'FRONT OFFICE', 1, '2022-12-14 01:50:15', '2022-12-14 01:50:15'),
(2, 2, 'RM', 1, '2022-12-14 02:50:15', '2022-12-14 02:50:15'),
(3, 3, 'Keuangan', 1, '2023-01-06 07:00:00', '2023-01-06 07:00:00'),
(4, 3, 'Manajemen', 2, '2023-01-06 07:00:00', '2023-01-06 07:00:00'),
(6, 4, 'Farmasi', 1, '2023-01-06 07:00:00', '2023-01-06 07:00:00'),
(7, 5, 'IGD', 1, '2023-01-27 03:41:04', '2023-01-27 03:41:04'),
(8, 6, 'IGD', 2, '2023-01-27 03:41:04', '2023-01-27 03:41:04'),
(9, 7, 'IGD', 1, '2023-01-27 05:41:04', '2023-01-27 05:41:04'),
(10, 8, 'Laboratorium', 2, '2023-01-27 05:41:04', '2023-01-27 05:41:04'),
(11, 9, 'IGD', 1, '2023-01-27 05:41:04', '2023-01-27 05:41:04'),
(12, 10, 'IGD', 1, '2023-02-10 08:09:02', '2023-02-10 08:09:02'),
(13, 11, 'FRONT OFFICE', 1, '2023-03-01 08:57:45', '2023-03-01 08:57:45'),
(14, 12, 'FRONT OFFICE', 1, '2023-03-21 06:15:46', '2023-03-21 06:15:46'),
(15, 13, 'FRONT OFFICE', 1, '2023-03-21 06:15:46', '2023-03-21 07:08:32'),
(16, 14, 'FRONT OFFICE', 1, '2023-03-21 06:15:46', '2023-03-21 06:15:46'),
(17, 15, 'FRONT OFFICE', 1, '2023-03-23 06:15:46', '2023-03-23 07:15:46'),
(18, 16, 'RM', 1, '2025-08-29 03:05:10', '2025-08-29 03:05:10'),
(19, 17, 'IT', 1, '2025-09-02 06:44:54', '2025-09-02 06:44:54'),
(20, 18, 'IT', 1, '2025-09-02 08:06:58', '2025-09-02 08:06:58'),
(21, 19, 'IT', 1, '2024-05-27 02:01:49', '2024-05-27 02:01:49'),
(22, 20, 'Radiologi', 1, '2025-09-03 03:21:36', '2025-09-03 03:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `no_inventaris` varchar(100) DEFAULT NULL,
  `pengajuan_id` int(11) DEFAULT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `jenis_barang` enum('Komputer & Laptop','Komponen Komputer & Laptop','Printer & Scanner','Komponen Printer & Scanner','Komponen Network') NOT NULL,
  `nomor_seri` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `tanggal_pembelian` date DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `status` enum('Tersedia','Diserahkan','Rusak','Dalam Perbaikan','Dipindahkan','Disposisi') DEFAULT 'Tersedia',
  `tanggal_penyerahan` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stok` int(11) NOT NULL,
  `catatan_penyerahan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventaris`
--

INSERT INTO `inventaris` (`id`, `no_inventaris`, `pengajuan_id`, `nama_barang`, `spesifikasi`, `jenis_barang`, `nomor_seri`, `ip_address`, `tanggal_pembelian`, `harga`, `status`, `tanggal_penyerahan`, `created_at`, `stok`, `catatan_penyerahan`) VALUES
(1, '002/IT/LOG/RSPI/2022', 2, 'UPS', '-', 'Komponen Komputer & Laptop', '-', NULL, '2022-12-14', 0.00, 'Diserahkan', NULL, '2022-12-14 01:50:15', 0, '0'),
(2, '001/IT/LOG/RSPI/2022', 1, 'Printer Epson L121', '• Print Speed : Black 8.5 ipm, Color 4.5 ipm\r\n\r\n• Resolution : 720 x 720 dpi\r\n\r\n• Max Media Size : A4\r\n\r\n• Input Tray : 50 sheets, A4 Plain paper (75g/m²)\r\n\r\n• Power Consumption : Printing 10W, Standby 2.0W, Sleep 0.6W, Power Off 0.3W', 'Printer & Scanner', '-', NULL, '2022-12-14', 1600000.00, 'Diserahkan', NULL, '2022-12-14 02:50:15', 0, '0'),
(3, '002/IT/LOG/RSPI/2023', 4, 'Uninterruptible Power Supply (UPS)', '-', 'Komponen Komputer & Laptop', '-', NULL, '2023-01-06', 0.00, 'Diserahkan', NULL, '2023-01-06 07:00:00', 0, '0'),
(4, '001/IT/LOG/RSPI/2023', 3, 'POWER SUPLLY', '-', 'Komponen Komputer & Laptop', '-', NULL, '2023-01-06', 0.00, 'Diserahkan', NULL, '2023-01-06 07:00:00', 0, '0'),
(5, '003/IT/LOG/RSPI/2023', 5, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Pentium® G3260 3,30 GHz, RAM 4 GB', 'Komputer & Laptop', '-', '192.168.1.225', '2023-01-27', 0.00, 'Diserahkan', NULL, '2023-01-27 03:41:04', 0, '0'),
(6, '004/IT/LOG/RSPI/2023', 6, 'Monitor PC', '-', 'Komponen Komputer & Laptop', '-', NULL, '2023-01-27', 0.00, 'Diserahkan', NULL, '2023-01-27 03:41:04', 0, '0'),
(7, '005/IT/LOG/RSPI/2023', 7, 'Printer Epson L3210', 'Printer Type:\r\n\r\nPrint, Scan, Copy\r\n\r\n\r\n\r\nPrint Method:\r\n\r\nOn-demand inkjet (Piezoelectric)\r\n\r\nPrinter Language:\r\n\r\nESC/P-R, ESC/P Raster\r\n\r\nNozzle Configuration:\r\n\r\n180 x 1 nozzles Black, 59 x 1 nozzles per Colour (Cyan, Magenta, Yellow)\r\n\r\nMaximum Resolution:\r\n\r\n5760 x 1440 dpi\r\n\r\nAutomatic 2-sided Printing:\r\n\r\nNo\r\n\r\n\r\n\r\nPhoto Default - 10 x 15 cm / 4 x 6 \" *1:\r\n\r\nApprox. 69 sec per photo (Border) / 90 sec per photo (Borderless)*2\r\n\r\nDraft, A4 (Black / Colour):\r\n\r\nUp to 33.0 ppm / 15.0 ppm*2\r\n\r\nISO 24734, A4 Simplex (Black / Colour):\r\n\r\nUp to 10.0 ipm / 5.0 ipm*2\r\n\r\nFirst Page Out Time from Ready Mode (Black / Colour):\r\n\r\nApprox. 10 sec / 16 sec*2\r\n\r\n\r\n\r\nMaximum Copies from Standalone:\r\n\r\n20 copies\r\n\r\nMaximum Copy Resolution:\r\n\r\n600 x 600 dpi\r\n\r\nMaximum Copy Size:\r\n\r\nA4, Letter\r\n\r\nISO 29183, A4 Simplex (Black / Colour):\r\n\r\nUp to 7.0 ipm / 1.7 ipm\r\n\r\n\r\n\r\nScanner Type:\r\n\r\nFlatbed colour image scanner\r\n\r\nSensor Type:\r\n\r\nCIS\r\n\r\nOptical Resolution:\r\n\r\n600 x 1200 dpi\r\n\r\nMaximum Scan Area:\r\n\r\n216 x 297 mm\r\n\r\nScanner Bit Depth (Colour):\r\n\r\n48-bit input, 24-bit output\r\n\r\nScanner Bit Depth (Grayscale):\r\n\r\n16-bit input, 8-bit output\r\n\r\nScanner Bit Depth (Black & White):\r\n\r\n16-bit input, 1-bit output', 'Printer & Scanner', '-', NULL, '2023-01-27', 2200000.00, 'Diserahkan', NULL, '2023-01-27 05:41:04', 0, '0'),
(8, '007/IT/LOG/RSPI/2023', 9, 'Uninterruptible Power Supply (UPS)', '-', 'Komponen Komputer & Laptop', '-', NULL, '2023-01-30', 0.00, 'Diserahkan', NULL, '2023-01-27 05:41:04', 0, '0'),
(9, '006/IT/LOG/RSPI/2023', 8, 'Printer Epson Lx-310', 'Print Method : Impact dot matrix\r\n\r\nNumber of Pins in Head : 9 pins\r\n\r\nPrint Direction : Bi-direction with logic seeking\r\n\r\nControl Code : ESC/P and IBM PPDS emulation', 'Printer & Scanner', '-', NULL, '2023-01-30', 2000000.00, 'Diserahkan', NULL, '2023-01-27 05:41:04', 0, '0'),
(10, '008/IT/LOG/RSPI/2023', 10, 'Access Point', '-', 'Komponen Network', '-', NULL, '2023-01-30', 0.00, 'Diserahkan', NULL, '2023-01-27 05:41:04', 0, '0'),
(11, '009/IT/LOG/RSPI/2023', 11, 'PC Desktop (Unit CPU / PC Rakitan)', 'Komputer 1 :\nIntel® Core™ i3-2120 CPU 3.30 GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10\nKomputer 2 : \nIntel® G2030 CPU 3GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10\nKomputer 3 :\nIntel® Core™ i3-3210 CPU 3.20GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10\nKomputer 4 :\nIntel® Core™ i3-2100 CPU 3.10GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10\nKomputer 5 (Asuransi) :\nIntel® Core™ i3-2120 CPU 3.30 GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', 'FO 1(asuransi) :192.168.1.13', '2022-01-02', 0.00, 'Diserahkan', NULL, '2023-01-27 05:41:04', 0, '0'),
(12, '010/IT/LOG/RSPI/2023', 12, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® G2030 CPU 3GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', '192.168.1.60', '2023-02-28', 0.00, 'Diserahkan', NULL, '2023-02-28 05:46:24', 0, '0'),
(13, '011/IT/LOG/RSPI/2023', 13, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-3210 CPU 3.20GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', '192.168.1.235', '2023-02-28', 0.00, 'Diserahkan', NULL, '2023-02-28 05:46:24', 0, '0'),
(14, '012/IT/LOG/RSPI/2023', 14, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-2100 CPU 3.10GHz\r\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', '192.168.1.125', '2023-02-28', 0.00, 'Diserahkan', NULL, '2023-02-28 05:46:24', 0, '0'),
(15, '013/IT/LOG/RSPI/2023', 15, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-2120 CPU 3.30 GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', '192.168.1.51', '2023-02-28', 0.00, 'Diserahkan', NULL, '2023-02-28 05:46:24', 0, '0'),
(16, '014/IT/LOG/RSPI/2023', 16, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-3210 CPU 3.20GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer & Laptop', '-', '192168191', '2022-01-05', 0.00, 'Diserahkan', NULL, '2022-01-05 01:21:51', 0, '0'),
(17, '001/IT/LOG/RSPI/2025', 19, 'Webcame Logitech C270 HD 720p', 'Menggunakan webcam C270 anda akan mendapatkan panggilan video HD 720p dan foto 3 Mega-pixel. Built-in mikrofonnya menggunakan teknologi RightSound yang menghasilkan percakapan yang jernih tanpa noise latar belakang yang mengganggu. Dalam cahaya remang-remang C270 secara otomatis akan menyesuaikan gambar menjadi lebih baik berkat RightLight teknologi. Mendukung aplikasi Skype, Google Hangouts, Yahoo Messenger dan aplikasi pesan instan popular lainnya. Sistem operasi : Windows XP (SP2 atau lebih baru), Windows vista, Windows 7 (32 bit atau 64 bit), Windows 8 dan Windows10. Spesifikasi Teknik : Panggilan video HD (1280 x 720 piksel) dengan sistem yang direkomendasikan. Perekaman video: Hingga 1280 x 720 piksel. Foto: Hingga 3,0 megapiksel (ditingkatkan menggunakan software). Mikrofon bawaan dengan teknologi Logitech RightSound. Bersertifikat Hi-Speed USB 2.0 (direkomendasikan). Klip universal cocok dengan berbagai laptop, monitor LCD atau CRT. Dimensi kemasan : Tinggi x Lebar x Tebal (cm) : 21 x 16 x 9. Isi Kemasan : - Webcam dengan kabel sepanjang 150 cm. - Dokumentasi pengguna.', 'Komponen Komputer & Laptop', '-', NULL, '2025-03-01', 334000.00, 'Diserahkan', NULL, '2025-03-01 06:44:33', 0, '0'),
(18, '002/IT/LOG/RSPI/2025', 20, 'Laptop lenovo thinkpad', 'Intel® Core™ i5-8350U CPU 1.70GHz\nRAM 16 GB, Stroge 238 GB, Stystem 64-bit, Sistem Operasi WIndows 11', 'Komputer & Laptop', '-', '192.168.1.104', '2025-03-03', 0.00, 'Diserahkan', NULL, '2025-09-02 07:50:14', 0, '0'),
(19, '001/IT/LOG/RSPI/2024', 26, 'VivoBook Laptop ASUS Silver', 'Processor : 11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz (4 CPUs), ~3.0GHz\r\nRAM 8 GB, Storage 238 GB, Windows 11 Home-64-bit Operating System\r\n', 'Komputer & Laptop', 'R2N0CV06D544073', '192.168.1.95', '2024-05-27', 0.00, 'Diserahkan', NULL, '2024-05-27 02:59:24', 0, '0'),
(20, '003/IT/LOG/RSPI/2025', 27, 'TP-LINK TL-SG1008D 8-Port Gigabit Switch Desktop / Gigabit 8 Port - HITAM', 'HARDWARE FEATURES\r\nStandards and Protocols IEEE 802.3i/802.3u/ 802.3ab/802.3x\r\nInterface 8 10/100/1000Mbps RJ45 Ports\r\nAUTO Negotiation/AUTO MDI/MDIX\r\nFan Quantity Fanless\r\nPower Consumption Maximum: 4.63W (220V/50Hz)\r\nExternal Power Supply External Power Adapter (Output: 5VDC / 0.6A)\r\nJumbo Frame 15 KB\r\nSwitching Capacity 16 Gbps\r\nDimensions ( W x D x H ) 7.1 * 3.5 * 1.0 in. (180 * 90 * 25.5 mm)\r\nSOFTWARE FEATURES\r\nTransfer Method Store and Forward\r\nMAC Address Table 4K\r\nAdvanced Functions Green Technology, saving power up to 80%\r\n802.3X Flow Control, Back Pressure\r\nOTHERS\r\nCertification FCC, CE, RoHs\r\nPackage Contents 8-Port Gigabit Desktop Switch TL-SG1008D\r\nPower Adapter\r\nInstallation Guide\r\nEnvironment\r\nOperating Temperature: 0~40 (32~104); Storage Temperature: -40~70\r\n(-40~158); Operating Humidity: 10%~90% non-condensing; Storage Humidity:\r\n5%~90% non-condensing', 'Komponen Network', '224421L3026271', NULL, '2025-03-03', 286000.00, 'Diserahkan', NULL, '2025-09-03 03:20:47', 0, '0');

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

--
-- Dumping data for table `pemindahan_barang`
--

INSERT INTO `pemindahan_barang` (`id`, `inventaris_id`, `distribusi_id`, `divisi_asal`, `divisi_tujuan`, `tanggal_pemindahan`, `alasan_pemindahan`, `created_at`) VALUES
(1, 20, 22, 'IT', 'Radiologi', '2025-04-25', 'Memindah Koneksi', '2025-04-25 03:27:16');

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

--
-- Dumping data for table `pengajuan_barang`
--

INSERT INTO `pengajuan_barang` (`id`, `nama_barang`, `spesifikasi`, `alasan_pengajuan`, `jumlah`, `perkiraan_harga`, `status`, `staff_id`, `tanggal_pengajuan`, `tanggal_verifikasi`, `tanggal_acc_keuangan`, `catatan_verifikasi`, `catatan_keuangan`) VALUES
(1, 'Printer Epson L121', '• Print Speed : Black 8.5 ipm, Color 4.5 ipm\r\n\r\n• Resolution : 720 x 720 dpi\r\n\r\n• Max Media Size : A4\r\n\r\n• Input Tray : 50 sheets, A4 Plain paper (75g/m²)\r\n\r\n• Power Consumption : Printing 10W, Standby 2.0W, Sleep 0.6W, Power Off 0.3W', 'Rm Membutuhkan Printer', 1, 1600000.00, 'Diterima', 1, '2022-12-06 01:14:10', '2022-12-07 01:15:52', '2022-12-12 01:16:10', 'ok', 'ok Silahkan beli'),
(2, 'UPS', '-', 'untuk display antrian poli', 1, 0.00, 'Diterima', 1, '2022-12-06 01:14:10', '2022-12-07 01:15:52', '2022-12-12 01:16:10', 'ok', 'silahkan beli'),
(3, 'POWER SUPLLY', '-', 'Untuk Farmasi di dalam', 1, 0.00, 'Diterima', 1, '2023-01-02 01:23:14', '2023-01-03 01:23:14', '2023-01-06 03:23:14', 'Oke, Diteruskan Ke kuangan', 'silahkan beli'),
(4, 'Uninterruptible Power Supply (UPS)', '-', 'untuk keuangan , manajemen', 3, 0.00, 'Diterima', 1, '2023-01-02 01:23:14', '2023-01-03 01:23:14', '2023-01-06 03:23:14', 'Oke, Diteruskan Ke kuangan', 'silahkan beli'),
(5, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Pentium® G3260 3,30 GHz, RAM 4 GB', 'untuk keperluan fo rawat inap', 1, 0.00, 'Diterima', 1, '2023-01-24 01:45:16', '2023-01-25 01:45:16', '2023-01-30 01:45:16', 'Oke, Diteruskan Ke kuangan', 'Oke'),
(6, 'Monitor PC', '-', 'untuk keperluan fo rawat inap', 2, 0.00, 'Diterima', 1, '2023-01-24 01:45:16', '2023-01-25 01:45:16', '2023-01-30 01:45:16', 'ACC, Diteruskan Ke kuangan', 'ok'),
(7, 'Printer Epson L3210', 'Printer Type:\r\n\r\nPrint, Scan, Copy\r\n\r\n\r\n\r\nPrint Method:\r\n\r\nOn-demand inkjet (Piezoelectric)\r\n\r\nPrinter Language:\r\n\r\nESC/P-R, ESC/P Raster\r\n\r\nNozzle Configuration:\r\n\r\n180 x 1 nozzles Black, 59 x 1 nozzles per Colour (Cyan, Magenta, Yellow)\r\n\r\nMaximum Resolution:\r\n\r\n5760 x 1440 dpi\r\n\r\nAutomatic 2-sided Printing:\r\n\r\nNo\r\n\r\n\r\n\r\nPhoto Default - 10 x 15 cm / 4 x 6 \" *1:\r\n\r\nApprox. 69 sec per photo (Border) / 90 sec per photo (Borderless)*2\r\n\r\nDraft, A4 (Black / Colour):\r\n\r\nUp to 33.0 ppm / 15.0 ppm*2\r\n\r\nISO 24734, A4 Simplex (Black / Colour):\r\n\r\nUp to 10.0 ipm / 5.0 ipm*2\r\n\r\nFirst Page Out Time from Ready Mode (Black / Colour):\r\n\r\nApprox. 10 sec / 16 sec*2\r\n\r\n\r\n\r\nMaximum Copies from Standalone:\r\n\r\n20 copies\r\n\r\nMaximum Copy Resolution:\r\n\r\n600 x 600 dpi\r\n\r\nMaximum Copy Size:\r\n\r\nA4, Letter\r\n\r\nISO 29183, A4 Simplex (Black / Colour):\r\n\r\nUp to 7.0 ipm / 1.7 ipm\r\n\r\n\r\n\r\nScanner Type:\r\n\r\nFlatbed colour image scanner\r\n\r\nSensor Type:\r\n\r\nCIS\r\n\r\nOptical Resolution:\r\n\r\n600 x 1200 dpi\r\n\r\nMaximum Scan Area:\r\n\r\n216 x 297 mm\r\n\r\nScanner Bit Depth (Colour):\r\n\r\n48-bit input, 24-bit output\r\n\r\nScanner Bit Depth (Grayscale):\r\n\r\n16-bit input, 8-bit output\r\n\r\nScanner Bit Depth (Black & White):\r\n\r\n16-bit input, 1-bit output', 'untuk keperluan fo rawat inap', 1, 2200000.00, 'Diterima', 1, '2023-01-24 01:45:16', '2023-01-25 01:45:16', '2023-01-30 01:45:16', 'ACC, Diteruskan Ke kuangan', 'ok'),
(8, 'Printer Epson Lx-310', 'Print Method : Impact dot matrix\r\n\r\nNumber of Pins in Head : 9 pins\r\n\r\nPrint Direction : Bi-direction with logic seeking\r\n\r\nControl Code : ESC/P and IBM PPDS emulation', 'untuk keperluan fo rawat inap', 1, 2000000.00, 'Diterima', 1, '2023-01-25 01:45:16', '2023-01-25 02:45:16', '2023-01-30 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(9, 'Uninterruptible Power Supply (UPS)', '-', 'untuk alat Laboratorium dan komputer', 2, 0.00, 'Diterima', 1, '2023-01-25 01:45:16', '2023-01-25 02:45:16', '2023-01-30 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(10, 'Access Point', '-', 'untuk keperluan fo rawat inap', 1, 0.00, 'Diterima', 1, '2023-01-25 01:45:16', '2023-01-25 02:45:16', '2023-01-30 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(11, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-2120 CPU 3.30 GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10', 'Untuk Resepsionis (FO Ralan) 1 ', 1, 0.00, 'Diterima', 1, '2022-01-01 01:45:16', '2022-01-01 01:45:16', '2022-01-01 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(12, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® G2030 CPU 3GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10', 'Untuk Resepsionis (FO Ralan) 2', 1, 0.00, 'Diterima', 1, '2022-01-01 01:45:16', '2022-01-01 01:45:16', '2022-01-01 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(13, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-3210 CPU 3.20GHz\r\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Untuk Resepsionis (FO Ralan) 3', 1, 0.00, 'Diterima', 1, '2022-01-01 01:45:16', '2022-01-01 01:45:16', '2022-01-01 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(14, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-2100 CPU 3.10GHz\r\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Untuk Resepsionis (FO Ralan) 4', 1, 0.00, 'Diterima', 1, '2022-01-01 01:45:16', '2022-01-01 01:45:16', '2022-01-01 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(15, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-2120 CPU 3.30 GHz\nRAM 4 GB, Stystem 64-bit, Sistem Operasi WIndows 10', 'Untuk Resepsionis (FO Ralan) 5', 1, 0.00, 'Diterima', 1, '2022-01-01 01:45:16', '2022-01-01 01:45:16', '2022-01-01 01:45:16', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(16, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Core™ i3-3210 CPU 3.20GHz\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer RM (Purwanto)', 1, 0.00, 'Diterima', 1, '2022-01-03 08:10:42', '2022-01-04 05:10:42', '2022-01-05 08:10:42', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(17, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Pentium™ G630 CPU 2.76GHz\r\nRAM 4 GB, Stystem 32-bit, Sistem Operasi WIndows 10', 'Komputer RM (Rima)', 1, 0.00, 'ACC Keuangan', 1, '2022-01-03 08:10:42', '2022-01-04 05:10:42', '2022-01-05 08:10:42', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(18, 'PC Desktop (Unit CPU / PC Rakitan)', 'Intel® Pentium™ G630 CPU 2.76GHz\r\nRAM 4 GB, Sistem Operasi 64-bit Win 10', 'Untuk RM mba rima', 1, 0.00, 'ACC Keuangan', 1, '2022-01-03 08:10:42', '2022-01-04 05:10:42', '2022-01-05 08:10:42', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(19, 'Webcame Logitech C270 HD 720p', 'Menggunakan webcam C270 anda akan mendapatkan panggilan video HD 720p dan foto 3 Mega-pixel. Built-in mikrofonnya menggunakan teknologi RightSound yang menghasilkan percakapan yang jernih tanpa noise latar belakang yang mengganggu. Dalam cahaya remang-remang C270 secara otomatis akan menyesuaikan gambar menjadi lebih baik berkat RightLight teknologi. Mendukung aplikasi Skype, Google Hangouts, Yahoo Messenger dan aplikasi pesan instan popular lainnya. Sistem operasi : Windows XP (SP2 atau lebih baru), Windows vista, Windows 7 (32 bit atau 64 bit), Windows 8 dan Windows10. Spesifikasi Teknik : Panggilan video HD (1280 x 720 piksel) dengan sistem yang direkomendasikan. Perekaman video: Hingga 1280 x 720 piksel. Foto: Hingga 3,0 megapiksel (ditingkatkan menggunakan software). Mikrofon bawaan dengan teknologi Logitech RightSound. Bersertifikat Hi-Speed USB 2.0 (direkomendasikan). Klip universal cocok dengan berbagai laptop, monitor LCD atau CRT. Dimensi kemasan : Tinggi x Lebar x Tebal (cm) : 21 x 16 x 9. Isi Kemasan : - Webcam dengan kabel sepanjang 150 cm. - Dokumentasi pengguna.', 'Untuk penunjang Akreditasi', 1, 330000.00, 'Diterima', 1, '2025-02-18 06:35:53', '2025-02-20 06:39:32', '2025-02-27 06:39:42', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(20, 'Laptop', 'bebas', 'Untuk penunjang akreditasi dan untuk acara agenda2 rapat kedepannya', 1, 4000000.00, 'Diterima', 1, '2025-02-20 02:43:46', '2025-02-20 06:39:57', '2025-02-27 06:39:42', 'ACC, Diteruskan Ke kuangan', 'silahkan beli'),
(21, 'ADAPTOR LCD/LED MONITOR LG', 'Adaptor LCD/LED Monitor LG 19V - 0,84A Original\r\n\r\n- Adaptor model : LCAP42\r\n\r\n- Adaptor untuk LED Monitor/TV LG 19V /0,84A', 'Mengganti punya rizky', 1, 100000.00, 'Diajukan', 1, '2025-09-03 02:21:13', NULL, NULL, NULL, NULL),
(22, 'RAM 8 GB DDR 4 Merek KingSton dan V-Gen', 'DDR4 8GB PC 21300 / 2666MHz', 'Upgrade PC Desktop (Unit CPU / PC Rakitan) punya riyan it', 2, 225000.00, 'Diajukan', 1, '2025-09-03 02:25:53', NULL, NULL, NULL, NULL),
(23, 'V-GeN SSD', 'Speed : Read up to 500Mbps; Write up to 400Mbps\r\nDimensi : 100 x 70 x 6 mm\r\nInterface : SATA 3 - 6GB/s\r\nForm Factor : 2.5 inch\r\nType : Internal Storage\r\nSupported : UDMA Mode 6\r\nTRIM Support : Yes (Requires OS Support)\r\nGarbage Collection : Yes\r\nS.M.A.R.T : Yes\r\nWrite Cache : Yes\r\nHost Protect Area : Yes\r\nAPM : Yes\r\nNCQ : Yes\r\n48-Bit : Yes\r\nSecurity : AES 256-Bit Full Disk Encryption (FDE)\r\nTCG/Opal V2.0 , Encryption Drive (IEEE1667)\r\nVolume : +/- 20 gr', 'Upgrade PC Desktop (Unit CPU / PC Rakitan) punya riyan it', 1, 0.00, 'Diajukan', 1, '2025-09-03 02:28:56', NULL, NULL, NULL, NULL),
(24, 'POWER SUPLLY EZMAX 600W RGB 80 PLUS', '-', 'Untuk farmasi dan hadi it', 2, 0.00, 'Diajukan', 1, '2025-09-03 02:30:39', NULL, NULL, NULL, NULL),
(25, 'Solution Digital Persona U are U 4500 Free SDK', '- PC based\r\n- Need Komputer\r\n- Interface : USB Kabel\r\n- Free SDK : VB6, C++,C#, Java dan Linux', 'Untuk mengganti finger bpjs di fo ralan', 1, 0.00, 'Diajukan', 1, '2025-09-03 02:31:57', NULL, NULL, NULL, NULL),
(26, 'VivoBook Laptop ASUS Silver', 'Processor : 11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz (4 CPUs), ~3.0GHz\r\nRAM 8 GB, Storage 238 GB, Windows 11 Home-64-bit Operating System\r\n', 'Untuk penunjang kegiatan unit IT', 1, 0.00, 'Diterima', 1, '2024-02-01 02:53:31', '2024-02-13 01:54:49', '2024-05-14 02:55:11', 'ACC, diteruskan ke keuangan dulu', 'Silahkan Beli'),
(27, 'TP-LINK TL-SG1008D 8-Port Gigabit Switch Desktop / Gigabit 8 Port - HITAM', 'HARDWARE FEATURES\r\nStandards and Protocols IEEE 802.3i/802.3u/ 802.3ab/802.3x\r\nInterface 8 10/100/1000Mbps RJ45 Ports\r\nAUTO Negotiation/AUTO MDI/MDIX\r\nFan Quantity Fanless\r\nPower Consumption Maximum: 4.63W (220V/50Hz)\r\nExternal Power Supply External Power Adapter (Output: 5VDC / 0.6A)\r\nJumbo Frame 15 KB\r\nSwitching Capacity 16 Gbps\r\nDimensions ( W x D x H ) 7.1 * 3.5 * 1.0 in. (180 * 90 * 25.5 mm)\r\nSOFTWARE FEATURES\r\nTransfer Method Store and Forward\r\nMAC Address Table 4K\r\nAdvanced Functions Green Technology, saving power up to 80%\r\n802.3X Flow Control, Back Pressure\r\nOTHERS\r\nCertification FCC, CE, RoHs\r\nPackage Contents 8-Port Gigabit Desktop Switch TL-SG1008D\r\nPower Adapter\r\nInstallation Guide\r\nEnvironment\r\nOperating Temperature: 0~40 (32~104); Storage Temperature: -40~70\r\n(-40~158); Operating Humidity: 10%~90% non-condensing; Storage Humidity:\r\n5%~90% non-condensing', 'Untuk penunjang kegiatan Akreditasi dan untuk backup IT', 1, 286000.00, 'Diterima', 1, '2025-02-20 02:43:46', '2025-02-20 06:39:57', '2025-02-27 06:39:42', 'ACC, diteruskan ke keuangan dulu', 'Silahkan Beli');

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

--
-- Dumping data for table `penyerahan_barang`
--

INSERT INTO `penyerahan_barang` (`id`, `inventaris_id`, `divisi_tujuan`, `jumlah_serah`, `tanggal_penyerahan`, `catatan_penyerahan`, `created_at`) VALUES
(1, 1, 'FRONT OFFICE', 1, '2022-12-15', 'Running Display Antrian FO Rawat Jalan', '2022-12-15 01:52:26'),
(2, 2, 'RM', 1, '2022-12-15', 'sudah di serahkan ke RM ke Purwanto', '2022-12-15 01:52:26'),
(3, 3, 'Keuangan', 1, '2023-01-09', 'Sudah di serahkan dan dipakai oleh 2 orang sekaligus yaitu mba cindy dan mas hendra', '2023-01-09 02:28:46'),
(4, 3, 'Manajemen', 1, '2023-01-09', 'Sudah diserahkan dan di pakai oleh mba arienda dan mba yuna', '2023-01-09 02:28:46'),
(5, 3, 'Manajemen', 1, '2023-01-09', 'Diserahkan ke manajemen dan dipakai oleh mas surya dan mas yudi', '2023-01-09 02:28:46'),
(6, 4, 'Farmasi', 1, '2023-01-09', 'Untuk Komputer farmasi di bagian dalam', '2023-01-09 05:08:46'),
(7, 5, 'IGD', 1, '2023-02-01', 'Untuk Keperluan Pendaftaran FO UDG Baru', '2023-02-01 02:28:46'),
(8, 6, 'IGD', 2, '2023-02-01', 'Untuk Keperluan Pendaftaran FO UDG Baru', '2023-02-01 02:28:46'),
(9, 7, 'IGD', 1, '2023-02-01', 'Untuk Keperluan Pendaftaran FO UDG Baru', '2025-08-22 04:09:13'),
(10, 8, 'Laboratorium', 2, '2023-02-01', 'Untuk Keperluan Komputer Lab, Dan Diterima Oleh Heni', '2025-08-27 07:45:34'),
(11, 9, 'IGD', 1, '2023-02-01', 'Sudah Diteruskan Oleh Orang IGD', '2025-08-27 07:49:04'),
(12, 10, 'IGD', 1, '2023-02-01', 'Untuk Keperluan Pendaftaran FO UDG Baru, dan diserahkan oleh Adi', '2025-08-27 08:09:02'),
(13, 11, 'FRONT OFFICE', 1, '2023-03-01', 'sudah diserahkan dan diterima oleh Adi sebagai kepala ruangan', '2023-03-01 02:00:00'),
(14, 12, 'FRONT OFFICE', 1, '2023-03-21', 'sudah dipasang di FO no 2 diterima oleh Adi sebagai kepala ruangan', '2023-03-21 06:15:46'),
(15, 13, 'FRONT OFFICE', 1, '2023-03-21', 'sudah dipasang di FO no 3 diterima oleh Adi sebagai kepala ruangan', '2023-03-21 07:08:32'),
(16, 14, 'FRONT OFFICE', 1, '2023-03-21', 'sudah dipasang di FO no 4 diterima oleh Adi sebagai kepala ruangan', '2023-03-21 06:15:46'),
(17, 15, 'FRONT OFFICE', 1, '2023-03-23', 'sudah dipasang di FO no 5 diterima oleh Adi sebagai kepala ruangan', '2023-03-23 07:15:32'),
(18, 16, 'RM', 1, '2022-01-10', 'sudah diserahkan dengan purwanto sebagai kepala ruangan RM', '2025-08-29 03:05:10'),
(19, 17, 'IT', 1, '2025-03-01', 'Untuk Penunjang Akreditasi dan selanjutnya diserahkan ke unit IT untuk Acara rapat zoom', '2025-09-02 06:44:54'),
(20, 18, 'IT', 1, '2025-03-03', 'Untuk penunjang akreditasi dan untuk acara agenda2 rapat kedepannya', '2025-09-02 08:06:58'),
(21, 19, 'IT', 1, '2024-05-27', 'Diterima oleh Arien sebagai kepala unit IT dan digunakan untuk penunjang kegiatan unit IT', '2024-05-27 02:01:49'),
(22, 20, 'IT', 1, '2025-03-03', 'Diserahkan oleh riyan IT untuk penunjang kegiatan Akreditasi dan untuk backup IT', '2025-09-03 03:21:36');

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
(1, 'staff', 'staff', 'Staff IT', 'staff', '2025-08-12 02:02:45'),
(2, 'arien', 'arien123', 'Kepala Ruangan IT', 'kepala', '2025-08-12 02:02:45'),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `kerusakan_barang`
--
ALTER TABLE `kerusakan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemindahan_barang`
--
ALTER TABLE `pemindahan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `penyerahan_barang`
--
ALTER TABLE `penyerahan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
