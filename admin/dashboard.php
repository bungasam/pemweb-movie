<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Statistik
$total_film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM film"))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
$total_review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM review"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MatchaFlix</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #6f1d1b, #2d6a4f);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #6f1d1b;
        }
        .admin-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px;
            flex-wrap: wrap;
        }
        .admin-nav a {
            background: #2d6a4f;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
        }
        .admin-nav a:hover {
            background: #6f1d1b;
        }
        .film-table, .review-table {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f4faf4;
            color: #2d6a4f;
        }
        .btn-edit, .btn-delete {
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            margin: 0 5px;
        }
        .btn-edit {
            background: #40916c;
            color: white;
        }
        .btn-delete {
            background: #6f1d1b;
            color: white;
        }
        .btn-add {
            background: #6f1d1b;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <h1>👑 Admin Dashboard</h1>
    <p>Selamat datang, <?= $_SESSION['username'] ?>! 🍵</p>
</div>

<nav>
    <a href="../index.php">🏠 Home</a>
    <a href="../rekomendasi.php">✨ Rekomendasi</a>
    <a href="../tentang.php">🎬 Tentang</a>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?= $total_film ?></div>
        <div>Total Film</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $total_user ?></div>
        <div>Total User</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $total_review ?></div>
        <div>Total Review</div>
    </div>
</div>

<div class="film-table">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>🎬 Kelola Film</h2>
        <a href="tambah_film.php" class="btn-add">+ Tambah Film</a>
    </div>
    
    <table>
        <thead>
            <tr><th>ID</th><th>Poster</th><th>Judul</th><th>Genre</th><th>Tahun</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $films = mysqli_query($conn, "SELECT * FROM film ORDER BY id DESC");
            while($film = mysqli_fetch_assoc($films)):
            ?>
            <tr>
                <td><?= $film['id'] ?></td>
                <td><img src="../img/<?= $film['poster'] ?>" width="50" height="70" style="object-fit:cover; border-radius:10px;" onerror="this.src='../img/default.jpg'"></td>
                <td><?= htmlspecialchars($film['judul']) ?></td>
                <td><?= htmlspecialchars($film['genre'] ?? '-') ?></td>
                <td><?= $film['tahun'] ?? '-' ?></td>
                <td>
                    <a href="edit_film.php?id=<?= $film['id'] ?>" class="btn-edit">Edit</a>
                    <a href="hapus_film.php?id=<?= $film['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus film ini? Semua review akan hilang!')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="review-table">
    <h2>📝 Kelola Review</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Film</th><th>Reviewer</th><th>Review</th><th>Rating</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $reviews = mysqli_query($conn, "
                SELECT r.*, f.judul as film_judul 
                FROM review r 
                JOIN film f ON r.film_id = f.id 
                ORDER BY r.created_at DESC
            ");
            while($review = mysqli_fetch_assoc($reviews)):
            ?>
            <tr>
                <td><?= $review['id'] ?></td>
                <td><?= htmlspecialchars($review['film_judul']) ?></td>
                <td><?= htmlspecialchars($review['nama_reviewer']) ?></td>
                <td style="max-width:300px;"><?= htmlspecialchars(substr($review['review'], 0, 50)) ?>...</td>
                <td>⭐ <?= $review['rating'] ?>/5</td>
                <td>
                    <a href="hapus_review.php?id=<?= $review['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus review ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<footer style="margin-top: 40px;">
    <p>🍵 MatchaFlix Admin Panel — Kelola film dengan mudah 🍃</p>
</footer>

</body>
</html>