// konfirmasi logout pop up 
function confirmLogout(event) {
    event.preventDefault();
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
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../logout.php';
        }
    });
}

// konfirmasi hapus data (film, review, user)
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
            return true;
        }
        return false;
    });
    return false;
}

// drag & drop + preview untuk tambah/edit film (dipisah agar bisa dipanggil dari event listener)
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah elemen upload area ada di halaman
    const uploadArea = document.getElementById('uploadArea');
    if (!uploadArea) return; // Skip jika tidak ada di halaman
    
    const posterInput = document.getElementById('posterInput');
    const browseBtn = document.getElementById('browseBtn');
    const previewImage = document.getElementById('previewImage');
    const previewPlaceholder = document.getElementById('previewPlaceholder');

    // Browse button
    if (browseBtn) {
        browseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            posterInput.click();
        });
    }

    // Upload area click
    if (uploadArea) {
        uploadArea.addEventListener('click', function(e) {
            if (e.target === uploadArea || e.target.classList.contains('upload-icon') || e.target.classList.contains('upload-text')) {
                posterInput.click();
            }
        });
    }

    // Drag & Drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            posterInput.files = files;
            handleFilePreview(files[0], previewImage, previewPlaceholder, posterInput);
        }
    });

    // File input change
    if (posterInput) {
        posterInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                handleFilePreview(this.files[0], previewImage, previewPlaceholder, posterInput);
            } else {
                // Reset preview jika tidak ada file
                if (previewImage) previewImage.style.display = 'none';
                if (previewPlaceholder) previewPlaceholder.style.display = 'block';
            }
        });
    }
});

// Preview function (dipisahkan agar bisa dipanggil dari event listener)
function handleFilePreview(file, previewImage, previewPlaceholder, posterInput) {
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Format file harus JPG, PNG, atau WEBP!');
        if (posterInput) posterInput.value = '';
        if (previewImage) previewImage.style.display = 'none';
        if (previewPlaceholder) previewPlaceholder.style.display = 'block';
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file maksimal 5MB!');
        if (posterInput) posterInput.value = '';
        if (previewImage) previewImage.style.display = 'none';
        if (previewPlaceholder) previewPlaceholder.style.display = 'block';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        if (previewImage) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
        }
        if (previewPlaceholder) {
            previewPlaceholder.style.display = 'none';
        }
    };
    reader.readAsDataURL(file);
}

