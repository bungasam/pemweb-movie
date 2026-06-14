<?php
// =============================================
// FILE: admin/kelola_user.php
// Fungsi: Admin melihat, hapus user, dan lihat review per user
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses hapus user
if (isset($_GET['hapus'])) {
    $hapus_id = intval($_GET['hapus']);
    
    if ($hapus_id == $_SESSION['user_id']) {
        $pesan_hapus = "Tidak bisa menghapus akun sendiri!";
        $tipe_hapus  = "error";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
            $stmt->execute([$hapus_id]);
            $pesan_hapus = "User berhasil dihapus!";
            $tipe_hapus  = "sukses";
        } catch (PDOException $e) {
            $pesan_hapus = "Gagal menghapus user!";
            $tipe_hapus  = "error";
        }
    }
}

// Ambil semua user (bukan admin)
$semua_user = $pdo->query("
    SELECT u.*, COUNT(r.id) AS jml_review
    FROM users u
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

// Hitung statistik user
$total_user = $semua_user->rowCount();

// Hitung user yang pernah review (memiliki setidaknya 1 review)
$user_dengan_review = $pdo->query("
    SELECT COUNT(DISTINCT user_id) FROM reviews
")->fetchColumn();

// Hitung user yang belum pernah review
$user_belum_review = $total_user - $user_dengan_review;

// Kalau ada parameter lihat_review, ambil review user tersebut
$lihat_user_id = intval($_GET['lihat_review'] ?? 0);
$review_user   = [];
$nama_user_dipilih = '';

if ($lihat_user_id > 0) {
    $stmt_cek = $pdo->prepare("SELECT username FROM users WHERE id = ? AND role = 'user'");
    $stmt_cek->execute([$lihat_user_id]);
    $user_dipilih = $stmt_cek->fetch();
    
    if ($user_dipilih) {
        $nama_user_dipilih = $user_dipilih['username'];
        
        $stmt_rv = $pdo->prepare("
            SELECT r.*, f.judul AS judul_film, f.id AS film_id
            FROM reviews r
            JOIN films f ON r.film_id = f.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt_rv->execute([$lihat_user_id]);
        $review_user = $stmt_rv->fetchAll();
    }
}

$pesan = $_GET['pesan'] ?? ($pesan_hapus ?? '');
$tipe  = $_GET['tipe']  ?? ($tipe_hapus  ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Panel review user */
        .panel-review-user {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .panel-review-user h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: #FFEC89;
            margin-bottom: 1rem;
        }
        
        /* Statistik cards */
        .stat-user-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-user-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s;
        }
        
        .stat-user-card:hover {
            border-color: #BA3801;
        }
        
        .stat-user-angka {
            font-size: 2rem;
            font-weight: bold;
            color: #FFEC89;
            font-family: 'Playfair Display', serif;
        }
        
        .stat-user-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-top: 0.3rem;
        }
        
        .stat-user-icon {
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
        }
        
        /* Search bar styling */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 0.4rem 0.8rem;
            transition: all 0.2s;
        }
        
        .search-box:focus-within {
            border-color: #BA3801;
        }
        
        .search-box input {
            background: transparent;
            border: none;
            color: #f0f0f0;
            padding: 0.5rem;
            min-width: 220px;
            outline: none;
            font-size: 0.85rem;
        }
        
        .search-box input::placeholder {
            color: #555;
        }
        
        .search-box span {
            color: #888;
            font-size: 1rem;
        }
        
        .total-info {
            font-size: 0.85rem;
            color: #888;
        }
        
        .total-info strong {
            color: #FFEC89;
        }
        
        /* Sembunyikan baris yang tidak sesuai filter */
        .user-row.hidden {
            display: none;
        }
        
        /* Badge filter */
        .filter-info-user {
            margin-top: 0.8rem;
            margin-bottom: 1rem;
            font-size: 0.8rem;
            color: #888;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .badge-filter-user {
            background: rgba(186,56,1,0.2);
            color: #FFEC89;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-filter-user .remove-filter {
            margin-left: 0.3rem;
            cursor: pointer;
            font-weight: bold;
        }
        
        .badge-filter-user .remove-filter:hover {
            color: #BA3801;
        }
        
        .clear-search {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 1rem;
            padding: 0 0.3rem;
        }
        
        .clear-search:hover {
            color: #BA3801;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="../index.php">Lihat Website</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_film.php">Kelola Film</a>
        <a href="kelola_review.php">Kelola Review</a>
        <a href="kelola_user.php" class="active">Kelola User</a>
        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:0.5rem;">
            Kelola User
        </h1>
        <p style="color:#aaa; font-size:0.9rem;">Kelola pengguna yang terdaftar di platform CineView</p>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <!-- ========== STATISTIK USER ========== -->
        <div class="stat-user-grid">
            <div class="stat-user-card">
                <div class="stat-user-icon">👥</div>
                <div class="stat-user-angka"><?= $total_user ?></div>
                <div class="stat-user-label">Total User</div>
            </div>
            <div class="stat-user-card">
                <div class="stat-user-icon">✍️</div>
                <div class="stat-user-angka"><?= $user_dengan_review ?></div>
                <div class="stat-user-label">User dengan Review</div>
            </div>
            <div class="stat-user-card">
                <div class="stat-user-icon">📝</div>
                <div class="stat-user-angka"><?= $user_belum_review ?></div>
                <div class="stat-user-label">User Belum Review</div>
            </div>
        </div>
        
        <!-- ========== HEADER TABEL DENGAN PENCARIAN ========== -->
        <div class="table-header">
            <div class="total-info">
                Menampilkan <strong id="visibleCount"><?= $total_user ?></strong> dari <strong><?= $total_user ?></strong> user
            </div>
            <div class="search-box">
                <span>🔍</span>
                <input type="text" 
                       id="searchUser" 
                       placeholder="Cari berdasarkan username atau email..."
                       autocomplete="off">
                <button class="clear-search" id="clearSearch" style="display:none;">✕</button>
            </div>
        </div>
        
        <!-- Informasi filter aktif -->
        <div id="filterInfoUser" class="filter-info-user" style="display:none;">
            <span>Filter aktif:</span>
            <div id="activeFiltersUser" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
        </div>
        
    <!-- ========== TABEL USER ========== -->
<div class="tabel-wrapper">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Email</th>
                <th>Review</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBodyUser">
            <?php 
            $no = 1;
            $ada_user = false;
            foreach ($semua_user as $u):
                $ada_user = true;
                $username_lower = strtolower($u['username']);
                $email_lower = strtolower($u['email']);
            ?>
            <tr class="user-row" 
                data-username="<?= htmlspecialchars($username_lower) ?>"
                data-email="<?= htmlspecialchars($email_lower) ?>"
                data-id="<?= $u['id'] ?>">
                
                <td style="width:50px;"><?= $no++ ?></td>
                
                <td>
                <span style="display:inline-flex; align-items:center; gap:0.5rem;">

                    <?php
                    $nama_foto = basename($u['foto'] ?? '');
                    $lokasi_foto = '../img/' . $nama_foto;
                    ?>

                    <?php if (!empty($nama_foto) && file_exists($lokasi_foto)): ?>

                        <img
                            src="../img/<?= htmlspecialchars($nama_foto) ?>?v=<?= filemtime($lokasi_foto) ?>"
                            alt="Foto Profil"
                            style="
                                width:32px;
                                height:32px;
                                border-radius:50%;
                                object-fit:cover;
                                border:1px solid #333;
                            "
                        >

                    <?php else: ?>

                        <span style="
                            width:32px;
                            height:32px;
                            border-radius:50%;
                            background:#BA3801;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            font-size:0.8rem;
                            font-weight:700;
                            color:white;
                        ">
                            <?= strtoupper(substr($u['username'],0,1)) ?>
                        </span>

                    <?php endif; ?>

                    <?= htmlspecialchars($u['username']) ?>

                </span>
            </td>
                
                <td style="color:#aaa; font-size:0.85rem;">
                    <?= htmlspecialchars($u['email']) ?>
                </td>
                
                <td>
                    <?php if ($u['jml_review'] > 0): ?>
                    <a href="kelola_user.php?lihat_review=<?= $u['id'] ?>#panel-review"
                       style="background:rgba(74,105,179,0.2); border:1px solid #4A69B3; color:#6b8fd4; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.75rem; text-decoration:none; display:inline-block;">
                        📖 <?= $u['jml_review'] ?> review
                    </a>
                    <?php else: ?>
                    <span style="background:rgba(74,105,179,0.1); border:1px solid #333; color:#555; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.75rem;">
                        0 review
                    </span>
                    <?php endif; ?>
                </td>
                
                <td style="font-size:0.8rem; white-space:nowrap;">
                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                </td>
                
                <td>
                    <a href="kelola_user.php?hapus=<?= $u['id'] ?>"
                       class="btn btn-hapus btn-kecil"
                       onclick="return konfirmasiHapus('Hapus user <?= addslashes($u['username']) ?>? Semua reviewnya juga akan terhapus.')">
                        Hapus
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (!$ada_user): ?>
            <tr id="emptyRowUser">
                <td colspan="6" style="text-align:center; padding:2rem; color:#aaa;">
                    Belum ada user terdaftar
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
        
        <!-- Panel review user yang dipilih -->
        <?php if ($lihat_user_id > 0 && $nama_user_dipilih): ?>
        <div class="panel-review-user" id="panel-review">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3>📝 Review dari: <?= htmlspecialchars($nama_user_dipilih) ?></h3>
                <a href="kelola_user.php" class="btn btn-outline btn-kecil">✕ Tutup</a>
            </div>
            
            <?php if (count($review_user) > 0): ?>
            <div class="tabel-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Film</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no2 = 1; foreach ($review_user as $rv): ?>
                        <tr>
                            <td><?= $no2++ ?></td>
                            <td>
                                <a href="../detail.php?id=<?= $rv['film_id'] ?>"
                                   style="color:#FFEC89; text-decoration:none;" target="_blank">
                                    <?= htmlspecialchars($rv['judul_film']) ?> ↗
                                </a>
                            </td>
                            <td>
                                <span style="color:#FFEC89;">
                                    <?php for($i=1;$i<=5;$i++) echo $i<=$rv['rating']?'★':'☆'; ?>
                                </span>
                                <span style="color:#aaa; font-size:0.75rem;">(<?= $rv['rating'] ?>)</span>
                            </td>
                            <td style="max-width:250px; color:#ccc; font-size:0.85rem; line-height:1.5;">
                                <?= !empty($rv['komentar']) ? nl2br(htmlspecialchars($rv['komentar'])) : '<em style="color:#777;">Tanpa komentar</em>' ?>
                            </td>
                            <td><?= date('d M Y', strtotime($rv['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="color:#aaa;">User ini belum menulis review apapun.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </main>
</div>

<script src="../script.js"></script>
<script>
// ============================================
// FILTER REAL-TIME UNTUK KELOLA USER
// ============================================

// Ambil elemen
const searchUser = document.getElementById('searchUser');
const clearSearchBtn = document.getElementById('clearSearch');
const tableBodyUser = document.getElementById('tableBodyUser');
const filterInfoUser = document.getElementById('filterInfoUser');
const activeFiltersUser = document.getElementById('activeFiltersUser');
const visibleCountSpan = document.getElementById('visibleCount');
const totalUserCount = <?= $total_user ?>;

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
        clearSearchBtn.style.display = 'block';
    } else {
        clearSearchBtn.style.display = 'none';
    }
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
    visibleCountSpan.textContent = visibleRows.length;
}

// Update filter info
function updateUserFilterInfo(search, count) {
    let hasActive = false;
    activeFiltersUser.innerHTML = '';
    
    if (search) {
        hasActive = true;
        activeFiltersUser.innerHTML += `
            <span class="badge-filter-user">
                🔍 Pencarian: "${escapeHtml(search)}" 
                <span class="remove-filter" data-filter="search">✕</span>
            </span>
        `;
    }
    
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

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

// Tampilkan pesan jika tidak ada hasil (overwrite fungsi showEmptyMessage untuk user)
function showEmptyUserMessage() {
    let emptyRow = document.getElementById('emptyRowUser');
    const visibleRows = document.querySelectorAll('#tableBodyUser .user-row:not(.hidden)');
    
    if (visibleRows.length === 0 && getUserRows().length > 0) {
        if (!emptyRow) {
            const newRow = document.createElement('tr');
            newRow.id = 'emptyRowUser';
            newRow.innerHTML = '<td colspan="6" style="text-align:center; padding:2rem; color:#aaa;">😢 Tidak ada user yang ditemukan.<br>Coba dengan kata kunci yang berbeda.</td>';
            tableBodyUser.appendChild(newRow);
        }
    } else if (emptyRow && visibleRows.length > 0) {
        emptyRow.remove();
    }
}

// Override applyUserFilter untuk include empty message
const originalApplyUserFilter = applyUserFilter;
window.applyUserFilter = function() {
    originalApplyUserFilter();
    showEmptyUserMessage();
};
applyUserFilter();
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