<?php
// =============================================
// FILE: admin/edit_film.php
// Fungsi: Form untuk admin mengedit data film
// =============================================

session_start();
include '../koneksi.php';

// Proteksi: hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil ID film dari URL
$film_id = intval($_GET['id'] ?? 0);
if ($film_id == 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data film yang akan diedit
$stmt = mysqli_prepare($koneksi, "SELECT * FROM films WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $film_id);
mysqli_stmt_execute($stmt);
$film = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$film) {
    header("Location: dashboard.php?pesan=Film+tidak+ditemukan&tipe=error");
    exit;
}

$pesan = '';

// =============================================
// PROSES: Update data film
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $judul     = trim($_POST['judul']);
    $genre     = trim($_POST['genre']);
    $tahun     = intval($_POST['tahun']);
    $sutradara = trim($_POST['sutradara']);
    $sinopsis  = trim($_POST['sinopsis']);
    $poster    = $film['poster']; // Gunakan poster lama dulu
    
    if (empty($judul)) {
        $pesan = "Judul tidak boleh kosong!";
    } else {
        
        // Upload poster baru kalau ada
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $file     = $_FILES['poster'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $boleh    = ['jpg','jpeg','png','webp'];
            
            if (in_array($ekstensi, $boleh) && $file['size'] <= 2*1024*1024) {
                $nama_file = 'poster_' . time() . '.' . $ekstensi;
                if (move_uploaded_file($file['tmp_name'], '../img/' . $nama_file)) {
                    // Hapus poster lama (kalau bukan default)
                    if ($film['poster'] != 'default.jpg') {
                        @unlink('../img/' . $film['poster']); // @ untuk suppress error
                    }
                    $poster = $nama_file;
                }
            }
        }
        
        // Update data di database
        $stmt = mysqli_prepare($koneksi, "
            UPDATE films 
            SET judul=?, genre=?, tahun=?, sutradara=?, sinopsis=?, poster=?
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, "ssisssi", $judul, $genre, $tahun, $sutradara, $sinopsis, $poster, $film_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: dashboard.php?pesan=Film+berhasil+diupdate&tipe=sukses");
            exit;
        } else {
            $pesan = "Gagal mengupdate data!";
        }
    }
    
    // Refresh data film (tampilkan nilai terbaru di form)
    $stmt2 = mysqli_prepare($koneksi, "SELECT * FROM films WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $film_id);
    mysqli_stmt_execute($stmt2);
    $film = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Film — CineView Admin</title>
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
        <a href="kelola_user.php">👥 Kelola User</a>
        <div class="sidebar-title">Sistem</div>
        <a href="../logout.php">🚪 Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        <div style="max-width:700px;">
            
            <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:0.5rem;">
                Edit Film
            </h1>
            <p style="color:#aaa; margin-bottom:1rem;">ID: #<?= $film_id ?> &mdash; <?= htmlspecialchars($film['judul']) ?></p>
            <div class="garis-dekorasi"></div>
            
            <?php if ($pesan): ?>
            <div class="alert alert-error"><?= $pesan ?></div>
            <?php endif; ?>
            
            <!-- Poster saat ini -->
            <div style="margin-bottom:1.5rem;">
                <p style="font-size:0.8rem; color:#aaa; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px;">Poster Saat Ini</p>
                <img src="../img/<?= htmlspecialchars($film['poster']) ?>"
                     alt="Poster"
                     onerror="this.src='../img/default.jpg'"
                     style="height:150px; border-radius:6px; border:1px solid #333;">
            </div>
            
            <!-- Form edit film -->
            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Judul Film *</label>
                    <input type="text" name="judul" 
                           value="<?= htmlspecialchars($film['judul']) ?>" required>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label>Genre</label>
                        <input type="text" name="genre" 
                               value="<?= htmlspecialchars($film['genre']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" 
                               value="<?= $film['tahun'] ?>" min="1900" max="2030">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Sutradara</label>
                    <input type="text" name="sutradara" 
                           value="<?= htmlspecialchars($film['sutradara']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Sinopsis</label>
                    <textarea name="sinopsis"><?= htmlspecialchars($film['sinopsis']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Ganti Poster (Biarkan kosong jika tidak ingin ganti)</label>
                    <input type="file" id="poster" name="poster" accept="image/*" style="color:#aaa;">
                    <img id="preview-poster" src="" style="display:none; max-width:120px; margin-top:0.5rem; border-radius:6px;">
                </div>
                
                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-biru">Simpan Perubahan</button>
                    <a href="dashboard.php" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>