// filter real-time untuk halaman kelola film)
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah elemen filter ada di halaman
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return; // Skip jika tidak ada di halaman
    
    const genreSelect = document.getElementById('genreSelect');
    const tahunSelect = document.getElementById('tahunSelect');
    const tableBody = document.getElementById('tableBody');
    const filterInfo = document.getElementById('filterInfo');
    const activeFiltersDiv = document.getElementById('activeFilters');
    const resultCountSpan = document.getElementById('resultCount');

    // Ambil semua baris film (kecuali emptyRow)
    function getFilmRows() {
        return Array.from(document.querySelectorAll('#tableBody .film-row'));
    }

    // Fungsi filter
    function applyFilter() {
        const searchValue = searchInput.value.toLowerCase().trim();
        const genreValue = genreSelect.value;
        const tahunValue = tahunSelect.value;
        
        const rows = getFilmRows();
        let visibleCount = 0;
        
        // Loop setiap baris
        rows.forEach(row => {
            const judul = row.getAttribute('data-judul');
            const genre = row.getAttribute('data-genre');
            const tahun = row.getAttribute('data-tahun');
            
            let match = true;
            
            // Filter judul
            if (searchValue && !judul.includes(searchValue)) {
                match = false;
            }
            
            // Filter genre (cek apakah genre yang dipilih ada di kolom genre film)
            if (match && genreValue && !genre.includes(genreValue.toLowerCase())) {
                match = false;
            }
            
            // Filter tahun
            if (match && tahunValue && tahun !== tahunValue) {
                match = false;
            }
            
            // Tampilkan atau sembunyikan baris
            if (match) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Update nomor urut
        updateNomorUrut();
        
        // Update filter info
        updateFilterInfo(searchValue, genreValue, tahunValue, visibleCount);
        
        // Tampilkan pesan jika tidak ada hasil
        showEmptyMessage(visibleCount);
    }

    // Update nomor urut setelah filter
    function updateNomorUrut() {
        const visibleRows = Array.from(document.querySelectorAll('#tableBody .film-row:not(.hidden)'));
        visibleRows.forEach((row, index) => {
            const noCell = row.cells[0];
            if (noCell) {
                noCell.textContent = index + 1;
            }
        });
    }

    // Tampilkan pesan "Tidak ada film" jika diperlukan
    function showEmptyMessage(visibleCount) {
        let emptyRow = document.getElementById('emptyRow');
        
        if (visibleCount === 0) {
            if (!emptyRow) {
                const newRow = document.createElement('tr');
                newRow.id = 'emptyRow';
                newRow.innerHTML = '<td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">😢 Tidak ada film yang ditemukan.<br>Coba dengan kata kunci atau filter yang berbeda.</td>';
                tableBody.appendChild(newRow);
            }
        } else {
            if (emptyRow) {
                emptyRow.remove();
            }
        }
    }

    // Update badge filter aktif
    function updateFilterInfo(search, genre, tahun, count) {
        let hasActive = false;
        activeFiltersDiv.innerHTML = '';
        
        if (search) {
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    Judul: "${escapeHtml(search)}" 
                    <span class="remove-filter" data-filter="search">✕</span>
                </span>
            `;
        }
        
        if (genre) {
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    Genre: ${escapeHtml(genre)} 
                    <span class="remove-filter" data-filter="genre">✕</span>
                </span>
            `;
        }
        
        if (tahun) {
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    Tahun: ${escapeHtml(tahun)} 
                    <span class="remove-filter" data-filter="tahun">✕</span>
                </span>
            `;
        }
        
        if (hasActive) {
            filterInfo.style.display = 'flex';
            resultCountSpan.innerHTML = `Ditemukan: ${count} film`;
            
            // Event listener untuk tombol hapus filter
            document.querySelectorAll('.remove-filter').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter');
                    switch(filterType) {
                        case 'search':
                            searchInput.value = '';
                            break;
                        case 'genre':
                            genreSelect.value = '';
                            break;
                        case 'tahun':
                            tahunSelect.value = '';
                            break;
                    }
                    applyFilter(); // Terapkan filter ulang
                });
            });
        } else {
            filterInfo.style.display = 'none';
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Event listener (real-time, tanpa tombol)
    searchInput.addEventListener('input', applyFilter);
    genreSelect.addEventListener('change', applyFilter);
    tahunSelect.addEventListener('change', applyFilter);

    // Inisialisasi: tampilkan semua film
    applyFilter();
});

// filter real-time untuk halaman kelola review
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah elemen filter ada di halaman
    const filterFilm = document.getElementById('filterFilm');
    if (!filterFilm) return; // Skip jika tidak ada di halaman
    
    const filterUser = document.getElementById('filterUser');
    const filterRating = document.getElementById('filterRating');
    const filterTanggal = document.getElementById('filterTanggal');
    const tableBody = document.getElementById('tableBody');
    const filterInfo = document.getElementById('filterInfo');
    const activeFiltersDiv = document.getElementById('activeFilters');
    const resultCountSpan = document.getElementById('resultCount');

    // Fungsi untuk mendapatkan semua baris review
    function getReviewRows() {
        return Array.from(document.querySelectorAll('#tableBody .review-row'));
    }

    // Fungsi untuk membandingkan tanggal
    function isDateInRange(tanggal, filter) {
        const tgl = new Date(tanggal);
        const sekarang = new Date();
        const today = new Date(sekarang.getFullYear(), sekarang.getMonth(), sekarang.getDate());
        const tglDate = new Date(tgl.getFullYear(), tgl.getMonth(), tgl.getDate());
        
        switch(filter) {
            case 'today':
                return tglDate.getTime() === today.getTime();
            case 'week':
                const weekAgo = new Date(today);
                weekAgo.setDate(today.getDate() - 7);
                return tglDate >= weekAgo;
            case 'month':
                return tgl.getMonth() === sekarang.getMonth() && 
                       tgl.getFullYear() === sekarang.getFullYear();
            case 'year':
                return tgl.getFullYear() === sekarang.getFullYear();
            default:
                return true;
        }
    }

    // Fungsi filter utama
    function applyFilter() {
        const filmValue = filterFilm.value.toLowerCase().trim();
        const userValue = filterUser.value.toLowerCase().trim();
        const ratingValue = filterRating.value;
        const tanggalValue = filterTanggal.value;
        
        const rows = getReviewRows();
        let visibleCount = 0;
        
        rows.forEach(row => {
            const judul = row.getAttribute('data-judul') || '';
            const user = row.getAttribute('data-user') || '';
            const rating = parseInt(row.getAttribute('data-rating')) || 0;
            const tanggal = row.getAttribute('data-tanggal') || '';
            
            let match = true;
            
            // Filter judul film
            if (filmValue && !judul.includes(filmValue)) {
                match = false;
            }
            
            // Filter user
            if (match && userValue && !user.includes(userValue)) {
                match = false;
            }
            
            // Filter rating (>= nilai yang dipilih)
            if (match && ratingValue && rating !== parseInt(ratingValue)) {
                match = false;
            }
            
            // Filter tanggal
            if (match && tanggalValue && !isDateInRange(tanggal, tanggalValue)) {
                match = false;
            }
            
            if (match) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Update nomor urut
        updateNomorUrut();
        
        // Update filter info
        updateFilterInfo(filmValue, userValue, ratingValue, tanggalValue, visibleCount);
        
        // Tampilkan pesan jika tidak ada hasil
        showEmptyMessage(visibleCount);
    }

    // Update nomor urut setelah filter
    function updateNomorUrut() {
        const visibleRows = Array.from(document.querySelectorAll('#tableBody .review-row:not(.hidden)'));
        visibleRows.forEach((row, index) => {
            const noCell = row.cells[0];
            if (noCell) {
                noCell.textContent = index + 1;
            }
        });
    }

    // Tampilkan pesan "Tidak ada review"
    function showEmptyMessage(visibleCount) {
        let emptyRow = document.getElementById('emptyRow');
        
        if (visibleCount === 0) {
            if (!emptyRow) {
                const newRow = document.createElement('tr');
                newRow.id = 'emptyRow';
                newRow.innerHTML = '<td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">😢 Tidak ada review yang ditemukan.<br>Coba dengan filter yang berbeda.</td>';
                tableBody.appendChild(newRow);
            }
        } else {
            if (emptyRow) {
                emptyRow.remove();
            }
        }
    }

    // Update badge filter aktif
    function updateFilterInfo(film, user, rating, tanggal, count) {
        let hasActive = false;
        activeFiltersDiv.innerHTML = '';
        
        if (film) {
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    🎬 Film: "${escapeHtml(film)}" 
                    <span class="remove-filter" data-filter="film">✕</span>
                </span>
            `;
        }
        
        if (user) {
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    👤 User: "${escapeHtml(user)}" 
                    <span class="remove-filter" data-filter="user">✕</span>
                </span>
            `;
        }
        
        if (rating) {
            let ratingText = '';
            switch(rating) {
                case '5': ratingText = '★★★★★ (5)'; break;
                case '4': ratingText = '★★★★☆ (4)'; break;
                case '3': ratingText = '★★★☆☆ (3)'; break;
                case '2': ratingText = '★★☆☆☆ (2)'; break;
                case '1': ratingText = '★☆☆☆☆ (1)'; break;
            }
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    ⭐ Rating: ${ratingText}
                    <span class="remove-filter" data-filter="rating">✕</span>
                </span>
            `;
        }
        
        if (tanggal) {
            let tanggalText = '';
            switch(tanggal) {
                case 'today': tanggalText = 'Hari Ini'; break;
                case 'week': tanggalText = '7 Hari Terakhir'; break;
                case 'month': tanggalText = 'Bulan Ini'; break;
                case 'year': tanggalText = 'Tahun Ini'; break;
            }
            hasActive = true;
            activeFiltersDiv.innerHTML += `
                <span class="badge-filter">
                    📅 Tanggal: ${tanggalText}
                    <span class="remove-filter" data-filter="tanggal">✕</span>
                </span>
            `;
        }
        
        if (hasActive) {
            filterInfo.style.display = 'flex';
            resultCountSpan.innerHTML = `Ditemukan: ${count} review`;
            
            // Event listener untuk tombol hapus filter
            document.querySelectorAll('.remove-filter').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter');
                    switch(filterType) {
                        case 'film':
                            filterFilm.value = '';
                            break;
                        case 'user':
                            filterUser.value = '';
                            break;
                        case 'rating':
                            filterRating.value = '';
                            break;
                        case 'tanggal':
                            filterTanggal.value = '';
                            break;
                    }
                    applyFilter();
                });
            });
        } else {
            filterInfo.style.display = 'none';
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Event listener (real-time, tanpa tombol)
    filterFilm.addEventListener('input', applyFilter);
    filterUser.addEventListener('input', applyFilter);
    filterRating.addEventListener('change', applyFilter);
    filterTanggal.addEventListener('change', applyFilter);

    // Inisialisasi: tampilkan semua review
    applyFilter();

    // pop up liat komentar lengkap
    window.lihatKomentar = function(username, judulFilm, rating, komentar) {
        const modalUsername = document.getElementById('modal-username');
        const modalJudulFilm = document.getElementById('modal-judul-film');
        const modalKomentarIsi = document.getElementById('modal-komentar-isi');
        const modalRating = document.getElementById('modal-rating');
        const modalKomentar = document.getElementById('modal-komentar');
        
        if (modalUsername) modalUsername.textContent = username;
        if (modalJudulFilm) modalJudulFilm.textContent = judulFilm;
        if (modalKomentarIsi) modalKomentarIsi.textContent = komentar;
        
        let bintang = '';
        for (let i = 1; i <= 5; i++) {
            bintang += (i <= rating) ? '★' : '☆';
        }
        if (modalRating) modalRating.innerHTML = bintang + ' (' + rating + '/5)';
        
        if (modalKomentar) modalKomentar.classList.add('aktif');
    }

    window.tutupModal = function() {
        const modalKomentar = document.getElementById('modal-komentar');
        if (modalKomentar) modalKomentar.classList.remove('aktif');
    }

    // Tutup modal saat klik overlay
    const modalKomentar = document.getElementById('modal-komentar');
    if (modalKomentar) {
        modalKomentar.addEventListener('click', function(e) {
            if (e.target === this) window.tutupModal();
        });
    }
});

