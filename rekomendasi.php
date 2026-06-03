<?php
// =============================================
// FILE: rekomendasi.php
// Fungsi: Halaman daftar & rekomendasi film
//         (Film dengan rating tertinggi)
// =============================================

session_start();
include 'koneksi.php';

// ---- Ambil semua film beserta rating rata-rata ----
// Diurutkan dari rating tertinggi
$query = "
    SELECT f.*, 
           ROUND(AVG(r.rating), 1) AS rata_rating,
           COUNT(r.id) AS jml_review
    FROM films f
    LEFT JOIN reviews r ON f.id = r.film_id
    GROUP BY f.id
    ORDER BY rata_rating DESC, jml_review DESC
";
$hasil = mysqli_query($koneksi, $query);
$semua_film = mysqli_fetch_all($hasil, MYSQLI_ASSOC); // Ambil semua sekaligus

// ---- Filter genre (jika ada) ----
$genre_dipilih = $_GET['genre'] ?? '';

// ---- Ambil semua genre unik untuk filter ----
$hasil_genre = mysqli_query($koneksi, "SELECT DISTINCT genre FROM films ORDER BY genre");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Film — CineView</title>
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
            <li><a href="tentang.php">Tentang</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="user/dashboard.php">Profil</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Banner halaman -->
<div class="rekomendasi-banner">
    <h1>✦ Rekomendasi Film</h1>
    <p style="color:#aaa; margin-top:0.5rem;">Film terbaik berdasarkan rating komunitas</p>
</div>

<div class="section">
    
    <!-- Filter & Pencarian -->
    <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; align-items:center;">
        <!-- Input pencarian -->
        <input type="text" 
               id="cari-film" 
               placeholder="🔍 Cari judul film..." 
               oninput="cariFilm()"
               style="padding:0.6rem 1rem; background:#242424; border:1px solid #333; color:#f0f0f0; border-radius:6px; font-family:'DM Sans',sans-serif; width:250px; outline:none;">
        
        <!-- Filter genre -->
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="rekomendasi.php" 
               class="btn btn-kecil <?= empty($genre_dipilih) ? 'btn-merah' : 'btn-outline' ?>">
                Semua
            </a>
            <?php while ($g = mysqli_fetch_assoc($hasil_genre)): ?>
            <a href="?genre=<?= urlencode($g['genre']) ?>"
               class="btn btn-kecil <?= $genre_dipilih == $g['genre'] ? 'btn-biru' : 'btn-outline' ?>">
                <?= htmlspecialchars($g['genre']) ?>
            </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Grid film dengan nomor ranking -->
    <?php if (!empty($semua_film)): ?>
    <div class="film-grid" id="tabel-film">
        <?php 
        $nomor = 1;
        foreach ($semua_film as $film): 
            // Skip jika filter genre aktif dan tidak cocok
            if ($genre_dipilih && $film['genre'] != $genre_dipilih) {
                $nomor++;
                continue;
            }
        ?>
        <div class="film-card-wrapper">
            <!-- Nomor ranking -->
            <?php if ($nomor <= 3): ?>
            <div class="ranking-badge" 
                 style="background: <?= $nomor==1 ? '#BA3801' : ($nomor==2 ? '#4A69B3' : '#555') ?>">
                <?= $nomor ?>
            </div>
            <?php endif; ?>
            
            <a href="detail.php?id=<?= $film['id'] ?>" class="film-card">
                <img src="img/<?= htmlspecialchars($film['poster']) ?>"
                     alt="<?= htmlspecialchars($film['judul']) ?>"
                     onerror="this.src='img/default.jpg'">
                <div class="film-card-body">
                    <div class="film-card-judul"><?= htmlspecialchars($film['judul']) ?></div>
                    <div class="film-card-genre"><?= htmlspecialchars($film['genre']) ?> &bull; <?= $film['tahun'] ?></div>
                    <div class="rating-stars">
                        <?php
                        $r = $film['rata_rating'] ?? 0;
                        for ($i = 1; $i <= 5; $i++) echo $i <= round($r) ? '★' : '☆';
                        ?>
                        <span class="rating-angka">
                            <?= $r > 0 ? $r : 'Belum ada' ?>
                            <?= $film['jml_review'] > 0 ? "({$film['jml_review']})" : '' ?>
                        </span>
                    </div>
                </div>
            </a>
        </div>
        <?php $nomor++; endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="ikon">🎬</div>
        <p>Belum ada film yang tersedia.</p>
    </div>
    <?php endif; ?>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-bawah">
            &copy; 2024 CineView &mdash; Dibuat dengan <span style="color:#BA3801;">♥</span>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>