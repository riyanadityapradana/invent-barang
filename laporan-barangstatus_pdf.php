<?php
// laporan-barangstatus_pdf.php
// Generate PDF laporan data barang berdasarkan status dan tahun

ob_start();
require_once('config/database.php');
require_once('library/tcpdf/tcpdf.php');

$status = isset($_GET['status']) ? $_GET['status'] : '';
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

if (!$status || !$tahun) {
    die('Parameter status dan tahun wajib diisi!');
}

// Query data inventaris sesuai filter tahun

if ($status === 'Tersedia') {
    $sql = "SELECT * FROM inventaris WHERE (status = 'Tersedia' OR status = 'Diserahkan' OR status = 'Dipindahkan') AND YEAR(created_at) = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $tahun);
} else {
    $sql = "SELECT * FROM inventaris WHERE status = ? AND YEAR(created_at) = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $tahun);
}
$stmt->execute();
$result = $stmt->get_result();

// Mulai PDF
class MYPDF extends TCPDF {
    public function Header() {
        // Landscape: lebar A4 = 297mm
        $this->Image('img/logo.jpg', 15, 8, 32);
        $this->SetFont('helvetica', 'B', 18);
        $this->Cell(0, 12, 'PT. PELITA INSANI MULIA', 0, 1, 'C');
        $this->SetFont('helvetica', '', 14);
        $this->Cell(0, 8, 'RUMAH SAKIT PELITA INSANI MARTAPURA', 0, 1, 'C');
        $this->SetFont('helvetica', '', 13);
        $this->Cell(0, 7, 'Terakreditasi KARS Versi SNARS Edisi 1 Tingkat Madya', 0, 1, 'C');
        $this->Image('img/bintang.png', 208, 20, 22); // Geser ke kanan, lebih besar
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 7, 'Jl. Sekumpul No. 66 Martapura - Telp. (0511) 4722210, 4722220, Kalimantan Selatan', 0, 1, 'C');
        $html = '<span style="color:black;">Fax. (0511) 4722230, </span><span style="color:red;">Emergency Call (0511) 4722222</span> <span>Email: </span><span style="color:blue;">rs.pelitainsani@gmail.com</span>';
        $this->writeHTMLCell(0, 7, '', '', $html, 0, 1, false, true, 'C', true);
        $this->Cell(0, 7, 'Website: www.pelitainsani.com', 0, 1, 'C');
        $this->Ln(4);
        $this->Line(15, 48, 282, 48); // garis bawah kop, lebar landscape
        $this->Ln(7);
    }
}

$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 50, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);


$html = '<h2 style="text-align:center;">LAPORAN DATA BARANG - TAHUN : '. htmlspecialchars($tahun) . '</h2>';

$html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%; font-size:12px; border-collapse:collapse;">';
$html .= '<thead>';
$html .= '<tr style="background-color:#f2f2f2;">';
$html .= '<th width="4%" style="text-align:center; vertical-align:middle;">No</th>';
$html .= '<th width="18%" style="text-align:center; vertical-align:middle;">No Inventaris</th>';
$html .= '<th width="20%" style="text-align:center; vertical-align:middle;">Nama Barang</th>';
$html .= '<th width="10%" style="text-align:center; vertical-align:middle;">Nomor Seri</th>';
$html .= '<th width="18%" style="text-align:center; vertical-align:middle;">Jenis Barang</th>';
$html .= '<th width="10%" style="text-align:center; vertical-align:middle;">Status</th>';
$html .= '<th width="22%" style="text-align:center; vertical-align:middle;">Spesifikasi</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';

$no = 1;

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td width="4%" style="text-align:center; vertical-align:middle;">' . $no++ . '</td>';
    $html .= '<td width="18%" style="text-align:center; vertical-align:middle;">' . htmlspecialchars($row['no_inventaris'] ?: '-') . '</td>';
    $html .= '<td width="20%" style="text-align:center; vertical-align:middle;">' . htmlspecialchars($row['nama_barang'] ?: '-') . '</td>';
    $html .= '<td width="10%" style="text-align:center; vertical-align:middle;">' . htmlspecialchars($row['nomor_seri'] ?: '-') . '</td>';
    $html .= '<td width="18%" style="text-align:center; vertical-align:middle;">' . htmlspecialchars($row['jenis_barang'] ?: '-') . '</td>';
    $html .= '<td width="10%" style="text-align:center; vertical-align:middle;">' . htmlspecialchars($row['status']) . '</td>';
    $html .= '<td width="22%" style="text-align:center; vertical-align:middle;">' . nl2br(htmlspecialchars($row['spesifikasi'] ?: '-')) . '</td>';
    $html .= '</tr>';
}

if ($no == 1) {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada data barang dengan status dan tahun tersebut.</td></tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Laporan_Barang_' . $status . '_' . $tahun . '.pdf', 'I');
exit;
