<?php
// =============================================
// FILE: admin/dashboard.php
// Fungsi: Halaman utama panel admin
// =============================================

session_start();
include '../koneksi.php'; // Naik satu folder ke root

// ---- Cek apakah yang akses adalah admin ----
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// ---- Ambil statistik untuk dashboard menggunakan PDO ----
$total_film   = $pdo->query("SELECT COUNT(*) FROM films")->fetchColumn();
$total_review = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$total_user   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$rata_rating  = $pdo->query("SELECT ROUND(AVG(rating),1) FROM reviews")->fetchColumn();
$rata_rating = $rata_rating ?: 0;

// ---- Ambil film terbaru ----
$film_terbaru = $pdo->query("SELECT * FROM films ORDER BY id DESC LIMIT 5");

// ---- Ambil review terbaru ----
$review_terbaru = $pdo->query("
    SELECT r.*, u.username, u.foto AS foto_profil, f.judul 
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
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="../index.php">Lihat Website</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">
    
    <aside class="sidebar">
        <a href="dashboard.php" class="active">Dashboard</a>

        <a href="kelola_film.php">Kelola Film</a>
        
        <a href="kelola_review.php">Kelola Review</a>
        
        <a href="kelola_user.php">Kelola User</a>
        
        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <div style="margin-bottom:2rem;">
            <h1 style="font-family:'Playfair Display',serif; font-size:2rem; color:#FFEC89;">
                Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!
            </h1>
            <p style="color:#aaa; font-size:0.9rem;">Panel administrasi CineView</p>
        </div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
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
        
        <div id="film" style="margin-bottom:2rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem;">Film Terbaru</h2>
                <a href="kelola_film.php" class="btn btn-outline btn-kecil">Lihat Semua</a>
            </div>
            
            <div class="tabel-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Genre</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($film_terbaru as $f): ?>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
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
                        <?php foreach ($review_terbaru as $r): ?>
                        <tr>
                            <td style="color:#f0f0f0;">
                                <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                                    <?php
                                    $nama_foto = basename((string) ($r['foto_profil'] ?? ''));
                                    $lokasi_foto = __DIR__ . '/../img/' . $nama_foto;
                                    ?>

                                    <?php if ($nama_foto !== '' && is_file($lokasi_foto)): ?>
                                        <img
                                            src="../img/<?= htmlspecialchars($nama_foto, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($lokasi_foto) ?>"
                                            alt="Foto profil"
                                            class="avatar-kecil avatar-kecil-foto"
                                        >
                                    <?php else: ?>
                                        <span class="avatar-kecil">
                                            <?= htmlspecialchars(strtoupper(substr($r['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endif; ?>

                                    <?= htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($r['judul']) ?></td>
                            <td>
                                <span style="color:#FFEC89;">
                                    <?php for ($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <a href="hapus_review.php?id=<?= $r['id'] ?>&asal=dashboard"
                                   class="btn btn-hapus btn-kecil"
                                   onclick="return konfirmasiHapus('Hapus review ini?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>
</div>

<script src="admin.js"></script>
</body>
</html>