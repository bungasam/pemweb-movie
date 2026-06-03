// =============================================
// FILE: script.js
// Fungsi: Interaksi JavaScript untuk CineView
// =============================================

// =============================================
// 1. KONFIRMASI HAPUS
//    Muncul dialog tanya sebelum data dihapus
// =============================================
function konfirmasiHapus(pesan) {
    // window.confirm() menampilkan popup YA/TIDAK
    // Mengembalikan true jika klik OK, false jika Cancel
    return window.confirm(pesan || "Yakin ingin menghapus data ini?");
}

// =============================================
// 2. AUTO-HIDE ALERT
//    Pesan sukses/error otomatis hilang setelah 4 detik
// =============================================
window.addEventListener('DOMContentLoaded', function () {
    
    // Cari semua elemen dengan class 'alert'
    var alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // Set timer 4000ms = 4 detik
        setTimeout(function() {
            // Animasi fade out (transparansi berkurang perlahan)
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            
            // Setelah animasi selesai, hapus dari halaman
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 4000);
    });

    // =============================================
    // 3. PREVIEW GAMBAR
    //    Menampilkan preview poster sebelum di-upload
    // =============================================
    var inputGambar = document.getElementById('poster');
    var previewGambar = document.getElementById('preview-poster');
    
    if (inputGambar && previewGambar) {
        inputGambar.addEventListener('change', function() {
            var file = this.files[0]; // Ambil file pertama yang dipilih
            
            if (file) {
                var reader = new FileReader(); // Objek untuk membaca file
                
                // Ketika file selesai dibaca
                reader.onload = function(e) {
                    previewGambar.src = e.target.result; // Tampilkan gambar
                    previewGambar.style.display = 'block';
                };
                
                reader.readAsDataURL(file); // Baca file sebagai URL data
            }
        });
    }

    // =============================================
    // 4. AKTIFKAN LINK SIDEBAR
    //    Tandai menu yang sedang aktif di sidebar
    // =============================================
    var linkSidebar = document.querySelectorAll('.sidebar a');
    var urlSekarang = window.location.pathname; // URL halaman saat ini
    
    linkSidebar.forEach(function(link) {
        // Cek apakah URL link cocok dengan halaman sekarang
        if (link.getAttribute('href') && urlSekarang.includes(link.getAttribute('href'))) {
            link.classList.add('active'); // Tambah class active
        }
    });

});

// =============================================
// 5. VALIDASI FORM REVIEW
//    Pastikan rating sudah dipilih sebelum submit
// =============================================
function validasiReview() {
    // Cek apakah ada radio button rating yang dipilih
    var ratingTerpilih = document.querySelector('input[name="rating"]:checked');
    
    if (!ratingTerpilih) {
        alert('⚠️ Pilih rating bintang terlebih dahulu!');
        return false; // Batalkan submit form
    }
    
    var komentar = document.getElementById('komentar');
    if (komentar && komentar.value.trim().length < 5) {
        alert('⚠️ Tulis komentar minimal 5 karakter!');
        return false;
    }
    
    return true; // Lanjutkan submit
}

// =============================================
// 6. SEARCH FILM (Filter tabel secara real-time)
// =============================================
function cariFilm() {
    var inputCari  = document.getElementById('cari-film');
    var tabel      = document.getElementById('tabel-film');
    
    if (!inputCari || !tabel) return;
    
    var kata = inputCari.value.toLowerCase(); // Ubah ke huruf kecil
    var baris = tabel.querySelectorAll('tbody tr');
    
    baris.forEach(function(row) {
        var teks = row.textContent.toLowerCase(); // Teks di baris
        
        // Tampilkan baris jika teks mengandung kata pencarian
        if (teks.includes(kata)) {
            row.style.display = '';    // Tampilkan
        } else {
            row.style.display = 'none'; // Sembunyikan
        }
    });
}