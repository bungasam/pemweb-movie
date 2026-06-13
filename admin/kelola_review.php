<?php
// =============================================
// FILE: admin/kelola_review.php
// Fungsi: Admin melihat, filter real-time, dan hapus review
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua review (tanpa filter dulu, nanti difilter oleh JavaScript)
$semua_review = $pdo->query("
    SELECT r.*, u.username, f.judul AS judul_film, f.id AS film_id
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN films f ON r.film_id = f.id
    ORDER BY r.created_at DESC
");

// Ambil daftar film untuk dropdown (opsional, tapi bisa dipakai juga)
$daftar_film = $pdo->query("SELECT id, judul FROM films ORDER BY judul ASC");

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Review — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Filter bar styling */
        .filter-bar {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #333;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 0.3rem;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.6rem 0.8rem;
            background: #242424;
            border: 1px solid #333;
            border-radius: 8px;
            color: #f0f0f0;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #BA3801;
        }
        
        .filter-group input::placeholder {
            color: #555;
        }
        
        .filter-info {
            margin-top: 0.8rem;
            font-size: 0.8rem;
            color: #888;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .badge-filter {
            background: rgba(186,56,1,0.2);
            color: #FFEC89;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-filter .remove-filter {
            margin-left: 0.3rem;
            cursor: pointer;
            font-weight: bold;
        }
        
        .badge-filter .remove-filter:hover {
            color: #BA3801;
        }
        
        .result-count {
            color: #FFEC89;
            font-weight: 600;
        }
        
        /* Sembunyikan baris yang tidak sesuai filter */
        .review-row.hidden {
            display: none;
        }
        
        /* Modal untuk lihat komentar lengkap */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.aktif {
            display: flex;
        }
        .modal-kotak {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        .modal-tutup {
            position: absolute;
            top: 0.8rem;
            right: 1rem;
            background: none;
            border: none;
            color: #aaa;
            font-size: 1.4rem;
            cursor: pointer;
        }
        .modal-tutup:hover { color: #fff; }
        
        /* Rating stars */
        .rating-stars {
            color: #FFEC89;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_film.php">Kelola Film</a>
        <a href="kelola_review.php" class="active">Kelola Review</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:1rem;">
            Kelola Review
        </h1>
        <p style="color:#aaa; font-size:0.9rem;">Moderasi seluruh review pengguna di platform CineView</p>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <!-- ========== FILTER BAR REAL-TIME ========== -->
        <div class="filter-bar">
            <div class="filter-form">
                <div class="filter-group">
                    <label>🎬 Nama Film</label>
                    <input type="text" 
                           id="filterFilm" 
                           placeholder="Cari berdasarkan judul film..." 
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label>👤 User</label>
                    <input type="text" 
                           id="filterUser" 
                           placeholder="Cari berdasarkan nama user..." 
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label>⭐ Rating</label>
                    <select id="filterRating">
                        <option value="">Semua Rating</option>
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4+)</option>
                        <option value="3">★★★☆☆ (3+)</option>
                        <option value="2">★★☆☆☆ (2+)</option>
                        <option value="1">★☆☆☆☆ (1+)</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>📅 Tanggal</label>
                    <select id="filterTanggal">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">7 Hari Terakhir</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </div>
            </div>
            
            <!-- Informasi filter aktif -->
            <div id="filterInfo" class="filter-info" style="display:none;">
                <span>Filter aktif:</span>
                <div id="activeFilters" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
                <span id="resultCount" class="result-count"></span>
            </div>
        </div>
        
        <!-- ========== TABEL REVIEW ========== -->
        <div class="tabel-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Film</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php 
                    $no = 1;
                    foreach ($semua_review as $r): 
                        // Simpan data sebagai atribut data-* untuk filter JavaScript
                        $judul_lower = strtolower($r['judul_film']);
                        $user_lower = strtolower($r['username']);
                        $rating_val = $r['rating'];
                        $tgl = $r['created_at'];
                    ?>
                    <tr class="review-row" 
                        data-id="<?= $r['id'] ?>"
                        data-judul="<?= htmlspecialchars($judul_lower) ?>"
                        data-user="<?= htmlspecialchars($user_lower) ?>"
                        data-rating="<?= $rating_val ?>"
                        data-tanggal="<?= $tgl ?>">
                        
                        <td><?= $no++ ?></td>
                        <td style="color:#f0f0f0; font-weight:600;">
                            <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                                <span style="width:28px; height:28px; border-radius:50%; background:#BA3801; display:inline-flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:white;">
                                    <?= strtoupper(substr($r['username'],0,1)) ?>
                                </span>
                                <?= htmlspecialchars($r['username']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="../detail.php?id=<?= $r['film_id'] ?>" 
                               style="color:#FFEC89; text-decoration:none;" target="_blank">
                                <?= htmlspecialchars($r['judul_film']) ?> ↗
                            </a>
                        </td>
                        <td>
                            <span class="rating-stars">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                            </span>
                            <span style="color:#aaa; font-size:0.75rem;">(<?= $r['rating'] ?>)</span>
                        </td>
                        <td style="max-width:200px;">
                            <span style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;">
                                <?= htmlspecialchars($r['komentar']) ?>
                            </span>
                            <button type="button"
                                    class="btn btn-outline btn-kecil"
                                    style="margin-top:0.3rem; font-size:0.7rem;"
                                    onclick="lihatKomentar(
                                        '<?= addslashes(htmlspecialchars($r['username'])) ?>',
                                        '<?= addslashes(htmlspecialchars($r['judul_film'])) ?>',
                                        <?= $r['rating'] ?>,
                                        '<?= addslashes(htmlspecialchars($r['komentar'])) ?>'
                                    )">
                                📖 Lihat Lengkap
                            </button>
                        </td>
                        <td style="font-size:0.8rem; white-space:nowrap;">
                            <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            <br><small style="color:#555;"><?= date('H:i', strtotime($r['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="hapus_review.php?id=<?= $r['id'] ?>&film_id=<?= $r['film_id'] ?>"
                               class="btn btn-hapus btn-kecil"
                               onclick="return konfirmasiHapus('Hapus review dari <?= addslashes($r['username']) ?>?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if ($semua_review->rowCount() == 0): ?>
                    <tr id="emptyRow">
                        <td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">
                            Belum ada review.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal lihat komentar lengkap -->
<div class="modal-overlay" id="modal-komentar">
    <div class="modal-kotak">
        <button class="modal-tutup" onclick="tutupModal()">✕</button>
        <div style="margin-bottom:0.5rem; font-size:0.75rem; color:#BA3801; text-transform:uppercase; letter-spacing:1px;">Komentar Lengkap</div>
        <h3 id="modal-judul-film" style="font-family:'Playfair Display',serif; color:#FFEC89; margin-bottom:0.3rem;"></h3>
        <div style="font-size:0.85rem; color:#aaa; margin-bottom:0.5rem;">
            oleh <span id="modal-username" style="color:#f0f0f0; font-weight:600;"></span>
        </div>
        <div id="modal-rating" style="color:#FFEC89; font-size:1.1rem; margin-bottom:1rem;"></div>
        <div id="modal-komentar-isi" style="color:#ccc; line-height:1.8; white-space:pre-wrap;"></div>
    </div>
</div>

<script src="../script.js"></script>
<script>
// ============================================
// FILTER REAL-TIME UNTUK KELOLA REVIEW
// ============================================

// Ambil elemen
const filterFilm = document.getElementById('filterFilm');
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
        if (match && ratingValue && rating < parseInt(ratingValue)) {
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
            case '4': ratingText = '★★★★☆ (4+)'; break;
            case '3': ratingText = '★★★☆☆ (3+)'; break;
            case '2': ratingText = '★★☆☆☆ (2+)'; break;
            case '1': ratingText = '★☆☆☆☆ (1+)'; break;
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

// ============================================
// MODAL KOMENTAR
// ============================================
function lihatKomentar(username, judulFilm, rating, komentar) {
    document.getElementById('modal-username').textContent = username;
    document.getElementById('modal-judul-film').textContent = judulFilm;
    document.getElementById('modal-komentar-isi').textContent = komentar;
    
    let bintang = '';
    for (let i = 1; i <= 5; i++) {
        bintang += (i <= rating) ? '★' : '☆';
    }
    document.getElementById('modal-rating').innerHTML = bintang + ' (' + rating + '/5)';
    
    document.getElementById('modal-komentar').classList.add('aktif');
}

function tutupModal() {
    document.getElementById('modal-komentar').classList.remove('aktif');
}

document.getElementById('modal-komentar').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});
</script>

<script>
// ============================================
// KONFIRMASI LOGOUT DENGAN SWEETALERT
// ============================================
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
</script>
</body>
</html>