<?php
// =============================================
// FILE: user/edit_review.php
// Fungsi: User mengedit review mereka sendiri
// =============================================

session_start();
include '../koneksi.php';

// Pastikan user login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

// Ambil ID review
$review_id = intval($_GET['id'] ?? 0);
if ($review_id == 0) {
    header("Location: dashboard.php?pesan=Review tidak ditemukan&tipe=error");
    exit;
}

// Ambil data review, pastikan milik user ini
$stmt = $pdo->prepare("
    SELECT r.*, f.judul 
    FROM reviews r 
    JOIN films f ON r.film_id = f.id 
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$review_id, $_SESSION['user_id']]);
$review = $stmt->fetch();

if (!$review) {
    header("Location: dashboard.php?pesan=Review tidak ditemukan&tipe=error");
    exit;
}

$pesan = '';

// Proses update review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating   = intval($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $pesan = "Rating tidak valid!";
    } elseif (empty($komentar)) {
        $pesan = "Komentar tidak boleh kosong!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, komentar = ? WHERE id = ?");
            $stmt->execute([$rating, $komentar, $review_id]);
            header("Location: dashboard.php?pesan=Review berhasil diupdate&tipe=sukses");
            exit;
        } catch (PDOException $e) {
            $pesan = "Gagal mengupdate review!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review — CineView</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="form-container">
    <div class="form-box">
        
        <div class="form-logo">
            <h2>Edit Review</h2>
            <p>Film: <?= htmlspecialchars($review['judul']) ?></p>
        </div>
        
        <?php if ($pesan): ?>
        <div class="alert alert-error"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            
            <div class="form-group">
                <label>Rating Bintang</label>
                <div class="rating-input">
                    <input type="radio" name="rating" id="bintang5" value="5" <?= $review['rating'] == 5 ? 'checked' : '' ?>>
                    <label for="bintang5" title="5 Bintang">★</label>
                    <input type="radio" name="rating" id="bintang4" value="4" <?= $review['rating'] == 4 ? 'checked' : '' ?>>
                    <label for="bintang4" title="4 Bintang">★</label>
                    <input type="radio" name="rating" id="bintang3" value="3" <?= $review['rating'] == 3 ? 'checked' : '' ?>>
                    <label for="bintang3" title="3 Bintang">★</label>
                    <input type="radio" name="rating" id="bintang2" value="2" <?= $review['rating'] == 2 ? 'checked' : '' ?>>
                    <label for="bintang2" title="2 Bintang">★</label>
                    <input type="radio" name="rating" id="bintang1" value="1" <?= $review['rating'] == 1 ? 'checked' : '' ?>>
                    <label for="bintang1" title="1 Bintang">★</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="komentar">Ulasan</label>
                <textarea id="komentar" name="komentar" rows="5" required><?= htmlspecialchars($review['komentar']) ?></textarea>
            </div>
            
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-biru">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="../script.js"></script>
<script>
function confirmLogout(event) {
    event.preventDefault();
    Swal.fire({
        title: '⚠️ Konfirmasi Logout',
        text: 'Apakah Anda yakin ingin logout dari CineView?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#BA3801',
        cancelButtonColor: '#555',
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal',
        background: '#1a1a1a',
        color: '#f0f0f0',
        iconColor: '#FFEC89'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../logout.php';
        }
    });
}
</script>
</body>
</html>