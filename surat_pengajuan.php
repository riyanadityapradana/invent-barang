<?php
ob_start();
require_once('config/database.php');
require_once('library/tcpdf/tcpdf.php');

// Ambil data barang dari database
$dataBarang = [];
$unit = '....................................................';
$tanggal = '....................................................';

if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
            $q = mysqli_query($conn, "
            SELECT pb.*, u.nama as staff_name 
            FROM pengajuan_barang pb 
            JOIN users u ON pb.staff_id = u.id 
            WHERE DATE(pb.tanggal_pengajuan) BETWEEN '$dari' AND '$sampai' AND pb.status = 'Diverifikasi' 
            ORDER BY pb.tanggal_pengajuan DESC
        ");
    while ($row = mysqli_fetch_assoc($q)) {
        $dataBarang[] = [
            'nama' => $row['nama_barang'],
            'satuan' => $row['spesifikasi'],
            'jumlah' => $row['jumlah'],
            'keterangan' => $row['alasan_pengajuan'],
            'harga' => $row['perkiraan_harga'],
            'unit' => $row['staff_name'],
            'tanggal' => $row['tanggal_pengajuan']
        ];
    }
    // Untuk header, ambil unit dan tanggal dari data pertama jika ada
    if (count($dataBarang) > 0) {
        $unit = $dataBarang[0]['unit'];
        $tanggal = date('d-m-Y', strtotime($dataBarang[0]['tanggal']));
    }
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = mysqli_query($conn, "
        SELECT pb.*, u.nama as staff_name 
        FROM pengajuan_barang pb 
        JOIN users u ON pb.staff_id = u.id 
        WHERE pb.id = '$id'
    ");
    if ($row = mysqli_fetch_assoc($q)) {
        $dataBarang[] = [
            'nama' => $row['nama_barang'],
            'satuan' => $row['spesifikasi'],
            'jumlah' => $row['jumlah'],
            'harga' => $row['perkiraan_harga'],
            'keterangan' => $row['alasan_pengajuan']
        ];
        $unit = $row['bidang_pengajuan'];
        $tanggal = $row['tanggal_pengajuan'] != '0000-00-00' ? date('d-m-Y', strtotime($row['tanggal_pengajuan'])) : '....................................................';
    }
}
// Tetap 10 baris minimal
while (count($dataBarang) < 10) {
    $dataBarang[] = ['nama' => '', 'satuan' => '', 'jumlah' => '', 'keterangan' => '', 'harga' => ''];
}

// Mulai PDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('img/logo.jpg', 8, 5, 32);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 10, 'PT. PELITA INSANI MULIA', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 5, 'RUMAH SAKIT PELITA INSANI MARTAPURA  ', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 5, 'Terakreditasi KARS Versi SNARS Edisi 1 Tingkat Madya', 0, 1, 'C');
        $this->Image('../../../assets/img/bintang.png', 160, 15 , 15);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Jl. Sekumpul No. 66 Martapura - Telp. (0511) 4722210, 4722220, Kalimantan Selatan', 0, 1, 'C');
        $html = '<span style="color:black;">Fax. (0511) 4722230, </span><span style="color:red;">Emergency Call (0511) 4722222</span> <span>Email: </span><span style="color:blue;">rs.pelitainsani@gmail.com</span>';
        $this->writeHTMLCell(0, 5, '', '', $html, 0, 1, false, true, 'C', true);
        $this->Cell(0, 5, 'Website: www.pelitainsani.com', 0, 1, 'C');
        $this->Ln(4);
        $this->Line(10, 40, 200, 40);
        $this->Ln(5);
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 50, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Judul
$html = '<h3 style="text-align:center;">SURAT PERMINTAAN BARANG (SPB)</h3>';
$html .= '<table cellpadding="2" style="margin-bottom:10px;">
<tr><td width="100">Unit / Bagian</td><td width="10">:</td><td width="200">'.$unit.'</td></tr>
<tr><td>Tanggal</td><td>:</td><td>'.$tanggal.'</td></tr>
</table>';
$html .= '<p>Melalui surat ini, Mohon disediakan alat/barang sebagai berikut :</p>';

// Tabel barang
$html .= '<table border="1" cellpadding="4" style="border-collapse:collapse; margin:auto;">
<tr style="background:#eee;">
    <th width="30" align="center">No.</th>
    <th width="180" align="center">NAMA BARANG</th>
    <th width="60" align="center">Satuan</th>
    <th width="60" align="center">Jumlah</th>
    <th width="180" align="center">Keterangan</th>
</tr>';
if (count(array_filter(array_column($dataBarang, 'nama'))) == 0) {
    $html .= '<tr><td colspan="5" align="center">Tidak ada data pengajuan pada tanggal tersebut.</td></tr>';
} else {
    for ($i=0; $i<count($dataBarang); $i++) {
        $no = $i+1;
        $row = $dataBarang[$i];
        $harga = isset($row['harga']) ? (float)$row['harga'] : 0;
        $keterangan = isset($row['keterangan']) ? $row['keterangan'] : '';
        $html .= '<tr>
            <td align="center">'.$no.'</td>
            <td>'.$row['nama'].'</td>
            <td align="center">'.$row['satuan'].'</td>
            <td align="center">'.$row['jumlah'].'</td>
            <td>'.$keterangan.'<p> Perkiraan Harga : Rp '.number_format($harga, 0, ',', '.').'</p></td>
        </tr>';
    }
}
$html .= '</table>';

$html .= '<br><br><br>';
$html .= '<table width="100%" style="font-size:11px;">
<tr>
    <td align="center">Mengetahui,</td>
    <td align="center">Ditujuakan Oleh,</td>
    <td align="center">Menyetujui,</td>
</tr>
<tr><td height="50"></td><td></td><td></td></tr>
<tr>
    <td align="center">(Ka Unit / Ruangan)</td>
    <td align="center">(........................................)</td>
    <td align="center">(Pimpinan / DIR / Keuangan)</td>
</tr>
</table>';

$html .= '<br><br><table style="font-size:10px;">

</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->IncludeJS('print();');
ob_clean();
$pdf->Output('lap_permintaan_barang.pdf', 'I'); 