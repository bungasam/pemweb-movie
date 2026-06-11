<?php
// =============================================
// FILE: admin/kelola_review.php
// Fungsi: Admin melihat, filter, dan hapus review
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// ---- Filter berdasarkan film yang dipilih ----
$film_dipilih = intval($_GET['film_id'] ?? 0);

// Ambil daftar semua film untuk dropdown filter
$daftar_film = $pdo->query("SELECT id, judul FROM films ORDER BY judul ASC");

// Query review: kalau ada filter film, tampilkan review film itu saja
if ($film_dipilih > 0) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.username, f.judul AS judul_film
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN films f ON r.film_id = f.id
        WHERE r.film_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$film_dipilih]);
} else {
    $stmt = $pdo->query("
        SELECT r.*, u.username, f.judul AS judul_film
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN films f ON r.film_id = f.id
        ORDER BY r.created_at DESC
    ");
}
$semua_review = $stmt;

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
    <style>
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
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php">📊 Dashboard</a>
        <div class="sidebar-title">Kelola Film</div>
        <a href="tambah_film.php">➕ Tambah Film</a>
        <div class="sidebar-title">Kelola Lainnya</div>
        <a href="kelola_review.php" class="active">💬 Kelola Review</a>
        <a href="kelola_user.php">👥 Kelola User</a>
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:1rem;">
            Kelola Review
        </h1>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <!-- Filter berdasarkan judul film -->
        <div style="margin-bottom:1.5rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <label style="color:#aaa; font-size:0.9rem;">Filter berdasarkan film:</label>
            <form method="GET" style="display:flex; gap:0.5rem; align-items:center;">
                <select name="film_id" 
                        style="background:#242424; border:1px solid #333; color:#f0f0f0; padding:0.5rem 0.8rem; border-radius:6px; font-family:'DM Sans',sans-serif;"
                        onchange="this.form.submit()">
                    <option value="0">-- Semua Film --</option>
                    <?php foreach ($daftar_film as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $film_dipilih == $f['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['judul']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($film_dipilih > 0): ?>
                <a href="kelola_review.php" class="btn btn-outline btn-kecil">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="tabel-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Film</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $ada_data = false;
                    foreach ($semua_review as $r):
                        $ada_data = true;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="color:#f0f0f0; font-weight:600;"><?= htmlspecialchars($r['username']) ?></td>
                        <td><?= htmlspecialchars($r['judul_film']) ?></td>
                        <td>
                            <span style="color:#FFEC89;">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                            </span>
                            <span style="color:#aaa; font-size:0.8rem;">(<?= $r['rating'] ?>)</span>
                        </td>
                        <td style="max-width:180px;">
                            <!-- Komentar terpotong + tombol lihat detail -->
                            <span style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:160px;">
                                <?= htmlspecialchars($r['komentar']) ?>
                            </span>
                            <button type="button"
                                    class="btn btn-outline btn-kecil"
                                    style="margin-top:0.3rem; font-size:0.72rem;"
                                    onclick="lihatKomentar(
                                        '<?= addslashes(htmlspecialchars($r['username'])) ?>',
                                        '<?= addslashes(htmlspecialchars($r['judul_film'])) ?>',
                                        <?= $r['rating'] ?>,
                                        '<?= addslashes(htmlspecialchars($r['komentar'])) ?>'
                                    )">
                                Lihat Lengkap
                            </button>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <a href="hapus_review.php?id=<?= $r['id'] ?>"
                               class="btn btn-hapus btn-kecil"
                               onclick="return konfirmasiHapus('Hapus review dari <?= addslashes($r['username']) ?>?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (!$ada_data): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">
                            <?= $film_dipilih > 0 ? 'Belum ada review untuk film ini.' : 'Belum ada review.' ?>
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
// Fungsi tampilkan modal komentar lengkap
function lihatKomentar(username, judulFilm, rating, komentar) {
    document.getElementById('modal-username').textContent    = username;
    document.getElementById('modal-judul-film').textContent  = judulFilm;
    document.getElementById('modal-komentar-isi').textContent = komentar;
    
    // Tampilkan bintang rating
    var bintang = '';
    for (var i = 1; i <= 5; i++) {
        bintang += (i <= rating) ? '★' : '☆';
    }
    document.getElementById('modal-rating').textContent = bintang + ' (' + rating + '/5)';
    
    document.getElementById('modal-komentar').classList.add('aktif');
}

function tutupModal() {
    document.getElementById('modal-komentar').classList.remove('aktif');
}

// Tutup modal kalau klik di luar kotak
document.getElementById('modal-komentar').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});
</script>
</body>
</html>
