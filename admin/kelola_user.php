<?php
// =============================================
// FILE: admin/kelola_user.php
// Fungsi: Admin melihat, hapus user, dan lihat review per user
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses hapus user
if (isset($_GET['hapus'])) {
    $hapus_id = intval($_GET['hapus']);
    
    if ($hapus_id == $_SESSION['user_id']) {
        $pesan_hapus = "Tidak bisa menghapus akun sendiri!";
        $tipe_hapus  = "error";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
            $stmt->execute([$hapus_id]);
            $pesan_hapus = "User berhasil dihapus!";
            $tipe_hapus  = "sukses";
        } catch (PDOException $e) {
            $pesan_hapus = "Gagal menghapus user!";
            $tipe_hapus  = "error";
        }
    }
}

// Ambil semua user (bukan admin)
$semua_user = $pdo->query("
    SELECT u.*, COUNT(r.id) AS jml_review
    FROM users u
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

// Kalau ada parameter lihat_review, ambil review user tersebut
$lihat_user_id = intval($_GET['lihat_review'] ?? 0);
$review_user   = [];
$nama_user_dipilih = '';

if ($lihat_user_id > 0) {
    $stmt_cek = $pdo->prepare("SELECT username FROM users WHERE id = ? AND role = 'user'");
    $stmt_cek->execute([$lihat_user_id]);
    $user_dipilih = $stmt_cek->fetch();
    
    if ($user_dipilih) {
        $nama_user_dipilih = $user_dipilih['username'];
        
        $stmt_rv = $pdo->prepare("
            SELECT r.*, f.judul AS judul_film, f.id AS film_id
            FROM reviews r
            JOIN films f ON r.film_id = f.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt_rv->execute([$lihat_user_id]);
        $review_user = $stmt_rv->fetchAll();
    }
}

$pesan = $_GET['pesan'] ?? ($pesan_hapus ?? '');
$tipe  = $_GET['tipe']  ?? ($tipe_hapus  ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Panel review user */
        .panel-review-user {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .panel-review-user h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: #FFEC89;
            margin-bottom: 1rem;
        }
    </style>
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
        <a href="kelola_review.php">💬 Kelola Review</a>
        <a href="kelola_user.php" class="active">👥 Kelola User</a>
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:1rem;">
            Kelola User
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
                        <th>Username</th>
                        <th>Email</th>
                        <th>Review</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $ada_user = false;
                    foreach ($semua_user as $u):
                        $ada_user = true;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="color:#f0f0f0; font-weight:600;">
                            <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                                <span style="width:28px; height:28px; border-radius:50%; background:#BA3801; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:white;">
                                    <?= strtoupper(substr($u['username'],0,1)) ?>
                                </span>
                                <?= htmlspecialchars($u['username']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <!-- Jumlah review bisa diklik untuk lihat detail -->
                            <?php if ($u['jml_review'] > 0): ?>
                            <a href="kelola_user.php?lihat_review=<?= $u['id'] ?>#panel-review"
                               style="background:rgba(74,105,179,0.2); border:1px solid #4A69B3; color:#6b8fd4; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.8rem; text-decoration:none; cursor:pointer;">
                                <?= $u['jml_review'] ?> review ↗
                            </a>
                            <?php else: ?>
                            <span style="background:rgba(74,105,179,0.2); border:1px solid #333; color:#555; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.8rem;">
                                0 review
                            </span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="kelola_user.php?hapus=<?= $u['id'] ?>"
                               class="btn btn-hapus btn-kecil"
                               onclick="return konfirmasiHapus('Hapus user <?= addslashes($u['username']) ?>? Semua reviewnya juga akan terhapus.')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (!$ada_user): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:2rem; color:#aaa;">
                            Belum ada user terdaftar
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Panel review user yang dipilih -->
        <?php if ($lihat_user_id > 0 && $nama_user_dipilih): ?>
        <div class="panel-review-user" id="panel-review">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3>Review dari: <?= htmlspecialchars($nama_user_dipilih) ?></h3>
                <a href="kelola_user.php" class="btn btn-outline btn-kecil">✕ Tutup</a>
            </div>
            
            <?php if (count($review_user) > 0): ?>
            <div class="tabel-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Film</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no2 = 1; foreach ($review_user as $rv): ?>
                        <tr>
                            <td><?= $no2++ ?></td>
                            <td>
                                <a href="../detail.php?id=<?= $rv['film_id'] ?>"
                                   style="color:#FFEC89; text-decoration:none;" target="_blank">
                                    <?= htmlspecialchars($rv['judul_film']) ?> ↗
                                </a>
                            </td>
                            <td>
                                <span style="color:#FFEC89;">
                                    <?php for($i=1;$i<=5;$i++) echo $i<=$rv['rating']?'★':'☆'; ?>
                                </span>
                                <span style="color:#aaa; font-size:0.8rem;">(<?= $rv['rating'] ?>)</span>
                            </td>
                            <td style="max-width:250px; color:#ccc; font-size:0.88rem; line-height:1.5;">
                                <?= nl2br(htmlspecialchars($rv['komentar'])) ?>
                            </td>
                            <td><?= date('d M Y', strtotime($rv['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="color:#aaa;">User ini belum menulis review apapun.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>
