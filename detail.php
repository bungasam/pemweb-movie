<?php
// =============================================
// FILE: detail.php
// Fungsi: Halaman detail film + review user
// =============================================

session_start();
include 'koneksi.php';

// Ambil ID film dari URL
$film_id = intval($_GET['id'] ?? 0);

if ($film_id == 0) {
    header("Location: index.php");
    exit;
}

// ---- Ambil data film ----
$stmt = $pdo->prepare("SELECT * FROM films WHERE id = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch();

if (!$film) {
    header("Location: index.php");
    exit;
}

// ---- Hitung rata-rata rating ----
$stmt_rating = $pdo->prepare("SELECT ROUND(AVG(rating),1) AS rata, COUNT(*) AS total FROM reviews WHERE film_id = ?");
$stmt_rating->execute([$film_id]);
$data_rating = $stmt_rating->fetch();

// ---- Cek apakah user sudah pernah review film ini ----
$sudah_review = false;
if (isset($_SESSION['user_id'])) {
    $cek = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND film_id = ?");
    $cek->execute([$_SESSION['user_id'], $film_id]);
    $sudah_review = $cek->rowCount() > 0;
}

// ---- Ambil semua review untuk film ini ----
$stmt_review = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.film_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_review->execute([$film_id]);
$semua_review = $stmt_review;

// ---- Pesan notifikasi ----
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($film['judul']) ?> — CineView</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="rekomendasi.php">Rekomendasi</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="admin/dashboard.php">Dashboard Admin</a></li>
                <?php else: ?>
                    <li><a href="user/dashboard.php">Profil Saya</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="register.php">Daftar</a></li>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- DETAIL FILM -->
<div class="detail-film-wrapper">
    
    <?php if ($pesan): ?>
    <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>

    <div class="detail-film-atas">
        
        <div class="detail-poster">
            <img src="img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg') ?>"
                 alt="<?= htmlspecialchars($film['judul']) ?>"
                 onerror="this.src='img/default.svg'">
        </div>
        
        <div class="detail-info">
            <h1><?= htmlspecialchars($film['judul']) ?></h1>
            
            <div class="detail-meta">
                <div class="meta-tag">🎬 <span><?= htmlspecialchars($film['genre']) ?></span></div>
                <div class="meta-tag">📅 <span><?= $film['tahun'] ?></span></div>
                <div class="meta-tag">🎥 <span><?= htmlspecialchars($film['sutradara']) ?></span></div>
            </div>
            
            <div class="detail-rating-besar">
                <div class="angka-rating">
                    <?= $data_rating['rata'] ?? '—' ?>
                </div>
                <div>
                    <div class="rating-stars" style="font-size:1.3rem;">
                        <?php
                        $rata = $data_rating['rata'] ?? 0;
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= round($rata) ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <div style="font-size:0.82rem; color:#aaa; margin-top:0.2rem;">
                        Dari <?= $data_rating['total'] ?> ulasan
                    </div>
                </div>
            </div>
            
            <p class="detail-sinopsis"><?= nl2br(htmlspecialchars($film['sinopsis'])) ?></p>
        </div>
    </div>

    <!-- FORM TAMBAH REVIEW -->
    <div style="margin-bottom:3rem;">
        <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'): ?>
        <h2 class="section-title" style="margin-bottom:1rem;">Tulis <span>Ulasanmu</span></h2>
        <div class="garis-dekorasi"></div>
        <?php endif; ?>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="alert alert-info">
            <a href="login.php" style="color:#6b8fd4;">Login</a> terlebih dahulu untuk menulis ulasan.
        </div>
        
        <?php elseif ($_SESSION['role'] == 'admin'): ?>
        <!-- Admin tidak melihat form review -->
        <?php echo '' ?>
        
        <?php elseif ($sudah_review): ?>
        <div class="alert alert-info">
            Kamu sudah mengulas film ini. 
            <a href="user/edit_review.php?film_id=<?= $film_id ?>" style="color:#6b8fd4;">Edit ulasanmu</a>.
        </div>
        
        <?php else: ?>
        <div style="background:var(--card); border:1px solid var(--card-border); border-radius:10px; padding:2rem; max-width:600px;">
            <form method="POST" action="proses_review.php" onsubmit="return validasiReview()">
                <input type="hidden" name="film_id" value="<?= $film_id ?>">
                <input type="hidden" name="aksi" value="tambah">
                
                <div class="form-group">
                    <label>Rating Bintang</label>
                    <div class="rating-input">
                        <input type="radio" name="rating" id="bintang5" value="5">
                        <label for="bintang5" title="5 Bintang">★</label>
                        <input type="radio" name="rating" id="bintang4" value="4">
                        <label for="bintang4" title="4 Bintang">★</label>
                        <input type="radio" name="rating" id="bintang3" value="3">
                        <label for="bintang3" title="3 Bintang">★</label>
                        <input type="radio" name="rating" id="bintang2" value="2">
                        <label for="bintang2" title="2 Bintang">★</label>
                        <input type="radio" name="rating" id="bintang1" value="1">
                        <label for="bintang1" title="1 Bintang">★</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="komentar">Ulasan</label>
                    <textarea id="komentar" name="komentar" 
                              placeholder="Tulis pendapatmu tentang film ini..."
                              required></textarea>
                </div>
                
                <button type="submit" class="btn btn-merah">Kirim Ulasan</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- DAFTAR REVIEW -->
    <div>
        <h2 class="section-title" style="margin-bottom:1rem;">
            Semua <span>Ulasan</span>
            <small style="font-size:1rem; color:#aaa; font-family:'DM Sans',sans-serif;">
                (<?= $data_rating['total'] ?>)
            </small>
        </h2>
        <div class="garis-dekorasi"></div>
        
        <?php if ($semua_review->rowCount() > 0): ?>
        <div class="review-list">
            <?php while ($review = $semua_review->fetch()): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-user">
                        <div class="avatar-kecil"><?= strtoupper(substr($review['username'], 0, 1)) ?></div>
                        <?= htmlspecialchars($review['username']) ?>
                    </div>
                    <div class="review-tanggal">
                        <?= date('d M Y', strtotime($review['created_at'])) ?>
                    </div>
                </div>
                
                <div class="rating-stars" style="margin-bottom:0.5rem;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= $review['rating'] ? '★' : '☆' ?>
                    <?php endfor; ?>
                </div>
                
                <p class="review-komentar"><?= nl2br(htmlspecialchars($review['komentar'])) ?></p>
                
                <?php
                $boleh_edit   = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id'];
                $boleh_hapus  = $boleh_edit || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
                ?>
                
                <?php if ($boleh_edit || $boleh_hapus): ?>
                <div class="review-actions">
                    <?php if ($boleh_edit): ?>
                    <a href="user/edit_review.php?id=<?= $review['id'] ?>" class="btn btn-biru btn-kecil">Edit</a>
                    <?php endif; ?>
                    
                    <?php if ($boleh_hapus): ?>
                    <a href="<?= $_SESSION['role'] == 'admin' ? 'admin/hapus_review.php' : 'user/hapus_review.php' ?>?id=<?= $review['id'] ?>&film_id=<?= $film_id ?>"
                       class="btn btn-hapus btn-kecil"
                       onclick="return konfirmasiHapus('Hapus ulasan ini?')">
                        Hapus
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php else: ?>
        <div class="empty-state">
            <div class="ikon">💬</div>
            <p>Belum ada ulasan untuk film ini. Jadilah yang pertama!</p>
        </div>
        <?php endif; ?>
    </div>
    
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-atas">
            <div class="footer-brand">
                <h3>Cine<span style="color:#BA3801">View</span></h3>
                <p>Platform ulasan dan rating film terpercaya untuk semua pecinta film.</p>
            </div>
            <div class="footer-kolom">
                <h4>Kontak</h4>
                <a href="mailto:cineview@gmail.com">✉️ cineview@gmail.com</a>
                <a href="tel:+6281234567890">📞 +62 812-3456-7890</a>
            </div>
        </div>
        <div class="footer-bawah">
            &copy; 2026 CineView &mdash; Platform Rating Film Terpercaya
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>