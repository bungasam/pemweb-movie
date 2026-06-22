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
        // alert-info (misal: "Login terlebih dahulu") TIDAK dihilangkan otomatis
        // Biar tetap terlihat selama user di halaman tersebut
        if (alert.classList.contains('alert-info')) {
            return; // Lewati, jangan sembunyikan
        }
        
        // Alert sukses dan error hilang otomatis setelah 4 detik
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
//    Rating wajib dipilih, komentar boleh dikosongkan
// =============================================
function validasiReview() {
    var ratingTerpilih = document.querySelector('input[name="rating"]:checked');

    if (!ratingTerpilih) {
        alert('⚠️ Pilih rating bintang terlebih dahulu!');
        return false;
    }

    return true;
}

// =============================================
// 6. KONFIRMASI LOGOUT
//    SweetAlert dipakai jika tersedia. Jika CDN gagal,
//    otomatis memakai window.confirm() biasa.
// =============================================
function confirmLogout(event, logoutUrl) {
    event.preventDefault();

    var tujuan = logoutUrl;
    if (!tujuan && event.currentTarget) {
        tujuan = event.currentTarget.getAttribute('href');
    }
    tujuan = tujuan || 'logout.php';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '⚠️ Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin logout dari CineView?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#BA3801',
            cancelButtonColor: '#555',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal',
            background: '#1a1a1a',
            color: '#f0f0f0',
            iconColor: '#FFEC89'
        }).then(function (hasil) {
            if (hasil.isConfirmed) {
                window.location.href = tujuan;
            }
        });
    } else if (window.confirm('Apakah Anda yakin ingin logout dari CineView?')) {
        window.location.href = tujuan;
    }

    return false;
}

// =============================================
// 7. SEARCH FILM (Filter grid film secara real-time)
//    Bekerja dengan card-based grid, bukan tabel
// =============================================
function cariFilm() {
    var inputCari = document.getElementById('cari-film');
    var container = document.getElementById('tabel-film');
    
    if (!inputCari || !container) return;
    
    var kata = inputCari.value.toLowerCase(); // Ubah ke huruf kecil
    var cards = container.querySelectorAll('.film-card-wrapper');
    var jumlahTampil = 0;
    
    cards.forEach(function(card) {
        // Ambil judul film dari .film-card-judul
        var judulElement = card.querySelector('.film-card-judul');
        var judul = judulElement ? judulElement.textContent.toLowerCase() : '';
        
        // Cek apakah judul mengandung kata pencarian
        if (kata === '' || judul.includes(kata)) {
            card.style.display = '';    // Tampilkan
            jumlahTampil++;
        } else {
            card.style.display = 'none'; // Sembunyikan
        }
    });
    
    // Tampilkan pesan jika tidak ada film yang ditemukan
    var pesanKosong = container.querySelector('.pesan-kosong-cari');
    if (jumlahTampil === 0 && kata !== '') {
        // Buat atau tampilkan pesan kosong
        if (!pesanKosong) {
            pesanKosong = document.createElement('div');
            pesanKosong.className = 'pesan-kosong-cari';
            pesanKosong.style.textAlign = 'center';
            pesanKosong.style.padding = '2rem';
            pesanKosong.style.color = '#aaa';
            container.appendChild(pesanKosong);
        }
        pesanKosong.innerHTML = '🎬 Tidak ada film yang cocok dengan "' + inputCari.value + '"';
        pesanKosong.style.display = 'block';
    } else if (pesanKosong) {
        pesanKosong.style.display = 'none';
    }
}
// =============================================
// 8. TOGGLE SHOW/HIDE PASSWORD
//    Tombol mata untuk lihat/sembunyikan password
// =============================================
function togglePassword(inputId, tombol) {
    var input = document.getElementById(inputId);
    
    if (input.type === 'password') {
        input.type = 'text';           // Tampilkan password
        tombol.textContent = '👁'; // Ganti teks tombol
    } else {
        input.type = 'password';       // Sembunyikan lagi
        tombol.textContent = '⌣';  // Kembali ke teks awal
    }
}

// =============================================
// 9. CEK KEKUATAN PASSWORD
//    Tampilkan indikator kuat/lemah
// =============================================
function cekKekuatanPassword(nilai) {
    var info = document.getElementById('info-password');
    var bar  = document.getElementById('isi-bar-password');
    
    if (!info || !bar) return;
    
    var panjang = nilai.length;
    
    if (panjang === 0) {
        info.textContent = 'Minimal 6 karakter';
        info.style.color = '#aaa';
        bar.style.width  = '0%';
        bar.style.background = '#333';
        
    } else if (panjang < 6) {
        info.textContent = 'Terlalu pendek (' + panjang + '/6 karakter minimum)';
        info.style.color = '#e74c3c';
        bar.style.width  = '30%';
        bar.style.background = '#e74c3c';
        
    } else if (panjang < 10) {
        info.textContent = '✓ Password cukup (' + panjang + ' karakter)';
        info.style.color = '#f39c12';
        bar.style.width  = '60%';
        bar.style.background = '#f39c12';
        
    } else {
        info.textContent = '✓✓ Password kuat (' + panjang + ' karakter)';
        info.style.color = '#2ecc71';
        bar.style.width  = '100%';
        bar.style.background = '#2ecc71';
    }
}

// =============================================
// 10. CEK KONFIRMASI PASSWORD
//    Tampilkan apakah password cocok
// =============================================
function cekKonfirmasiPassword() {
    var password = document.getElementById('password');
    var konfirm  = document.getElementById('konfirm_password');
    var info     = document.getElementById('info-konfirm');
    
    if (!password || !konfirm || !info) return;
    
    if (konfirm.value.length === 0) {
        info.textContent = '';
        return;
    }
    
    if (password.value === konfirm.value) {
        info.textContent = '✓ Password cocok';
        info.style.color = '#2ecc71';
    } else {
        info.textContent = '✗ Password tidak sama';
        info.style.color = '#e74c3c';
    }
}

// ============================================
// KONFIRMASI HAPUS DENGAN SWEETALERT - ada di detail.php itu manggil role admin buat hapus review. mangkaknya perlu ditambahin di root ini, cek bari 359
// kalo ndk pake dia ntr konfirmasi hapusnya ndk manggil js
// ============================================
function konfirmasiHapus(pesan) {
    Swal.fire({
        title: '⚠️ Konfirmasi Hapus',
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#BA3801',
        cancelButtonColor: '#555',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1a1a1a',
        color: '#f0f0f0',
        iconColor: '#FFEC89'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cari link yang mengandung fungsi ini
            const links = document.querySelectorAll('a[onclick*="konfirmasiHapus"]');
            links.forEach(link => {
                if (link.onclick.toString().includes(pesan)) {
                    window.location.href = link.href;
                }
            });
        }
    });
    return false;
}