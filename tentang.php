<?php
// =============================================
// FILE: tentang.php
// Fungsi: Halaman tentang website & tim
// =============================================

session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — CineView</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="rekomendasi.php">Rekomendasi</a></li>
            <li><a href="tentang.php">Tentang</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="tentang-wrapper">
    
    <!-- Header -->
    <div style="margin-bottom:3rem;">
        <div style="font-size:0.75rem; letter-spacing:3px; text-transform:uppercase; color:#BA3801; font-weight:700; margin-bottom:0.5rem;">
            Tentang Kami
        </div>
        <h1 class="tentang-judul">CineView</h1>
        <p style="color:#aaa; font-size:1.1rem; line-height:1.8; max-width:600px; margin-top:0.5rem;">
            Platform ulasan dan rating film yang dibangun sebagai proyek akhir mata kuliah Pemrograman Web.
        </p>
    </div>
    
    <!-- Divider -->
    <div class="garis-dekorasi"></div>
    
    <!-- Deskripsi -->
    <div style="background:var(--card); border:1px solid var(--card-border); border-radius:10px; padding:2rem; margin-bottom:3rem;">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem; color:#FFEC89; margin-bottom:1rem;">Tentang Website Ini</h3>
        <p style="color:#aaa; line-height:1.8; margin-bottom:1rem;">
            CineView adalah website rating dan ulasan film yang memungkinkan pengguna untuk memberikan penilaian terhadap film-film yang telah mereka tonton. Website ini dibangun menggunakan teknologi <strong style="color:#f0f0f0;">HTML, CSS, PHP, JavaScript</strong>, dan database <strong style="color:#f0f0f0;">MySQL</strong>.
        </p>
        <p style="color:#aaa; line-height:1.8;">
            Pengguna dapat mendaftar, login, memberikan rating bintang (1–5), dan menulis komentar. Admin dapat mengelola seluruh data film, review, dan pengguna.
        </p>
    </div>
    
    <!-- Fitur -->
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:1.5rem;">
        Fitur <span style="color:#FFEC89;">Utama</span>
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1rem; margin-bottom:3rem;">
        <?php
        $fitur = [
            ['🎬', 'Katalog Film', 'Koleksi film dengan informasi lengkap'],
            ['⭐', 'Rating Bintang', 'Sistem rating 1-5 bintang dari pengguna'],
            ['💬', 'Ulasan', 'Tulis dan baca ulasan dari komunitas'],
            ['🔐', 'Autentikasi', 'Sistem login aman dengan session PHP'],
            ['👤', 'Profil User', 'Kelola riwayat ulasanmu'],
            ['🛡️', 'Admin Panel', 'Dashboard lengkap untuk administrator'],
        ];
        foreach ($fitur as $f):
        ?>
        <div style="background:var(--card); border:1px solid var(--card-border); border-radius:8px; padding:1.2rem;">
            <div style="font-size:1.8rem; margin-bottom:0.5rem;"><?= $f[0] ?></div>
            <div style="font-weight:700; color:#f0f0f0; margin-bottom:0.3rem;"><?= $f[1] ?></div>
            <div style="font-size:0.82rem; color:#aaa;"><?= $f[2] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Teknologi -->
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:1.5rem;">
        Teknologi <span style="color:#FFEC89;">yang Digunakan</span>
    </h2>
    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:3rem;">
        <?php
        $tech = ['HTML5', 'CSS3', 'PHP 8', 'MySQL', 'JavaScript', 'XAMPP'];
        foreach ($tech as $t):
        ?>
        <span style="background:rgba(186,56,1,0.15); border:1px solid #BA3801; color:#FFEC89; padding:0.4rem 1rem; border-radius:20px; font-size:0.85rem; font-weight:600;">
            <?= $t ?>
        </span>
        <?php endforeach; ?>
    </div>
    
    <!-- Tim Pengembang -->
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:1rem;">
        Tim <span style="color:#FFEC89;">Pengembang</span>
    </h2>
    <div class="garis-dekorasi"></div>
    <div class="tim-grid">
        <!-- GANTI nama tim sesuai anggota kelompok kalian -->
        <?php
        $tim = [
            ['A', 'Nama Anggota 1', 'Project Manager'],
            ['B', 'Nama Anggota 2', 'Backend Dev'],
            ['C', 'Nama Anggota 3', 'Frontend Dev'],
            ['D', 'Nama Anggota 4', 'Database'],
        ];
        foreach ($tim as $t):
        ?>
        <div class="tim-card">
            <div class="tim-avatar"><?= $t[0] ?></div>
            <div style="font-weight:700; color:#f0f0f0; font-size:0.95rem;"><?= $t[1] ?></div>
            <div style="font-size:0.78rem; color:#aaa; margin-top:0.2rem;"><?= $t[2] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-bawah">
            &copy; 2024 CineView &mdash; Tugas Akhir Pemrograman Web
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>