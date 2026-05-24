<?php require_once 'koneksi.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tentang - MatchaFlix 🍵</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .about-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        .about-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .about-card h2 {
            color: #6f1d1b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .feature-item {
            background: #f4faf4;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
        }
        .feature-item h3 {
            color: #2d6a4f;
            margin-bottom: 10px;
        }
        .team-member {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: #f9f7f3;
            border-radius: 20px;
        }
        .team-avatar {
            font-size: 3rem;
        }
        @media (max-width: 600px) {
            .about-card {
                padding: 20px;
            }
            .team-member {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-decoration">🍃 🍵 🍃</div>
    <h1>🍵 MatchaFlix</h1>
    <p>Tentang Platform Review Film Kami 🎬</p>
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

<div class="about-container">
    <div class="about-card">
        <h2>📖 Tentang MatchaFlix</h2>
        <p style="line-height: 1.8; margin-bottom: 20px;">
            MatchaFlix adalah platform review film dan series yang terinspirasi dari kehangatan secangkir matcha latte. 
            Kami percaya bahwa setiap film punya cerita yang bisa dinikmati seperti menikmati matcha — pahit manis, 
            menghangatkan, dan meninggalkan kesan mendalam.
        </p>
        <p style="line-height: 1.8;">
            Dibangun dengan semangat berbagi rekomendasi tontonan berkualitas, MatchaFlix hadir untuk membantu kamu 
            menemukan film atau series terbaik berdasarkan review dari komunitas pecinta film.
        </p>
    </div>

    <div class="about-card">
        <h2>✨ Fitur Unggulan</h2>
        <div class="feature-list">
            <div class="feature-item">
                <h3>🎬 Database Film</h3>
                <p>Koleksi film dan series lengkap dengan deskripsi dan poster</p>
            </div>
            <div class="feature-item">
                <h3>⭐ Rating & Review</h3>
                <p>Berikan rating dan review untuk film favoritmu</p>
            </div>
            <div class="feature-item">
                <h3>🔍 Pencarian</h3>
                <p>Cari film dengan mudah berdasarkan judul</p>
            </div>
            <div class="feature-item">
                <h3>🏆 Rekomendasi</h3>
                <p>Dapatkan rekomendasi film dengan rating tertinggi</p>
            </div>
            <div class="feature-item">
                <h3>👑 Admin Panel</h3>
                <p>Kelola film dan review dengan mudah</p>
            </div>
            <div class="feature-item">
                <h3>📱 Responsive</h3>
                <p>Tampilan yang nyaman di berbagai perangkat</p>
            </div>
        </div>
    </div>

    <div class="about-card">
        <h2>🍃 Filosofi Matcha</h2>
        <p style="line-height: 1.8;">
            Matcha bukan sekadar minuman, tapi sebuah pengalaman. Setiap tegukan menghadirkan ketenangan dan kehangatan. 
            Seperti itulah MatchaFlix — sebuah ruang nyaman untuk berbagi pengalaman menonton, menemukan rekomendasi baru, 
            dan terhubung dengan sesama pecinta film. 🎬💚
        </p>
    </div>

    <div class="about-card">
        <h2>👥 Tim Pengembang</h2>
        <div class="team-member">
            <div class="team-avatar">🍵</div>
            <div>
                <h3 style="color: #6f1d1b;">MatchaFlix Team</h3>
                <p>Dibangun dengan 💚 oleh para pecinta film untuk para pecinta film</p>
                <p>Version 2.0 | 2026</p>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>🍵 MatchaFlix — Sip manis seperti matcha latte 🍃</p>
    <p>© 2026 MatchaFlix | Temukan cerita favoritmu</p>
</footer>

</body>
</html>