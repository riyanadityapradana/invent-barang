<?php
/**
 * Modal WhatsApp untuk notifikasi pengajuan barang
 */
?>

<!-- Modal WhatsApp -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-whatsapp text-success me-2"></i>Kirim Notifikasi WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="whatsappForm">
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp Tujuan</label>
                        <div class="input-group">
                            <span class="input-group-text">+62</span>
                            <input type="text" name="phone" class="form-control" placeholder="81234567890" required>
                        </div>
                        <small class="form-text text-muted">Masukkan nomor tanpa kode negara (contoh: 81234567890)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" name="recipient_name" class="form-control" placeholder="Nama penerima" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select name="recipient_role" class="form-select" required>
                            <option value="">Pilih Jabatan</option>
                            <option value="Kepala Ruangan IT">Kepala Ruangan IT</option>
                            <option value="Bagian Keuangan">Bagian Keuangan</option>
                            <option value="Staff IT">Staff IT</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea name="message" class="form-control" rows="4" readonly></textarea>
                        <small class="form-text text-muted">Pesan akan di-generate otomatis berdasarkan data pengajuan</small>
                    </div>
                    
                    <input type="hidden" name="pengajuan_id" id="whatsapp_pengajuan_id">
                    <input type="hidden" name="nama_barang" id="whatsapp_nama_barang">
                    <input type="hidden" name="staff_name" id="whatsapp_staff_name">
                    <input type="hidden" name="tanggal_pengajuan" id="whatsapp_tanggal">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="sendWhatsApp()">
                    <i class="fab fa-whatsapp me-2"></i>Kirim WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openWhatsAppModal(pengajuanId, namaBarang, staffName, tanggalPengajuan, recipientRole) {
    // Set data ke form
    document.getElementById('whatsapp_pengajuan_id').value = pengajuanId;
    document.getElementById('whatsapp_nama_barang').value = namaBarang;
    document.getElementById('whatsapp_staff_name').value = staffName;
    document.getElementById('whatsapp_tanggal').value = tanggalPengajuan;
    
    // Set jabatan penerima
    document.querySelector('select[name="recipient_role"]').value = recipientRole;
    
    // Generate pesan otomatis
    let message = '';
    if (recipientRole === 'Kepala Ruangan IT') {
        message = `Halo Bapak/Ibu Kepala Ruangan IT,

Ada pengajuan barang baru yang memerlukan verifikasi:

📦 Nama Barang: ${namaBarang}
👤 Staff: ${staffName}
📅 Tanggal Pengajuan: ${tanggalPengajuan}

Mohon segera verifikasi pengajuan ini melalui sistem inventaris.

Terima kasih.`;
    } else if (recipientRole === 'Bagian Keuangan') {
        message = `Halo Bapak/Ibu Bagian Keuangan,

Ada pengajuan barang yang sudah diverifikasi Kepala Ruangan dan memerlukan approval keuangan:

📦 Nama Barang: ${namaBarang}
👤 Staff: ${staffName}
📅 Tanggal Pengajuan: ${tanggalPengajuan}

Mohon segera review dan approve pengajuan ini.

Terima kasih.`;
    }
    
    document.querySelector('textarea[name="message"]').value = message;
    
    // Buka modal
    new bootstrap.Modal(document.getElementById('whatsappModal')).show();
}

function sendWhatsApp() {
    const form = document.getElementById('whatsappForm');
    const formData = new FormData(form);
    
    // Validasi
    if (!formData.get('phone') || !formData.get('recipient_name') || !formData.get('recipient_role')) {
        alert('Mohon lengkapi semua field yang diperlukan!');
        return;
    }
    
    // Format nomor telepon
    let phone = formData.get('phone');
    if (phone.startsWith('0')) {
        phone = phone.substring(1);
    }
    if (!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    // Encode pesan untuk URL WhatsApp
    const message = encodeURIComponent(formData.get('message'));
    
    // Buat URL WhatsApp
    const whatsappUrl = `https://wa.me/${phone}?text=${message}`;
    
    // Buka WhatsApp
    window.open(whatsappUrl, '_blank');
    
    // Tutup modal
    bootstrap.Modal.getInstance(document.getElementById('whatsappModal')).hide();
    
    // Reset form
    form.reset();
    
    // Tampilkan notifikasi sukses
    alert('WhatsApp berhasil dibuka! Silakan kirim pesan secara manual.');
}
</script>
