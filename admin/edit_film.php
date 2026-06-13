<?php
// =============================================
// FILE: admin/edit_film.php
// Fungsi: Form untuk admin mengedit data film (Layout 2 Kolom)
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
$sukses = false;

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
            
            if (in_array($ekstensi, $boleh) && $file['size'] <= 5*1024*1024) {
                $nama_file = 'poster_' . time() . '_' . uniqid() . '.' . $ekstensi;
                if (move_uploaded_file($file['tmp_name'], '../img/' . $nama_file)) {
                    // Hapus poster lama jika bukan default
                    if ($film['poster'] != 'default.jpg' && $film['poster'] != 'default.svg' && $film['poster'] != '') {
                        @unlink('../img/' . $film['poster']);
                    }
                    $poster = $nama_file;
                } else {
                    $pesan = "Gagal mengupload poster!";
                }
            } else {
                $pesan = "Format file harus jpg, jpeg, png, webp (maks 5MB)!";
            }
        }
        
        if (empty($pesan)) {
            try {
                $stmt = $pdo->prepare("UPDATE films SET judul=?, genre=?, tahun=?, sutradara=?, sinopsis=?, poster=? WHERE id=?");
                $stmt->execute([$judul, $genre, $tahun, $sutradara, $sinopsis, $poster, $film_id]);
                $sukses = true;
                $pesan  = "Film berhasil diupdate!";
                
                // Refresh data setelah update
                $stmt2 = $pdo->prepare("SELECT * FROM films WHERE id = ?");
                $stmt2->execute([$film_id]);
                $film = $stmt2->fetch();
                $genre_saat_ini = array_map('trim', explode(',', $film['genre']));
            } catch (PDOException $e) {
                $pesan = "Gagal mengupdate data!";
            }
        }
    }
    
    // Jika gagal, refresh data film
    if (!$sukses) {
        $stmt2 = $pdo->prepare("SELECT * FROM films WHERE id = ?");
        $stmt2->execute([$film_id]);
        $film = $stmt2->fetch();
        $genre_saat_ini = array_map('trim', explode(',', $film['genre']));
    }
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
        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #888;
        }
        .breadcrumb a { color: #FFEC89; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb span { color: #555; }
        
        /* Header */
        .form-header { margin-bottom: 1.5rem; }
        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #FFEC89;
            margin-bottom: 0.5rem;
        }
        .form-header p { color: #aaa; font-size: 0.9rem; }
        .garis-dekorasi {
            width: 60px;
            height: 3px;
            background: #BA3801;
            margin-top: 1rem;
            border-radius: 3px;
        }
        
        /* LAYOUT 2 KOLOM: FORM KIRI - POSTER KANAN */
        .two-column-layout {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .form-column {
            flex: 2;
            min-width: 300px;
        }
        
        .poster-column {
            flex: 1;
            min-width: 250px;
        }
        
        /* Form styling */
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 0.5rem;
        }
        .form-group label .required { color: #BA3801; }
        .form-group .hint {
            font-size: 0.7rem;
            color: #666;
            font-weight: normal;
            margin-left: 0.5rem;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #f0f0f0;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #BA3801;
        }
        .form-group input::placeholder, .form-group textarea::placeholder { color: #555; }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        
        /* Genre checkboxes */
        .genre-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 0.5rem;
        }
        .genre-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .genre-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #BA3801;
            cursor: pointer;
        }
        .genre-item label {
            font-size: 0.85rem;
            color: #ccc;
            cursor: pointer;
            margin-bottom: 0;
            font-weight: normal;
        }
        .genre-item input[type="checkbox"]:checked + label {
            color: #FFEC89;
            font-weight: 500;
        }
        
        /* Poster Card (Kanan) */
        .poster-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 16px;
            padding: 1.5rem;
            position: sticky;
            top: 80px;
        }
        
        .current-poster {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #333;
        }
        
        .current-poster .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 0.8rem;
        }
        
        .current-poster img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            border: 1px solid #333;
        }
        
        .poster-preview {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .poster-preview .preview-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 0.8rem;
        }
        
        .preview-box {
            background: #121212;
            border-radius: 12px;
            padding: 1rem;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #333;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            display: none;
        }
        
        .preview-placeholder {
            text-align: center;
            color: #555;
        }
        
        .preview-placeholder .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .upload-area {
            border: 2px dashed #333;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            background: #121212;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }
        
        .upload-area:hover {
            border-color: #BA3801;
            background: #1a1a1a;
        }
        
        .upload-area.drag-over {
            border-color: #BA3801;
            background: rgba(186,56,1,0.1);
        }
        
        .upload-icon { font-size: 2rem; margin-bottom: 0.3rem; }
        .upload-text { font-size: 0.8rem; color: #888; }
        .upload-text .browse { color: #FFEC89; cursor: pointer; }
        .upload-info { font-size: 0.7rem; color: #555; margin-top: 0.5rem; }
        .file-input { display: none; }
        
        .btn-group { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .btn-simpan {
            background: #BA3801;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-simpan:hover { background: #8f2c00; }
        .btn-batal {
            background: #2a2a2a;
            color: #aaa;
            border: 1px solid #333;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
        }
        .btn-batal:hover { background: #333; color: white; }
        
        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-sukses {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="../index.php">Lihat Website</a></li>
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
        
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <a href="kelola_film.php">Kelola Film</a>
            <span>/</span>
            <span style="color:#FFEC89;">Edit Film</span>
        </div>
        
        <!-- Header -->
        <div class="form-header">
            <h1>Edit Film</h1>
            <p>ID: #<?= $film_id ?> — <?= htmlspecialchars($film['judul']) ?></p>
            <div class="garis-dekorasi"></div>
        </div>
        
        <!-- Alert -->
        <?php if ($pesan): ?>
        <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($sukses): ?>
        <div style="display:flex; gap:1rem; margin-bottom:1rem;">
            <a href="kelola_film.php" class="btn-simpan">← Kembali ke Kelola Film</a>
        </div>
        <?php endif; ?>
        
        <!-- LAYOUT 2 KOLOM -->
        <form method="POST" enctype="multipart/form-data" id="filmForm">
            <div class="two-column-layout">
                
                <!-- KOLOM KIRI: FORM INPUT -->
                <div class="form-column">
                    
                    <!-- Judul -->
                    <div class="form-group">
                        <label>Judul Film <span class="required">*</span> <span class="hint">(maks. 150 karakter)</span></label>
                        <input type="text" 
                               name="judul" 
                               placeholder="Contoh: The Dark Horizon"
                               value="<?= htmlspecialchars($film['judul']) ?>"
                               maxlength="150"
                               required>
                        <small style="color:#555; font-size:0.7rem;">Masukkan judul resmi film (maks. 150 karakter)</small>
                    </div>
                    
                    <!-- Genre Checkbox -->
                    <div class="form-group">
                        <label>Genre <span class="required">*</span> <span class="hint">(pilih satu atau lebih)</span></label>
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
                    
                    <!-- Tahun & Sutradara (2 kolom) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tahun Rilis <span class="required">*</span></label>
                            <input type="number" 
                                   name="tahun" 
                                   placeholder="Contoh: 2024"
                                   min="1900" 
                                   max="2030" 
                                   required
                                   value="<?= $film['tahun'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Sutradara <span class="required">*</span></label>
                            <input type="text" 
                                   name="sutradara" 
                                   placeholder="Contoh: Joko Anwar"
                                   required
                                   value="<?= htmlspecialchars($film['sutradara']) ?>">
                            <small style="color:#555; font-size:0.7rem;">Nama sutradara utama film</small>
                        </div>
                    </div>
                    
                    <!-- Sinopsis -->
                    <div class="form-group">
                        <label>Sinopsis <span class="required">*</span></label>
                        <textarea name="sinopsis" 
                                  placeholder="Tuliskan sinopsis film secara lengkap dan menarik..." 
                                  required><?= htmlspecialchars($film['sinopsis']) ?></textarea>
                    </div>
                    
                    <!-- Tombol -->
                    <div class="btn-group">
                        <button type="submit" class="btn-simpan">Simpan Perubahan</button>
                        <a href="kelola_film.php" class="btn-batal">Batal</a>
                    </div>
                    
                </div>
                
                <!-- KOLOM KANAN: POSTER PREVIEW & UPLOAD -->
                <div class="poster-column">
                    <div class="poster-card">
                        
                        <!-- Poster Saat Ini -->
                        <div class="current-poster">
                            <div class="label">POSTER SAAT INI</div>
                            <img src="../img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg') ?>"
                                 alt="Poster"
                                 onerror="this.src='../img/default.svg'">
                        </div>
                        
                        <div class="poster-preview">
                            <div class="preview-label">PREVIEW POSTER BARU</div>
                            <div class="preview-box" id="previewBox">
                                <div id="previewPlaceholder" class="preview-placeholder">
                                    <div class="icon">🖼️</div>
                                    <div style="font-size:0.8rem;">Belum ada gambar baru</div>
                                </div>
                                <img id="previewImage" class="preview-image" src="#" alt="Preview">
                            </div>
                        </div>
                        
                        <!-- Upload Area -->
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">📁</div>
                            <div class="upload-text">
                                Seret & lepas file di sini atau <span class="browse" id="browseBtn">Pilih File</span>
                            </div>
                            <div class="upload-info">
                                Format: JPG, PNG, WEBP (maks. 5MB)
                                <br>Kosongkan jika tidak ingin mengganti poster
                            </div>
                        </div>
                        
                        <input type="file" 
                               id="posterInput" 
                               name="poster" 
                               accept="image/jpeg,image/png,image/webp" 
                               class="file-input">
                               
                    </div>
                </div>
                
            </div>
        </form>
        
    </main>
</div>

<script>
// ============================================
// DRAG & DROP UPLOAD + PREVIEW
// ============================================

const uploadArea = document.getElementById('uploadArea');
const posterInput = document.getElementById('posterInput');
const browseBtn = document.getElementById('browseBtn');
const previewImage = document.getElementById('previewImage');
const previewPlaceholder = document.getElementById('previewPlaceholder');

// Browse button
browseBtn.addEventListener('click', function(e) {
    e.preventDefault();
    posterInput.click();
});

// Upload area click
uploadArea.addEventListener('click', function(e) {
    if (e.target === uploadArea || e.target.classList.contains('upload-icon') || e.target.classList.contains('upload-text')) {
        posterInput.click();
    }
});

// Drag & Drop
uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        posterInput.files = files;
        handleFilePreview(files[0]);
    }
});

// File input change
posterInput.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        handleFilePreview(this.files[0]);
    } else {
        // Reset preview jika tidak ada file
        previewImage.style.display = 'none';
        previewPlaceholder.style.display = 'block';
    }
});

// Preview function
function handleFilePreview(file) {
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Format file harus JPG, PNG, atau WEBP!');
        posterInput.value = '';
        previewImage.style.display = 'none';
        previewPlaceholder.style.display = 'block';
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file maksimal 5MB!');
        posterInput.value = '';
        previewImage.style.display = 'none';
        previewPlaceholder.style.display = 'block';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        previewImage.src = e.target.result;
        previewImage.style.display = 'block';
        previewPlaceholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// ============================================
// KONFIRMASI LOGOUT DENGAN SWEETALERT
// ============================================
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