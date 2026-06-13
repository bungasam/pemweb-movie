<?php
// =============================================
// FILE: admin/edit_film.php
// Fungsi: Form untuk admin mengedit data film
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$film_id = intval($_GET['id'] ?? 0);
if ($film_id == 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM films WHERE id = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch();

if (!$film) {
    header("Location: dashboard.php?pesan=Film+tidak+ditemukan&tipe=error");
    exit;
}

$pesan = '';

// Daftar genre yang tersedia
$daftar_genre = ['Action','Adventure','Animation','Comedy','Crime','Drama','Fantasy','Horror','Mystery','Romance','Sci-Fi','Thriller'];

// Genre film saat ini (pecah dari string "Action, Drama" jadi array)
$genre_saat_ini = array_map('trim', explode(',', $film['genre']));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $judul     = trim($_POST['judul']);
    $tahun     = intval($_POST['tahun']);
    $sutradara = trim($_POST['sutradara']);
    $sinopsis  = trim($_POST['sinopsis']);
    $poster    = $film['poster'];
    
    $genre_dipilih = isset($_POST['genre']) ? $_POST['genre'] : [];
    $genre         = implode(', ', $genre_dipilih);
    
    // Validasi semua field wajib
    if (empty($judul)) {
        $pesan = "Judul tidak boleh kosong!";
    } elseif (empty($genre)) {
        $pesan = "Pilih minimal satu genre!";
    } elseif ($tahun < 1900 || $tahun > 2030) {
        $pesan = "Tahun tidak valid!";
    } elseif (empty($sutradara)) {
        $pesan = "Nama sutradara harus diisi!";
    } elseif (empty($sinopsis)) {
        $pesan = "Sinopsis harus diisi!";
    } else {
        
        // Upload poster baru (opsional saat edit)
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $file     = $_FILES['poster'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $boleh    = ['jpg','jpeg','png','webp'];
            
            if (in_array($ekstensi, $boleh) && $file['size'] <= 2*1024*1024) {
                $nama_file = 'poster_' . time() . '.' . $ekstensi;
                if (move_uploaded_file($file['tmp_name'], '../img/' . $nama_file)) {
                    if ($film['poster'] != 'default.jpg' && $film['poster'] != 'default.svg') {
                        @unlink('../img/' . $film['poster']);
                    }
                    $poster = $nama_file;
                }
            }
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE films SET judul=?, genre=?, tahun=?, sutradara=?, sinopsis=?, poster=? WHERE id=?");
            $stmt->execute([$judul, $genre, $tahun, $sutradara, $sinopsis, $poster, $film_id]);
            
            header("Location: dashboard.php?pesan=Film+berhasil+diupdate&tipe=sukses");
            exit;
        } catch (PDOException $e) {
            $pesan = "Gagal mengupdate data!";
        }
    }
    
    // Refresh data film setelah gagal
    $stmt2 = $pdo->prepare("SELECT * FROM films WHERE id = ?");
    $stmt2->execute([$film_id]);
    $film = $stmt2->fetch();
    $genre_saat_ini = array_map('trim', explode(',', $film['genre']));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Film — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
        <a href="dashboard.php">Dashboard</a>

        <a href="kelola_film.php">Kelola Film</a>

        <a href="kelola_review.php">Kelola Review</a>

        <a href="kelola_user.php">Kelola User</a>

        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        <div style="max-width:700px;">
            
            <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:0.5rem;">
                Edit Film
            </h1>
            <p style="color:#aaa; margin-bottom:1rem;">ID: #<?= $film_id ?> &mdash; <?= htmlspecialchars($film['judul']) ?></p>
            <div class="garis-dekorasi"></div>
            
            <?php if ($pesan): ?>
            <div class="alert alert-error"><?= htmlspecialchars($pesan) ?></div>
            <?php endif; ?>
            
            <div style="margin-bottom:1.5rem;">
                <p style="font-size:0.8rem; color:#aaa; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px;">Poster Saat Ini</p>
                <img src="../img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg') ?>"
                     alt="Poster"
                     onerror="this.src='../img/default.svg'"
                     style="height:150px; border-radius:6px; border:1px solid #333;">
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Judul Film <span style="color:#BA3801;">*</span></label>
                    <input type="text" name="judul" 
                           value="<?= htmlspecialchars($film['judul']) ?>" required>
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
                                   <?= in_array($g, $genre_saat_ini) ? 'checked' : '' ?>>
                            <label for="genre_<?= $g ?>"><?= $g ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label>Tahun <span style="color:#BA3801;">*</span></label>
                        <input type="number" name="tahun" 
                               value="<?= $film['tahun'] ?>" min="1900" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label>Sutradara <span style="color:#BA3801;">*</span></label>
                        <input type="text" name="sutradara" required
                               value="<?= htmlspecialchars($film['sutradara']) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Sinopsis <span style="color:#BA3801;">*</span></label>
                    <textarea name="sinopsis" required><?= htmlspecialchars($film['sinopsis']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Ganti Poster <small style="color:#555; font-weight:400;">(biarkan kosong jika tidak ingin ganti)</small></label>
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
