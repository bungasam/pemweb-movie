<?php
// =============================================
// FILE: rekomendasi.php
// Fungsi: Halaman daftar & rekomendasi film
// =============================================

session_start();
include 'koneksi.php';

// ---- Ambil semua film beserta rating rata-rata ----
$query = "
    SELECT f.*, 
           ROUND(AVG(r.rating), 1) AS rata_rating,
           COUNT(r.id) AS jml_review
    FROM films f
    LEFT JOIN reviews r ON f.id = r.film_id
    GROUP BY f.id
    ORDER BY rata_rating DESC, jml_review DESC
";
$hasil = $pdo->query($query);
$semua_film = $hasil->fetchAll(); // Ambil semua sekaligus

// ---- Filter genre (jika ada) ----
$genre_dipilih = $_GET['genre'] ?? '';

// ---- Ambil semua genre unik dari field genre yang bisa multi (contoh: "Action, Drama") ----
// Kita pecah dulu tiap genre, lalu kumpulkan yang unik
$hasil_semua_genre = $pdo->query("SELECT genre FROM films");
$daftar_genre = []; // Array untuk menyimpan genre unik

foreach ($hasil_semua_genre as $baris) {
    // Pecah genre berdasarkan koma, misal "Action, Drama" jadi ["Action", "Drama"]
    $pecah = explode(',', $baris['genre']);
    foreach ($pecah as $g) {
        $g = trim($g); // Hapus spasi di pinggir
        if ($g != '' && !in_array($g, $daftar_genre)) {
            $daftar_genre[] = $g; // Tambahkan kalau belum ada
        }
    }
}
sort($daftar_genre); // Urutkan A-Z
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Film — CineView</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="rekomendasi.php">Rekomendasi</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="user/dashboard.php">Profil</a></li>
                <?php endif; ?>
                <li><a href="logout.php" onclick="return confirmLogout(event, this.href)">Logout</a></li>
            <?php else: ?>
                <li><a href="register.php">Daftar</a></li>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="rekomendasi-banner">
    <h1>✦ Rekomendasi Film</h1>
    <p style="color:#aaa; margin-top:0.5rem;">Film terbaik berdasarkan rating komunitas</p>
</div>

<div class="section">
    
    <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; align-items:center;">
        <input type="text" 
               id="cari-film" 
               placeholder="🔍 Cari judul film..." 
               oninput="cariFilm()"
               style="padding:0.6rem 1rem; background:#242424; border:1px solid #333; color:#f0f0f0; border-radius:6px; font-family:'DM Sans',sans-serif; width:250px; outline:none;">
        
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <!-- Tombol "Semua" untuk reset filter -->
            <a href="rekomendasi.php" 
               class="btn btn-kecil <?= empty($genre_dipilih) ? 'btn-merah' : 'btn-outline' ?>">
                Semua
            </a>
            <!-- Tombol per genre (satu-satu, bukan gabungan) -->
            <?php foreach ($daftar_genre as $g): ?>
            <a href="?genre=<?= urlencode($g) ?>"
               class="btn btn-kecil <?= $genre_dipilih == $g ? 'btn-biru' : 'btn-outline' ?>">
                <?= htmlspecialchars($g) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($semua_film)): ?>
    <div class="film-grid" id="tabel-film">
        <?php 
        $nomor = 1;
        foreach ($semua_film as $film): 
            // Kalau ada filter genre, cek apakah film ini mengandung genre tersebut
            if ($genre_dipilih != '') {
                // Pecah genre film menjadi array, misal "Action, Drama" jadi ["Action", "Drama"]
                $genre_film = array_map('trim', explode(',', $film['genre']));
                // Kalau genre yang dipilih tidak ada di genre film ini, skip
                if (!in_array($genre_dipilih, $genre_film)) {
                    $nomor++;
                    continue;
                }
            }
        ?>
        <div class="film-card-wrapper">
            <?php if ($nomor <= 3): ?>
            <div class="ranking-badge" 
                 style="background: <?= $nomor==1 ? '#BA3801' : ($nomor==2 ? '#4A69B3' : '#555') ?>">
                <?= $nomor ?>
            </div>
            <?php endif; ?>
            
            <a href="detail.php?id=<?= $film['id'] ?>" class="film-card">
                <img src="img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.jpg') ?>"
                     alt="<?= htmlspecialchars($film['judul']) ?>"
                     onerror="this.src='img/default.jpg'">
                <div class="film-card-body">
                    <div class="film-card-judul"><?= htmlspecialchars($film['judul']) ?></div>
                    <?php
                    // Tampilkan hanya genre PERTAMA saja di card rekomendasi
                    $genre_pertama = trim(explode(',', $film['genre'])[0]);
                    ?>
                    <div class="film-card-genre"><?= htmlspecialchars($genre_pertama) ?> &bull; <?= $film['tahun'] ?></div>
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

<?php $footer_base = ''; include 'footer.php'; ?>

<script src="script.js"></script>
</body>
</html>
