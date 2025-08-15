# Sistem Inventaris Barang IT

Sistem manajemen inventaris barang untuk divisi IT dengan alur kerja yang terstruktur dari pengajuan hingga monitoring barang.

## 🚀 Fitur Utama

### 1. **Dashboard**
- Ringkasan jumlah pengajuan, inventaris, dan status barang
- Grafik status pengajuan
- Aktivitas terbaru

### 2. **Pengajuan Barang**
- Form pengajuan barang oleh Staff IT
- Input spesifikasi dan alasan pengajuan
- Perkiraan harga

### 3. **Verifikasi & Persetujuan**
- Verifikasi oleh Kepala Ruangan IT
- Persetujuan anggaran oleh Bagian Keuangan
- Catatan dan feedback untuk setiap tahap

### 4. **Manajemen Inventaris**
- Input barang ke inventaris setelah disetujui
- Penyerahan barang ke divisi
- Input kerusakan barang (dapat/tidak dapat diperbaiki)
- Pemindahan barang antar divisi

### 5. **Monitoring & Laporan**
- Status barang real-time
- Filter dan pencarian
- Laporan pengajuan dan inventaris

## 🏗️ Arsitektur Sistem

### **Database Structure**
```
users (id, username, password, nama, role, created_at)
├── pengajuan_barang (id, nama_barang, spesifikasi, alasan_pengajuan, perkiraan_harga, status, staff_id, tanggal_pengajuan, tanggal_verifikasi, tanggal_acc_keuangan, catatan_verifikasi, catatan_keuangan)
├── inventaris (id, pengajuan_id, nama_barang, spesifikasi, nomor_seri, tanggal_pembelian, harga, status, divisi_tujuan, tanggal_penyerahan, catatan_penyerahan, created_at)
├── kerusakan_barang (id, inventaris_id, tanggal_kerusakan, jenis_kerusakan, deskripsi_kerusakan, status, created_at)
└── pemindahan_barang (id, inventaris_id, divisi_asal, divisi_tujuan, tanggal_pemindahan, alasan_pemindahan, created_at)
```

### **User Roles & Permissions**
- **Staff IT**: Buat pengajuan, input barang ke inventaris
- **Kepala Ruangan**: Verifikasi pengajuan, cetak surat
- **Keuangan**: Persetujuan anggaran
- **OB**: Lihat daftar pembelian
- **Admin**: Full access semua fitur

## 🔄 Alur Kerja Sistem

```
[Staff IT] → [Pengajuan Barang] → [Status: Diajukan]
     ↓
[Kepala Ruangan] → [Verifikasi] → [Status: Diverifikasi/Ditolak]
     ↓
[Bagian Keuangan] → [Persetujuan] → [Status: ACC Keuangan/Ditolak]
     ↓
[OB] → [Pembelian Barang]
     ↓
[Staff IT] → [Input ke Inventaris] → [Status: Tersedia]
     ↓
[Penyerahan/Input Kerusakan/Pemindahan]
```

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **Server**: Apache/Nginx (XAMPP)

## 📋 Persyaratan Sistem

### **Server Requirements**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx web server
- Extensions PHP: mysqli, session

### **Client Requirements**
- Browser modern (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- Responsive design untuk mobile

## 🚀 Cara Install & Setup

### **1. Clone Repository**
```bash
git clone [repository-url]
cd invent-barang
```

### **2. Setup Database**
- Buka XAMPP Control Panel
- Start Apache dan MySQL
- Buka phpMyAdmin: http://localhost/phpmyadmin
- Database akan otomatis dibuat saat pertama kali mengakses aplikasi

### **3. Konfigurasi Database**
Edit file `config/database.php` jika diperlukan:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'invent_barang';
```

### **4. Akses Aplikasi**
- Buka browser: http://localhost/invent-barang
- Login dengan kredensial demo

## 🔑 Kredensial Demo

| Role | Username | Password |
|------|----------|----------|
| Staff IT | staff | staff123 |
| Kepala Ruangan | kepala | kepala123 |
| Keuangan | keuangan | keuangan123 |
| OB | ob | ob123 |
| Admin | admin | admin123 |

## 📱 Fitur Mobile

- Responsive design untuk semua ukuran layar
- Touch-friendly interface
- Optimized untuk mobile browsing

## 🔒 Keamanan

- Session-based authentication
- Role-based access control
- SQL injection prevention dengan prepared statements
- XSS protection dengan htmlspecialchars
- CSRF protection

## 📊 Monitoring & Maintenance

### **Backup Database**
```bash
mysqldump -u root -p invent_barang > backup_$(date +%Y%m%d).sql
```

### **Log Files**
- Error logs: Apache error log
- Application logs: Database tables

## 🚧 Troubleshooting

### **Common Issues**

1. **Database Connection Error**
   - Pastikan MySQL service running
   - Cek kredensial database di config/database.php

2. **Session Issues**
   - Pastikan session directory writable
   - Cek PHP session configuration

3. **Permission Denied**
   - Pastikan file permissions correct
   - Cek web server user permissions

## 📈 Roadmap Pengembangan

### **Phase 1 (Current)**
- ✅ Basic CRUD operations
- ✅ User management
- ✅ Basic reporting

### **Phase 2 (Future)**
- 🔄 PDF generation untuk surat pengajuan
- 🔄 Advanced reporting dengan charts
- 🔄 Email notifications
- 🔄 Barcode/QR code integration

### **Phase 3 (Future)**
- 🔄 Mobile app
- 🔄 API endpoints
- 🔄 Integration dengan sistem lain
- 🔄 Advanced analytics

## 👥 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Support

Untuk pertanyaan dan dukungan:
- Email: support@example.com
- Documentation: [Wiki](link-to-wiki)
- Issues: [GitHub Issues](link-to-issues)

---

**Dibuat dengan ❤️ untuk Divisi IT**

