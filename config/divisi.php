<?php
// List divisi untuk sistem inventaris
$list_divisi = [
    'Manajemen',
    'Keuangan', 
    'SDM',
    'Pelayanan',
    'Rawat Inap',
    'Rawat Jalan',
    'IGD',
    'Farmasi',
    'Laboratorium',
    'Radiologi',
    'Gizi',
    'CSSD',
    'Laundry',
    'Housekeeping',
    'Security',
    'IT',
    'Lainnya'
];

// Function untuk mendapatkan list divisi
function getListDivisi() {
    global $list_divisi;
    return $list_divisi;
}

// Function untuk validasi divisi
function isValidDivisi($divisi) {
    global $list_divisi;
    return in_array($divisi, $list_divisi);
}
?>