// filter real-time untuk halaman kelola user
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah elemen search user ada di halaman
    const searchUser = document.getElementById('searchUser');
    if (!searchUser) return; // Skip jika tidak ada di halaman
    
    const clearSearchBtn = document.getElementById('clearSearch');
    const tableBodyUser = document.getElementById('tableBodyUser');
    const filterInfoUser = document.getElementById('filterInfoUser');
    const activeFiltersUser = document.getElementById('activeFiltersUser');
    const visibleCountSpan = document.getElementById('visibleCount');
    
    // Total user dari PHP (disimpan di data attribute atau hitung dari baris)
    const totalUserCount = document.querySelectorAll('#tableBodyUser .user-row').length;

    // Fungsi untuk mendapatkan semua baris user
    function getUserRows() {
        return Array.from(document.querySelectorAll('#tableBodyUser .user-row'));
    }

    // Fungsi filter utama
    function applyUserFilter() {
        const searchValue = searchUser.value.toLowerCase().trim();
        const rows = getUserRows();
        let visibleCount = 0;
        
        rows.forEach(row => {
            const username = row.getAttribute('data-username') || '';
            const email = row.getAttribute('data-email') || '';
            
            let match = true;
            
            if (searchValue) {
                if (!username.includes(searchValue) && !email.includes(searchValue)) {
                    match = false;
                }
            }
            
            if (match) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Update nomor urut
        updateUserNomorUrut();
        
        // Update filter info
        updateUserFilterInfo(searchValue, visibleCount);
        
        // Tampilkan/tombol clear search
        if (searchValue) {
            if (clearSearchBtn) clearSearchBtn.style.display = 'block';
        } else {
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
        }
        
        // Tampilkan pesan jika tidak ada hasil
        showEmptyUserMessage(visibleCount);
    }

    // Update nomor urut
    function updateUserNomorUrut() {
        const visibleRows = Array.from(document.querySelectorAll('#tableBodyUser .user-row:not(.hidden)'));
        visibleRows.forEach((row, index) => {
            const noCell = row.cells[0];
            if (noCell) {
                noCell.textContent = index + 1;
            }
        });
        
        // Update total info
        if (visibleCountSpan) {
            visibleCountSpan.textContent = visibleRows.length;
        }
    }

    // Update filter info
    function updateUserFilterInfo(search, count) {
        let hasActive = false;
        if (activeFiltersUser) activeFiltersUser.innerHTML = '';
        
        if (search) {
            hasActive = true;
            if (activeFiltersUser) {
                activeFiltersUser.innerHTML += `
                    <span class="badge-filter-user">
                        🔍 Pencarian: "${escapeHtml(search)}" 
                        <span class="remove-filter" data-filter="search">✕</span>
                    </span>
                `;
            }
        }
        
        if (filterInfoUser) {
            if (hasActive) {
                filterInfoUser.style.display = 'flex';
                
                // Event listener untuk tombol hapus filter
                document.querySelectorAll('#activeFiltersUser .remove-filter').forEach(btn => {
                    btn.addEventListener('click', function() {
                        searchUser.value = '';
                        applyUserFilter();
                    });
                });
            } else {
                filterInfoUser.style.display = 'none';
            }
        }
    }

    // Tampilkan pesan "Tidak ada user"
    function showEmptyUserMessage(visibleCount) {
        let emptyRow = document.getElementById('emptyRowUser');
        const rows = getUserRows();
        
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow && tableBodyUser) {
                const newRow = document.createElement('tr');
                newRow.id = 'emptyRowUser';
                newRow.innerHTML = '<td colspan="6" style="text-align:center; padding:2rem; color:#aaa;">😢 Tidak ada user yang ditemukan.<br>Coba dengan kata kunci yang berbeda.</td>';
                tableBodyUser.appendChild(newRow);
            }
        } else if (emptyRow && visibleCount > 0) {
            emptyRow.remove();
        }
    }

    // Clear search
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchUser.value = '';
            applyUserFilter();
            searchUser.focus();
        });
    }

    // Event listener real-time
    searchUser.addEventListener('input', applyUserFilter);

    // Inisialisasi
    applyUserFilter();
});

// form validasi untuk tambah/edit film 
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah form film ada di halaman
    const filmForm = document.getElementById('filmForm');
    if (!filmForm) return; // Skip jika tidak ada di halaman
    
    const posterInput = document.getElementById('posterInput');
    const sinopsisTextarea = document.querySelector('textarea[name="sinopsis"]');

    // Form validation (sinopsis min 100 karakter)
    if (filmForm) {
        filmForm.addEventListener('submit', function(e) {
            const sinopsis = sinopsisTextarea ? sinopsisTextarea.value.trim() : '';
            if (sinopsis.length < 100) {
                e.preventDefault();
                alert('Sinopsis minimal 100 karakter! Saat ini: ' + sinopsis.length + ' karakter');
                return false;
            }
            
            if (posterInput && (!posterInput.files || !posterInput.files[0])) {
                e.preventDefault();
                alert('Poster film harus diupload!');
                return false;
            }
        });
    }
});