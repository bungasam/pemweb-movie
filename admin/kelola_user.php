<?php
// =============================================
// FILE: admin/kelola_user.php
// Fungsi: Admin melihat & menghapus akun user
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
                    if ($semua_user->rowCount() > 0):
                    foreach ($semua_user as $u): ?>
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
                            <span style="background:rgba(74,105,179,0.2); border:1px solid #4A69B3; color:#6b8fd4; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.8rem;">
                                <?= $u['jml_review'] ?> review
                            </span>
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
                    <?php endforeach;
                    else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:2rem; color:#aaa;">
                            Belum ada user terdaftar
                         </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>