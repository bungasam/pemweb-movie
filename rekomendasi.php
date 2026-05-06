<?php
require_once 'koneksi.php';

// Ambil film dengan rating tertinggi (rata-rata rating)
$query = mysqli_query($conn, "
    SELECT f.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as total_review
    FROM film f
    LEFT JOIN review r ON f.id = r.film_id
    GROUP BY f.id
    ORDER BY avg_rating DESC, total_review DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekomendasi - MatchaFlix 🍵</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .rekomendasi-header {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #6f1d1b, #2d6a4f);
            color: white;
            margin-bottom: 30px;
        }
        .rating-star {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .total-review {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }
        .film-item {
            display: flex;
            gap: 20px;
            background: white;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .film-item:hover {
            transform: translateY(-5px);
        }
        .film-item img {
            width: 120px;
            height: 160px;
            object-fit: cover;
            border-radius: 15px;
        }
        .film-info {
            flex: 1;
        }
        .film-info h3 {
            color: #6f1d1b;
            margin-bottom: 10px;
        }
        .film-info .genre {
            color: #40916c;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .rating-container {
            margin-top: 10px;
        }
        @media (max-width: 600px) {
            .film-item {
                flex-direction: column;
                text-align: center;
            }
            .film-item img {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-decoration">🍃 🍵 🍃</div>
    <h1>🍵 MatchaFlix</h1>
    <p>Rekomendasi Film & Series Terbaik 🎬</p>
</header>

<nav>
    <a href="index.php">🏠 Home</a>
    <a href="rekomendasi.php">✨ Rekomendasi</a>
    <a href="tentang.php">🎬 Tentang</a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?= $_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">👤 Dashboard</a>
        <a href="logout.php">🚪 Logout (<?= $_SESSION['username'] ?>)</a>
    <?php else: ?>
        <a href="login.php">🔐 Login</a>
        <a href="register.php">📝 Daftar</a>
    <?php endif; ?>
</nav>

<div class="rekomendasi-header">
    <h2>✨ Rekomendasi Untukmu ✨</h2>
    <p>Film dan series dengan rating tertinggi dari para penonton MatchaFlix</p>
</div>

<div class="container" style="flex-direction: column; align-items: center;">
    <?php while($film = mysqli_fetch_assoc($query)): ?>
        <div class="film-item">
            <img src="img/<?= $film['poster'] ?>" onerror="this.src='img/default.jpg'">
            <div class="film-info">
                <h3><?= htmlspecialchars($film['judul']) ?></h3>
                <div class="genre">🎭 Genre: <?= htmlspecialchars($film['genre'] ?? 'Tidak tersedia') ?> | 📅 Tahun: <?= $film['tahun'] ?? '-' ?></div>
                <p><?= htmlspecialchars(substr($film['deskripsi'], 0, 150)) ?>...</p>
                <div class="rating-container">
                    <span class="rating-star">
                        <?php 
                        $avg = round($film['avg_rating'], 1);
                        for($i = 1; $i <= 5; $i++):
                            if($i <= $avg):
                                echo "⭐";
                            else:
                                echo "☆";
                            endif;
                        endfor;
                        ?>
                    </span>
                    <span style="font-weight: bold;"> <?= $avg ?>/5</span>
                    <div class="total-review">📊 Dari <?= $film['total_review'] ?> review</div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
    <?php if(mysqli_num_rows($query) == 0): ?>
        <div style="text-align: center; padding: 50px;">
            <p>Belum ada review. Jadilah yang pertama memberikan rating! 🍵</p>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>🍵 MatchaFlix — Temukan tontonan terbaikmu berikutnya 🍃</p>
</footer>

</body>
</html>