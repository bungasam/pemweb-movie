<?php
require_once 'koneksi.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CineView 🍵</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="script.js" defer></script>
</head>
<body>

<header>
    <div class="header-decoration">🍃 🍵 🍃</div>
    <h1>🍵 CineView</h1>
    <p>Review Film & Series Favorit Kamu 🎬</p>
</header>

<nav>
    <a href="index.php">🏠 Home</a>
    <a href="rekomendasi.php">✨ Rekomendasi</a>
    <a href="#list-film">🎬 List Film</a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?= $_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">👤 Dashboard</a>
        <a href="logout.php">🚪 Logout (<?= $_SESSION['username'] ?>)</a>
    <?php else: ?>
        <a href="login.php">🔐 Login</a>
        <a href="register.php">📝 Daftar</a>
    <?php endif; ?>
</nav>

<div class="search-box">
    <input type="text" id="search" placeholder="🔍 Cari film/series favoritmu...">
</div>

<h2 class="judul-section" id="list-film">Daftar Film</h2>

<div class="container" id="list">
    
<?php
$query = mysqli_query($conn, "SELECT * FROM film ORDER BY id DESC");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

while($film = mysqli_fetch_assoc($query)){
?>
    <div class="card" onclick="window.location='detail.php?id=<?= $film['id']; ?>'">
        <img src="img/<?= $film['poster']; ?>" class="poster" 
             onerror="this.src='img/default.jpg'">
        <h2><?= htmlspecialchars($film['judul']); ?></h2>
        <p class="deskripsi-singkat"><?= htmlspecialchars(substr($film['deskripsi'], 0, 80)) . '...'; ?></p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn-review" data-id="<?= $film['id']; ?>" data-judul="<?= htmlspecialchars($film['judul']); ?>">
                📝 Review Film Ini
            </button>
        <?php else: ?>
            <button class="btn-review" onclick="alert('Silakan login terlebih dahulu!')" style="background:#95d5b2;">
                🔒 Login untuk Review
            </button>
        <?php endif; ?>
    </div>
<?php } ?>
</div>

<!-- Modal untuk review -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalJudul">Review Film</h2>
        <form id="reviewForm" action="proses_review.php" method="POST">
            <input type="hidden" id="film_id" name="film_id">
            <input type="text" name="nama_reviewer" placeholder="Nama kamu" value="<?= isset($_SESSION['username']) ? $_SESSION['username'] : '' ?>" required>
            <textarea name="review" placeholder="Tulis review kamu di sini..." rows="4" required></textarea>
            <select name="rating" required>
                <option value="">⭐ Pilih Rating</option>
                <option value="5">⭐⭐⭐⭐⭐ 5/5</option>
                <option value="4">⭐⭐⭐⭐ 4/5</option>
                <option value="3">⭐⭐⭐ 3/5</option>
                <option value="2">⭐⭐ 2/5</option>
                <option value="1">⭐ 1/5</option>
            </select>
            <button type="submit"> Kirim Review</button>
        </form>
    </div>
</div>

<footer>
    <h3>CineView</h3>
    <p>Website review dan rekomendasi film.</p>
    <p>© 2026 CineView</p>
</footer>

</body>
</html>