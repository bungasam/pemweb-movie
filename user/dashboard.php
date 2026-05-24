<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$reviews = mysqli_query($conn, "
    SELECT r.*, f.judul as film_judul, f.poster
    FROM review r
    JOIN film f ON r.film_id = f.id
    WHERE r.user_id = '$user_id'
    ORDER BY r.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard User - CineView</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #2d6a4f, #6f1d1b);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .review-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            gap: 20px;
        }
        .review-card img {
            width: 80px;
            height: 110px;
            object-fit: cover;
            border-radius: 10px;
        }
        .review-info {
            flex: 1;
        }
        .review-info h3 {
            color: #6f1d1b;
            margin-bottom: 10px;
        }
        .review-rating {
            color: #ffc107;
            margin: 10px 0;
        }
        .review-text {
            color: #555;
            line-height: 1.6;
        }
        .review-actions {
            margin-top: 15px;
        }
        .btn-edit, .btn-delete {
            padding: 5px 15px;
            border-radius: 20px;
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
        }
        .btn-edit {
            background: #40916c;
            color: white;
        }
        .btn-delete {
            background: #6f1d1b;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 30px;
            margin: 40px auto;
            max-width: 500px;
        }
        @media (max-width: 600px) {
            .review-card {
                flex-direction: column;
                text-align: center;
            }
            .review-card img {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <h1>👤 Dashboard User</h1>
    <p>Selamat datang, <?= $_SESSION['username'] ?>! 🍵</p>
    <p>Kelola review filmmu di sini</p>
</div>

<nav>
    <a href="../index.php">🏠 Home</a>
    <a href="../rekomendasi.php">✨ Rekomendasi</a>
    <a href="../tentang.php">🎬 Tentang</a>
    <a href="dashboard.php">📝 Reviewku</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="container" style="flex-direction: column; align-items: center;">
    <h2 style="color:#6f1d1b; margin: 30px 0;">📝 Review yang Sudah Kamu Tulis</h2>
    
    <?php if(mysqli_num_rows($reviews) > 0): ?>
        <?php while($review = mysqli_fetch_assoc($reviews)): ?>
            <div class="review-card">
                <img src="../img/<?= $review['poster'] ?>" onerror="this.src='../img/default.jpg'">
                <div class="review-info">
                    <h3><?= htmlspecialchars($review['film_judul']) ?></h3>
                    <div class="review-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review['rating'] ? '⭐' : '☆' ?>
                        <?php endfor; ?>
                        <span style="color:#333;"> (<?= $review['rating'] ?>/5)</span>
                    </div>
                    <div class="review-text">
                        "<?= htmlspecialchars($review['review']) ?>"
                    </div>
                    <div class="review-actions">
                        <a href="edit_review.php?id=<?= $review['id'] ?>" class="btn-edit">✏️ Edit Review</a>
                        <a href="hapus_review.php?id=<?= $review['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus review ini?')">🗑️ Hapus</a>
                    </div>
                    <small style="color:#888;">Diposting: <?= date('d M Y H:i', strtotime($review['created_at'])) ?></small>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <p style="font-size: 3rem;">📝</p>
            <h3>Belum Ada Review</h3>
            <p>Kamu belum mereview film apapun. Yuk, review film favoritmu!</p>
            <a href="../index.php" style="display: inline-block; margin-top: 20px; background: #6f1d1b; color: white; padding: 10px 25px; border-radius: 30px; text-decoration: none;">🎬 Lihat Film</a>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>🍵 CineView — Kelola review filmmu dengan mudah 🍃</p>
</footer>

</body>
</html>