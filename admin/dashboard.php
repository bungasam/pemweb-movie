<?php
// =============================================
// FILE: admin/dashboard.php
// Fungsi: Halaman utama panel admin
// =============================================

session_start();
include '../koneksi.php'; // Naik satu folder ke root

// ---- Cek apakah yang akses adalah admin ----
// Kalau bukan admin, paksa ke login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// ---- Ambil statistik untuk dashboard ----
$total_film    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM films"))[0];
$total_review  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM reviews"))[0];
$total_user    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$rata_rating   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT ROUND(AVG(rating),1) FROM reviews"))[0] ?? 0;

// ---- Ambil film terbaru ----
$film_terbaru  = mysqli_query($koneksi, "SELECT * FROM films ORDER BY id DESC LIMIT 5");

// ---- Ambil review terbaru ----
$review_terbaru = mysqli_query($koneksi, "
    SELECT r.*, u.username, f.judul 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN films f ON r.film_id = f.id 
    ORDER BY r.created_at DESC LIMIT 5
");

// Pesan dari aksi lain
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — CineView</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="../index.php">Lihat Website</a></li>
            <li><a href="../logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
        </ul>
    </div>
</nav>

<!-- LAYOUT DASHBOARD -->
<div class="dashboard-layout">
    
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        
        <div class="sidebar-title">Kelola Film</div>
        <a href="tambah_film.php">➕ Tambah Film</a>
        <a href="dashboard.php#film">🎬 Daftar Film</a>
        
        <div class="sidebar-title">Kelola Lainnya</div>
        <a href="kelola_review.php">💬 Kelola Review</a>
        <a href="kelola_user.php">👥 Kelola User</a>
        
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <!-- KONTEN UTAMA -->
    <main class="dashboard-konten">
        
        <!-- Header halaman -->
        <div style="margin-bottom:2rem;">
            <h1 style="font-family:'Playfair Display',serif; font-size:2rem; color:#FFEC89;">
                Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!
            </h1>
            <p style="color:#aaa; font-size:0.9rem;">Panel administrasi CineView</p>
        </div>
        
        <!-- Notifikasi -->
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
        <!-- ==============================
             KARTU STATISTIK
        ============================== -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-angka"><?= $total_film ?></div>
                <div class="stat-label">Total Film</div>
            </div>
            <div class="stat-card">
                <div class="stat-angka"><?= $total_review ?></div>
                <div class="stat-label">Total Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-angka"><?= $total_user ?></div>
                <div class="stat-label">Total User</div>
            </div>
            <div class="stat-card">
                <div class="stat-angka" style="color:#4A69B3;"><?= $rata_rating ?: '—' ?></div>
                <div class="stat-label">Rata-rata Rating</div>
            </div>
        </div>
        
        <!-- ==============================
             TABEL FILM TERBARU
        ============================== -->
        <div id="film" style="margin-bottom:2rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem;">Film Terbaru</h2>
                <a href="tambah_film.php" class="btn btn-merah btn-kecil">+ Tambah Film</a>
            </div>
            
            <div class="tabel-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Judul</th>
                            <th>Genre</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($f = mysqli_fetch_assoc($film_terbaru)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td style="color:#f0f0f0; font-weight:600;">
                                <?= htmlspecialchars($f['judul']) ?>
                            </td>
                            <td><?= htmlspecialchars($f['genre']) ?></td>
                            <td><?= $f['tahun'] ?></td>
                            <td>
                                <div style="display:flex; gap:0.4rem;">
                                    <a href="../detail.php?id=<?= $f['id'] ?>" 
                                       class="btn btn-outline btn-kecil">Lihat</a>
                                    <a href="edit_film.php?id=<?= $f['id'] ?>" 
                                       class="btn btn-biru btn-kecil">Edit</a>
                                    <a href="hapus_film.php?id=<?= $f['id'] ?>" 
                                       class="btn btn-hapus btn-kecil"
                                       onclick="return konfirmasiHapus('Hapus film <?= addslashes($f['judul']) ?>?')">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- ==============================
             REVIEW TERBARU
        ============================== -->
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem;">Review Terbaru</h2>
                <a href="kelola_review.php" class="btn btn-outline btn-kecil">Lihat Semua</a>
            </div>
            
            <div class="tabel-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Film</th>
                            <th>Rating</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($review_terbaru)): ?>
                        <tr>
                            <td style="color:#f0f0f0;"><?= htmlspecialchars($r['username']) ?></td>
                            <td><?= htmlspecialchars($r['judul']) ?></td>
                            <td>
                                <span style="color:#FFEC89;">
                                    <?php for ($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <a href="hapus_review.php?id=<?= $r['id'] ?>"
                                   class="btn btn-hapus btn-kecil"
                                   onclick="return konfirmasiHapus('Hapus review ini?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>