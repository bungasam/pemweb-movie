<?php
// =============================================
// FILE: admin/kelola_review.php
// Fungsi: Admin melihat & menghapus semua review
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua review beserta info user dan film
$query = "
    SELECT r.*, u.username, f.judul AS judul_film
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN films f ON r.film_id = f.id
    ORDER BY r.created_at DESC
";
$semua_review = mysqli_query($koneksi, $query);

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Review — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php">📊 Dashboard</a>
        <div class="sidebar-title">Kelola Film</div>
        <a href="tambah_film.php">➕ Tambah Film</a>
        <div class="sidebar-title">Kelola Lainnya</div>
        <a href="kelola_review.php" class="active">💬 Kelola Review</a>
        <a href="kelola_user.php">👥 Kelola User</a>
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:1rem;">
            Kelola Review
        </h1>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <div class="tabel-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Film</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($semua_review) > 0):
                    while ($r = mysqli_fetch_assoc($semua_review)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="color:#f0f0f0; font-weight:600;"><?= htmlspecialchars($r['username']) ?></td>
                        <td><?= htmlspecialchars($r['judul_film']) ?></td>
                        <td>
                            <span style="color:#FFEC89;">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                            </span>
                            <span style="color:#aaa; font-size:0.8rem;">(<?= $r['rating'] ?>)</span>
                        </td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?= htmlspecialchars($r['komentar']) ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <a href="hapus_review.php?id=<?= $r['id'] ?>"
                               class="btn btn-hapus btn-kecil"
                               onclick="return konfirmasiHapus('Hapus review dari <?= addslashes($r['username']) ?>?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile;
                    else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">Belum ada review</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>