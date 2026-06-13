<?php
// =============================================
// FILE: admin/kelola_film.php
// Fungsi: Menampilkan semua film dengan filter real-time (tanpa AJAX)
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil SEMUA film
$semua_film = $pdo->query("SELECT * FROM films ORDER BY id DESC");

// Ambil daftar genre unik untuk dropdown
$genre_raw = $pdo->query("SELECT genre FROM films")->fetchAll();
$genre_list = [];
foreach ($genre_raw as $g) {
    $genres = explode(',', $g['genre']);
    foreach ($genres as $single) {
        $single = trim($single);
        if (!in_array($single, $genre_list)) {
            $genre_list[] = $single;
        }
    }
}
sort($genre_list);

// Ambil daftar tahun unik
$tahun_list = $pdo->query("SELECT DISTINCT tahun FROM films ORDER BY tahun DESC")->fetchAll();

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Film — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
            min-width: 160px;
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
        .film-row.hidden {
            display: none;
        }
        
        /* Header dengan tombol tambah di kanan */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #FFEC89;
            margin: 0;
        }
        
        .btn-tambah-header {
            background: linear-gradient(135deg, #BA3801, #8f2c00);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-tambah-header:hover {
            background: linear-gradient(135deg, #8f2c00, #6b2000);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(186,56,1,0.3);
        }
        
        .sub-header {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 1rem;
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
        <a href="kelola_film.php" class="active">Kelola Film</a>
        <a href="kelola_review.php">Kelola Review</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <!-- HEADER DENGAN TOMBOL TAMBAH DI KANAN -->
        <div class="page-header">
            <h1>Kelola Film</h1>
            <a href="tambah_film.php" class="btn-tambah-header">
                <span>➕</span> Tambah Film
            </a>
        </div>
        <p class="sub-header">Daftar seluruh film yang tersedia di platform CineView</p>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
        <!-- ========== FILTER BAR REAL-TIME (TANPA TOMBOL) ========== -->
        <div class="filter-bar">
            <div class="filter-form">
                <div class="filter-group" style="flex:2;">
                    <label>🔍 Cari berdasarkan judul film</label>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Ketik judul film..." 
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label>🎭 Genre</label>
                    <select id="genreSelect">
                        <option value="">Semua Genre</option>
                        <?php foreach ($genre_list as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>">
                            <?= htmlspecialchars($g) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>📅 Tahun</label>
                    <select id="tahunSelect">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($tahun_list as $t): ?>
                        <option value="<?= $t['tahun'] ?>">
                            <?= $t['tahun'] ?>
                        </option>
                        <?php endforeach; ?>
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
        
        <!-- ========== TABEL FILM ========== -->
        <div class="tabel-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Poster</th>
                        <th>Judul</th>
                        <th>Genre</th>
                        <th>Tahun</th>
                        <th>Sutradara</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php 
                    $no = 1;
                    foreach ($semua_film as $f): 
                        // Simpan data sebagai atribut data-* untuk filter JavaScript
                        $judul_lower = strtolower($f['judul']);
                        $genre_lower = strtolower($f['genre']);
                    ?>
                    <tr class="film-row" 
                        data-judul="<?= htmlspecialchars($judul_lower) ?>"
                        data-genre="<?= htmlspecialchars($genre_lower) ?>"
                        data-tahun="<?= $f['tahun'] ?>">
                        <td><?= $no++ ?></td>
                        <td>
                            <img src="../img/<?= htmlspecialchars($f['poster'] ?: 'default.svg') ?>" 
                                 style="width:40px; height:50px; object-fit:cover; border-radius:4px;"
                                 onerror="this.src='../img/default.svg'">
                        </td>
                        <td style="color:#f0f0f0; font-weight:600;"><?= htmlspecialchars($f['judul']) ?></td>
                        <td><?= htmlspecialchars($f['genre']) ?></td>
                        <td><?= $f['tahun'] ?></td>
                        <td><?= htmlspecialchars($f['sutradara']) ?></td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="../detail.php?id=<?= $f['id'] ?>" 
                                   class="btn btn-outline btn-kecil" target="_blank">Lihat</a>
                                <a href="edit_film.php?id=<?= $f['id'] ?>" 
                                   class="btn btn-biru btn-kecil">Edit</a>
                                <a href="hapus_film.php?id=<?= $f['id'] ?>" 
                                   class="btn btn-hapus btn-kecil"
                                   onclick="return konfirmasiHapus('Hapus film <?= addslashes($f['judul']) ?>?')">
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if ($semua_film->rowCount() == 0): ?>
                    <tr id="emptyRow">
                        <td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">
                            Belum ada film. Silakan tambah film terlebih dahulu.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </main>
</div>

<script src="../script.js"></script>
<script>
// ============================================
// FILTER REAL-TIME TANPA AJAX
// ============================================

// Ambil elemen
const searchInput = document.getElementById('searchInput');
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
</script>

<script>
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