<?php
// =============================================
// FILE: admin/tambah_film.php
// Fungsi: Form untuk admin menambah film baru
// =============================================

session_start();
include '../koneksi.php';

// Proteksi: hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan  = '';
$sukses = false;

// =============================================
// PROSES: Simpan film baru ke database
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $judul     = trim($_POST['judul']);
    $genre     = trim($_POST['genre']);
    $tahun     = intval($_POST['tahun']);
    $sutradara = trim($_POST['sutradara']);
    $sinopsis  = trim($_POST['sinopsis']);
    $poster    = 'default.svg';
    
    if (empty($judul)) {
        $pesan = "Judul film harus diisi!";
    } elseif ($tahun < 1900 || $tahun > 2030) {
        $pesan = "Tahun tidak valid!";
    } else {
        
        // Upload poster (opsional)
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $file     = $_FILES['poster'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $boleh    = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ekstensi, $boleh)) {
                if ($file['size'] <= 2 * 1024 * 1024) {
                    $nama_file = 'poster_' . time() . '.' . $ekstensi;
                    $tujuan    = '../img/' . $nama_file;
                    
                    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                        $poster = $nama_file;
                    }
                } else {
                    $pesan = "Ukuran file maksimal 2MB!";
                }
            } else {
                $pesan = "Format file harus jpg, jpeg, png, atau webp!";
            }
        }
        
        // Simpan ke database
        if (empty($pesan)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO films (judul, genre, tahun, sutradara, sinopsis, poster) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$judul, $genre, $tahun, $sutradara, $sinopsis, $poster]);
                $sukses = true;
                $pesan  = "Film berhasil ditambahkan!";
            } catch (PDOException $e) {
                $pesan = "Gagal menyimpan. Coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Film — CineView Admin</title>
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
        <a href="tambah_film.php" class="active">➕ Tambah Film</a>
        <div class="sidebar-title">Kelola Lainnya</div>
        <a href="kelola_review.php">💬 Kelola Review</a>
        <a href="kelola_user.php">👥 Kelola User</a>
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        <div style="max-width:700px;">
            
            <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:0.5rem;">
                Tambah Film Baru
            </h1>
            <div class="garis-dekorasi"></div>
            
            <?php if ($pesan): ?>
            <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
            <?php endif; ?>
            
            <?php if ($sukses): ?>
            <div style="display:flex; gap:1rem;">
                <a href="tambah_film.php" class="btn btn-merah">+ Tambah Lagi</a>
                <a href="dashboard.php" class="btn btn-outline">← Kembali</a>
            </div>
            <?php else: ?>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Judul Film *</label>
                    <input type="text" name="judul" placeholder="Contoh: Avengers: Endgame"
                           value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>"
                           required>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="genre" required>
                            <option value="">-- Pilih Genre --</option>
                            <option value="Action" <?= isset($_POST['genre']) && $_POST['genre'] == 'Action' ? 'selected' : '' ?>>Action</option>
                            <option value="Comedy" <?= isset($_POST['genre']) && $_POST['genre'] == 'Comedy' ? 'selected' : '' ?>>Comedy</option>
                            <option value="Drama" <?= isset($_POST['genre']) && $_POST['genre'] == 'Drama' ? 'selected' : '' ?>>Drama</option>
                            <option value="Horror" <?= isset($_POST['genre']) && $_POST['genre'] == 'Horror' ? 'selected' : '' ?>>Horror</option>
                            <option value="Romance" <?= isset($_POST['genre']) && $_POST['genre'] == 'Romance' ? 'selected' : '' ?>>Romance</option>
                            <option value="Sci-Fi" <?= isset($_POST['genre']) && $_POST['genre'] == 'Sci-Fi' ? 'selected' : '' ?>>Sci-Fi</option>
                            <option value="Thriller" <?= isset($_POST['genre']) && $_POST['genre'] == 'Thriller' ? 'selected' : '' ?>>Thriller</option>
                            <option value="Adventure" <?= isset($_POST['genre']) && $_POST['genre'] == 'Adventure' ? 'selected' : '' ?>>Adventure</option>
                            <option value="Animation" <?= isset($_POST['genre']) && $_POST['genre'] == 'Animation' ? 'selected' : '' ?>>Animation</option>
                            <option value="Crime" <?= isset($_POST['genre']) && $_POST['genre'] == 'Crime' ? 'selected' : '' ?>>Crime</option>
                            <option value="Fantasy" <?= isset($_POST['genre']) && $_POST['genre'] == 'Fantasy' ? 'selected' : '' ?>>Fantasy</option>
                            <option value="Mystery" <?= isset($_POST['genre']) && $_POST['genre'] == 'Mystery' ? 'selected' : '' ?>>Mystery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun Rilis</label>
                        <input type="number" name="tahun" placeholder="2024"
                               min="1900" max="2030"
                               value="<?= isset($_POST['tahun']) ? intval($_POST['tahun']) : date('Y') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Sutradara</label>
                    <input type="text" name="sutradara" placeholder="Nama sutradara"
                           value="<?= isset($_POST['sutradara']) ? htmlspecialchars($_POST['sutradara']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Sinopsis</label>
                    <textarea name="sinopsis" placeholder="Deskripsi singkat film..."><?= isset($_POST['sinopsis']) ? htmlspecialchars($_POST['sinopsis']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Poster Film (Opsional)</label>
                    <input type="file" id="poster" name="poster" accept="image/*"
                           style="color:#aaa;">
                    <small style="color:#555; font-size:0.8rem;">Format: jpg, png, webp. Maks: 2MB</small>
                    
                    <div style="margin-top:0.8rem;">
                        <img id="preview-poster" src="" 
                             style="display:none; max-width:150px; border-radius:6px; border:1px solid #333;">
                    </div>
                </div>
                
                <div style="display:flex; gap:1rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-merah">Simpan Film</button>
                    <a href="dashboard.php" class="btn btn-outline">Batal</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>