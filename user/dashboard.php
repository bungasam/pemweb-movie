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

if ($_SESSION['role'] == 'admin') {
    header("Location: ../admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user terkini dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ambil semua review yang dibuat user ini
$stmt_review = $pdo->prepare("
    SELECT r.*, f.judul AS judul_film, f.id AS film_id
    FROM reviews r
    JOIN films f ON r.film_id = f.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt_review->execute([$user_id]);
$review_user = $stmt_review;
$jml_review  = $stmt_review->rowCount();

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
    <!-- Tambahkan SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="../index.php">Beranda</a></li>
            <li><a href="../rekomendasi.php">Rekomendasi</a></li>
            <li><a href="dashboard.php">Profil</a></li>
            <li><a href="../logout.php" onclick="confirmLogout(event)">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="profil-wrapper">
    
    <?php if ($pesan): ?>
    <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>
    
    <div class="profil-header">
        <!-- Foto profil: tampilkan gambar kalau ada, kalau tidak pakai inisial -->
        <div class="profil-avatar" style="overflow:hidden; padding:0;">
            <?php if (!empty($user['foto'])): ?>
                <img src="../img/<?= htmlspecialchars($user['foto']) ?>"
                     alt="Foto Profil"
                     onerror="this.style.display='none'; document.getElementById('inisial-avatar').style.display='flex';"
                     style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                <div id="inisial-avatar" style="display:none; width:100%; height:100%; align-items:center; justify-content:center; font-size:2rem; font-weight:700;">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php else: ?>
                <div style="display:flex; width:100%; height:100%; align-items:center; justify-content:center; font-size:2rem; font-weight:700;">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <div class="profil-nama"><?= htmlspecialchars($user['username']) ?></div>
            <div class="profil-email">📧 <?= htmlspecialchars($user['email']) ?></div>
            <div class="profil-badge">User</div>
            <div style="font-size:0.82rem; color:#aaa; margin-top:0.5rem;">
                📅 Bergabung <?= date('d F Y', strtotime($user['created_at'])) ?>
            </div>
            <!-- Tombol edit profil -->
            <div style="margin-top:0.8rem;">
                <a href="edit_profil.php" class="btn btn-biru btn-kecil">✏️ Edit Profil</a>
            </div>
        </div>
    </div>
    
    <div style="margin-bottom:2rem;">
        <div style="background:var(--card); border:0.5px solid var(--card-border); border-radius:5px; padding:0.3rem; text-align:center;">
            <div style="font-family:'Playfair Display',serif; font-size:5rem; font-weight:950; color:#FFEC89;"><?= $jml_review ?></div>
            <div style="font-size:0.85rem; color:#aaa; text-transform:uppercase; letter-spacing:1px; margin-top:5px;">Review Ditulis</div>
        </div>
    </div>
    
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:1rem;">
        Riwayat <span style="color:#FFEC89;">Ulasan</span>
    </h2>
    <div class="garis-dekorasi"></div>
    
    <?php if ($jml_review > 0): ?>
    <div class="review-list">
        <?php while ($r = $review_user->fetch()): ?>
        <div class="review-card">
            <div class="review-header">
                <div>
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
    
    <div style="margin-top:3rem; padding-top:1.5rem; border-top:1px solid var(--card-border);">
        <a href="../logout.php" class="btn btn-outline">🚪 Logout</a>
    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-bawah">
            &copy; 2026 CineView &mdash; Platform Rating Film
        </div>
    </div>
</footer>

<script src="../script.js"></script>
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
</body>
</html>
