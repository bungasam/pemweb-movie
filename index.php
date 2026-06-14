<?php
session_start();
include 'koneksi.php';

$query_populer = "
    SELECT f.*, 
           ROUND(AVG(r.rating), 1) AS rata_rating,
           COUNT(r.id) AS jml_review
    FROM films f
    LEFT JOIN reviews r ON f.id = r.film_id
    GROUP BY f.id
    ORDER BY rata_rating DESC, jml_review DESC
    LIMIT 6
";
$hasil_populer = $pdo->query($query_populer);

$query_terbaru = "SELECT * FROM films ORDER BY id DESC LIMIT 4";
$hasil_terbaru = $pdo->query($query_terbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineView — Rating Film Terpercaya</title>
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

<section class="hero">
    <div class="hero-badge">✦ Platform Rating Film </div>
    <h1>Temukan Film <span class="highlight">Terbaik</span><br>Versi Kamu</h1>
    <p>Baca ulasan jujur, beri rating, dan temukan rekomendasi film dari komunitas pecinta film.</p>
    <div class="hero-buttons">
        <a href="rekomendasi.php" class="btn btn-kuning">Jelajahi Film</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-outline">Bergabung Gratis</a>
        <?php endif; ?>
    </div>
</section>

<!-- FILM POPULER -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">Film <span>Populer</span></h2>
        <a href="rekomendasi.php" class="btn btn-outline btn-kecil">Lihat Semua</a>
    </div>
    <div class="garis-dekorasi"></div>
    
    <?php if ($hasil_populer->rowCount() > 0): ?>
    <div class="film-grid">
        <?php while ($film = $hasil_populer->fetch()): ?>
        <a href="detail.php?id=<?= $film['id'] ?>" class="film-card">
            <img src="img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg') ?>"
                 alt="<?= htmlspecialchars($film['judul']) ?>"
                 onerror="this.src='img/default.svg'">
            <div class="film-card-body">
                <div class="film-card-judul"><?= htmlspecialchars($film['judul']) ?></div>
                <div class="film-card-genre"><?= htmlspecialchars($film['genre']) ?> &bull; <?= $film['tahun'] ?></div>
                
                <div class="rating-stars">
                    <?php
                    $rating = $film['rata_rating'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= round($rating) ? '★' : '☆';
                    }
                    ?>
                    <span class="rating-angka">
                        <?= $rating > 0 ? $rating : 'Belum ada' ?>
                        <?= $film['jml_review'] > 0 ? "({$film['jml_review']} ulasan)" : '' ?>
                    </span>
                </div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="ikon">🎬</div>
        <p>Belum ada film. Admin perlu menambahkan film.</p>
    </div>
    <?php endif; ?>
</div>

<!-- FILM TERBARU -->
<div style="background: #1a1a1a; border-top: 1px solid #333; border-bottom: 1px solid #333;">
<div class="section">
    <div class="section-header">
        <h2 class="section-title">Baru <span>Ditambahkan</span></h2>
    </div>
    <div class="garis-dekorasi"></div>
    
    <div class="film-grid">
        <?php 
        $hasil_terbaru = $pdo->query($query_terbaru);
        while ($film = $hasil_terbaru->fetch()): 
        ?>
        <a href="detail.php?id=<?= $film['id'] ?>" class="film-card">
            <img src="img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg') ?>"
                 alt="<?= htmlspecialchars($film['judul']) ?>"
                 onerror="this.src='img/default.svg'">
            <div class="film-card-body">
                <div class="film-card-judul"><?= htmlspecialchars($film['judul']) ?></div>
                <div class="film-card-genre"><?= htmlspecialchars($film['genre']) ?> &bull; <?= $film['tahun'] ?></div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>
</div>

<!-- AJAKAN LOGIN -->
<?php if (!isset($_SESSION['user_id'])): ?>
<div class="section" style="text-align:center; padding: 3rem 2rem;">
    <div style="background: var(--card); border: 1px solid var(--card-border); border-radius: 12px; padding: 3rem; max-width: 600px; margin: 0 auto;">
        <div style="font-size:2rem; margin-bottom:1rem;">🎬</div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.8rem; margin-bottom:0.5rem; color: #FFEC89;">Bergabung dengan CineView</h3>
        <p style="color:#aaa; margin-bottom:1.5rem;">Daftar gratis dan mulai berikan ulasan film favoritmu kepada komunitas!</p>
        <div style="display:flex; gap:1rem; justify-content:center;">
            <a href="register.php" class="btn btn-merah">Daftar Sekarang</a>
            <a href="login.php" class="btn btn-outline">Login</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $footer_base = ''; include 'footer.php'; ?>

<script src="script.js"></script>
</body>
</html>
