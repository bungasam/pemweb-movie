<?php
// =============================================
// FILE: user/dashboard.php
// Fungsi: Halaman profil & riwayat review user
// =============================================

session_start();
include '../koneksi.php';

// Proteksi: harus sudah login sebagai user
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Admin tidak boleh akses halaman ini
if ($_SESSION['role'] == 'admin') {
    header("Location: ../admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user terkini dari database
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Ambil semua review yang dibuat user ini
$stmt_review = mysqli_prepare($koneksi, "
    SELECT r.*, f.judul AS judul_film, f.id AS film_id
    FROM reviews r
    JOIN films f ON r.film_id = f.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
mysqli_stmt_bind_param($stmt_review, "i", $user_id);
mysqli_stmt_execute($stmt_review);
$review_user = mysqli_stmt_get_result($stmt_review);
$jml_review  = mysqli_num_rows($review_user);

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — CineView</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="../index.php">Beranda</a></li>
            <li><a href="../rekomendasi.php">Rekomendasi</a></li>
            <li><a href="dashboard.php">Profil</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="profil-wrapper">
    
    <!-- Notifikasi -->
    <?php if ($pesan): ?>
    <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>
    
    <!-- Header Profil -->
    <div class="profil-header">
        <!-- Avatar dengan inisial nama -->
        <div class="profil-avatar">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
        </div>
        <div>
            <div class="profil-nama"><?= htmlspecialchars($user['username']) ?></div>
            <div class="profil-email">📧 <?= htmlspecialchars($user['email']) ?></div>
            <div class="profil-badge">User</div>
            <div style="font-size:0.82rem; color:#aaa; margin-top:0.5rem;">
                📅 Bergabung <?= date('d F Y', strtotime($user['created_at'])) ?>
            </div>
        </div>
    </div>
    
    <!-- Statistik user -->
    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; margin-bottom:2rem;">
        <div style="background:var(--card); border:1px solid var(--card-border); border-radius:8px; padding:1.2rem; text-align:center;">
            <div style="font-family:'Playfair Display',serif; font-size:2rem; font-weight:900; color:#FFEC89;"><?= $jml_review ?></div>
            <div style="font-size:0.8rem; color:#aaa; text-transform:uppercase; letter-spacing:1px;">Review Ditulis</div>
        </div>
        <div style="background:var(--card); border:1px solid var(--card-border); border-radius:8px; padding:1.2rem; text-align:center;">
            <div style="font-family:'Playfair Display',serif; font-size:2rem; font-weight:900; color:#4A69B3;">
                <?php
                // Hitung rata-rata rating yang pernah diberikan user
                $stmt_avg = mysqli_prepare($koneksi, "SELECT ROUND(AVG(rating),1) FROM reviews WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt_avg, "i", $user_id);
                mysqli_stmt_execute($stmt_avg);
                $avg = mysqli_fetch_row(mysqli_stmt_get_result($stmt_avg))[0];
                echo $avg ?: '—';
                ?>
            </div>
            <div style="font-size:0.8rem; color:#aaa; text-transform:uppercase; letter-spacing:1px;">Avg. Rating</div>
        </div>
    </div>
    
    <!-- ==============================
         RIWAYAT REVIEW
    ============================== -->
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:1rem;">
        Riwayat <span style="color:#FFEC89;">Ulasan</span>
    </h2>
    <div class="garis-dekorasi"></div>
    
    <?php if ($jml_review > 0): ?>
    <div class="review-list">
        <?php while ($r = mysqli_fetch_assoc($review_user)): ?>
        <div class="review-card">
            <div class="review-header">
                <div>
                    <!-- Link ke film yang di-review -->
                    <a href="../detail.php?id=<?= $r['film_id'] ?>" 
                       style="color:#FFEC89; font-weight:700; text-decoration:none; font-size:1rem;">
                        🎬 <?= htmlspecialchars($r['judul_film']) ?>
                    </a>
                    <div class="rating-stars" style="margin-top:0.3rem; font-size:1rem;">
                        <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                        <span class="rating-angka">(<?= $r['rating'] ?>/5)</span>
                    </div>
                </div>
                <div class="review-tanggal">
                    <?= date('d M Y', strtotime($r['created_at'])) ?>
                </div>
            </div>
            
            <p class="review-komentar"><?= nl2br(htmlspecialchars($r['komentar'])) ?></p>
            
            <!-- Tombol edit & hapus review sendiri -->
            <div class="review-actions" style="margin-top:0.8rem;">
                <a href="edit_review.php?id=<?= $r['id'] ?>" class="btn btn-biru btn-kecil">Edit</a>
                <a href="hapus_review.php?id=<?= $r['id'] ?>"
                   class="btn btn-hapus btn-kecil"
                   onclick="return konfirmasiHapus('Hapus ulasan ini?')">
                    Hapus
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <?php else: ?>
    <div class="empty-state">
        <div class="ikon">💬</div>
        <p>Kamu belum menulis ulasan apapun.</p>
        <a href="../rekomendasi.php" class="btn btn-merah" style="margin-top:1rem;">Cari Film</a>
    </div>
    <?php endif; ?>
    
    <!-- Tombol logout -->
    <div style="margin-top:3rem; padding-top:1.5rem; border-top:1px solid var(--card-border);">
        <a href="../logout.php" class="btn btn-outline">🚪 Logout</a>
    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-bawah">
            &copy; 2024 CineView &mdash; Dibuat dengan <span style="color:#BA3801;">♥</span>
        </div>
    </div>
</footer>

<script src="../script.js"></script>
</body>
</html>