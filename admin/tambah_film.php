<?php
// =============================================
// FILE: admin/tambah_film.php
// Fungsi: Form untuk admin menambah film baru
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan  = '';
$sukses = false;

// Daftar genre yang tersedia
$daftar_genre = ['Action','Adventure','Animation','Comedy','Crime','Drama','Fantasy','Horror','Mystery','Romance','Sci-Fi','Thriller'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $judul     = trim($_POST['judul']);
    $tahun     = intval($_POST['tahun']);
    $sutradara = trim($_POST['sutradara']);
    $sinopsis  = trim($_POST['sinopsis']);
    $poster    = 'default.svg';
    
    // Genre: ambil dari array checkbox, gabung dengan koma
    $genre_dipilih = isset($_POST['genre']) ? $_POST['genre'] : [];
    $genre         = implode(', ', $genre_dipilih);
    
    // ---- Validasi semua field wajib ----
    if (empty($judul)) {
        $pesan = "Judul film harus diisi!";
    } elseif (empty($genre)) {
        $pesan = "Pilih minimal satu genre!";
    } elseif ($tahun < 1900 || $tahun > 2030) {
        $pesan = "Tahun tidak valid!";
    } elseif (empty($sutradara)) {
        $pesan = "Nama sutradara harus diisi!";
    } elseif (empty($sinopsis)) {
        $pesan = "Sinopsis harus diisi!";
    } else {
        
        // Upload poster (wajib)
        if (!isset($_FILES['poster']) || $_FILES['poster']['error'] != 0) {
            $pesan = "Poster film harus diupload!";
        } else {
            $file     = $_FILES['poster'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $boleh    = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($ekstensi, $boleh)) {
                $pesan = "Format file harus jpg, jpeg, png, atau webp!";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $pesan = "Ukuran file maksimal 2MB!";
            } else {
                $nama_file = 'poster_' . time() . '.' . $ekstensi;
                $tujuan    = '../img/' . $nama_file;
                
                if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                    $poster = $nama_file;
                } else {
                    $pesan = "Gagal mengupload poster!";
                }
            }
        }
        
        // Simpan ke database kalau tidak ada error
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
    <style>
        /* Styling checkbox genre */
        .genre-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .genre-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .genre-item input[type="checkbox"] {
            width: auto;
            accent-color: #BA3801;
            cursor: pointer;
        }
        .genre-item label {
            font-size: 0.88rem;
            color: #ccc;
            cursor: pointer;
            margin-bottom: 0;
        }
        .genre-item input[type="checkbox"]:checked + label {
            color: #FFEC89;
            font-weight: 600;
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
                    <label>Judul Film <span style="color:#BA3801;">*</span></label>
                    <input type="text" name="judul" placeholder="Contoh: Avengers: Endgame"
                           value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>"
                           required>
                </div>
                
                <!-- Genre: multi-checkbox -->
                <div class="form-group">
                    <label>Genre <span style="color:#BA3801;">*</span> <small style="color:#555; font-weight:400;">(pilih satu atau lebih)</small></label>
                    <div class="genre-grid">
                        <?php foreach ($daftar_genre as $g): ?>
                        <div class="genre-item">
                            <input type="checkbox"
                                   id="genre_<?= $g ?>"
                                   name="genre[]"
                                   value="<?= $g ?>"
                                   <?= (isset($_POST['genre']) && in_array($g, $_POST['genre'])) ? 'checked' : '' ?>>
                            <label for="genre_<?= $g ?>"><?= $g ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label>Tahun Rilis <span style="color:#BA3801;">*</span></label>
                        <input type="number" name="tahun" placeholder="2024"
                               min="1900" max="2030" required
                               value="<?= isset($_POST['tahun']) ? intval($_POST['tahun']) : date('Y') ?>">
                    </div>
                    <div class="form-group">
                        <label>Sutradara <span style="color:#BA3801;">*</span></label>
                        <input type="text" name="sutradara" placeholder="Nama sutradara" required
                               value="<?= isset($_POST['sutradara']) ? htmlspecialchars($_POST['sutradara']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Sinopsis <span style="color:#BA3801;">*</span></label>
                    <textarea name="sinopsis" placeholder="Deskripsi singkat film..." required><?= isset($_POST['sinopsis']) ? htmlspecialchars($_POST['sinopsis']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Poster Film <span style="color:#BA3801;">*</span></label>
                    <input type="file" id="poster" name="poster" accept="image/*"
                           style="color:#aaa;" required>
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
