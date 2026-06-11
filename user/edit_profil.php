<?php
// =============================================
// FILE: user/edit_profil.php
// Fungsi: Halaman edit profil user (nama & foto)
// =============================================

session_start();
include '../koneksi.php';

// Proteksi: harus sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user saat ini
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$pesan  = '';
$sukses = false;

// =============================================
// PROSES: Update profil
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username_baru = trim($_POST['username']);
    $foto          = $user['foto']; // Default pakai foto lama
    
    // Validasi username
    if (empty($username_baru)) {
        $pesan = "Username tidak boleh kosong!";
    } elseif (strlen($username_baru) < 3) {
        $pesan = "Username minimal 3 karakter!";
    } else {
        
        // Cek kalau username sudah dipakai user lain
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $cek->execute([$username_baru, $user_id]);
        
        if ($cek->rowCount() > 0) {
            $pesan = "Username sudah dipakai orang lain!";
        } else {
            
            // Upload foto profil (opsional)
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $file     = $_FILES['foto'];
                $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $boleh    = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (!in_array($ekstensi, $boleh)) {
                    $pesan = "Format foto harus jpg, png, atau webp!";
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $pesan = "Ukuran foto maksimal 2MB!";
                } else {
                    $nama_file = 'foto_' . $user_id . '_' . time() . '.' . $ekstensi;
                    $tujuan    = '../img/' . $nama_file;
                    
                    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                        // Hapus foto lama kalau bukan default
                        if (!empty($user['foto']) && $user['foto'] != 'default_user.png') {
                            $foto_lama = '../img/' . $user['foto'];
                            if (file_exists($foto_lama)) {
                                @unlink($foto_lama);
                            }
                        }
                        $foto = $nama_file;
                    } else {
                        $pesan = "Gagal mengupload foto!";
                    }
                }
            }
            
            // Update database kalau tidak ada error upload
            if (empty($pesan)) {
                try {
                    $stmt_update = $pdo->prepare("UPDATE users SET username = ?, foto = ? WHERE id = ?");
                    $stmt_update->execute([$username_baru, $foto, $user_id]);
                    
                    // Update session dengan username baru
                    $_SESSION['username'] = $username_baru;
                    
                    $sukses = true;
                    $pesan  = "Profil berhasil diperbarui!";
                    
                    // Refresh data user
                    $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt2->execute([$user_id]);
                    $user = $stmt2->fetch();
                    
                } catch (PDOException $e) {
                    $pesan = "Gagal memperbarui profil!";
                }
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
    <title>Edit Profil — CineView</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="../index.php">Beranda</a></li>
            <li><a href="../rekomendasi.php">Rekomendasi</a></li>
            <li><a href="dashboard.php">Profil</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="form-container">
    <div class="form-box" style="max-width:500px;">
        
        <div class="form-logo">
            <h2>Edit Profil</h2>
            <p>Perbarui nama dan foto profilmu</p>
        </div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
        <!-- Foto profil saat ini -->
        <div style="text-align:center; margin-bottom:1.5rem;">
            <?php if (!empty($user['foto'])): ?>
                <img src="../img/<?= htmlspecialchars($user['foto']) ?>"
                     alt="Foto Profil"
                     onerror="this.style.display='none'; document.getElementById('avatar-fallback').style.display='flex';"
                     style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #BA3801;">
                <div id="avatar-fallback" style="display:none; width:90px; height:90px; border-radius:50%; background:#BA3801; color:white; font-size:2rem; font-weight:700; align-items:center; justify-content:center; margin:0 auto; border:3px solid #BA3801;">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php else: ?>
                <div style="width:90px; height:90px; border-radius:50%; background:#BA3801; color:white; font-size:2rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto; border:3px solid #BA3801;">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div style="font-size:0.8rem; color:#aaa; margin-top:0.5rem;">Foto profil saat ini</div>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="username">Username Baru</label>
                <input type="text"
                       id="username"
                       name="username"
                       placeholder="Minimal 3 karakter"
                       value="<?= htmlspecialchars($user['username']) ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="foto">Ganti Foto Profil</label>
                <input type="file"
                       id="foto"
                       name="foto"
                       accept="image/*"
                       style="color:#aaa;"
                       onchange="previewFoto(this)">
                <small style="color:#555; font-size:0.8rem;">Format: jpg, png, webp. Maks: 2MB. Biarkan kosong jika tidak ingin ganti.</small>
                
                <!-- Preview foto baru -->
                <div style="margin-top:0.8rem; text-align:center;">
                    <img id="preview-foto-baru" src="" 
                         style="display:none; width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #BA3801;">
                    <div id="label-preview" style="display:none; font-size:0.75rem; color:#aaa; margin-top:0.3rem;">Preview foto baru</div>
                </div>
            </div>
            
            <div style="display:flex; gap:1rem; margin-top:1rem;">
                <button type="submit" class="btn btn-merah btn-submit">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
        
    </div>
</div>

<script src="../script.js"></script>
<script>
// Preview foto sebelum upload
function previewFoto(input) {
    var preview = document.getElementById('preview-foto-baru');
    var label   = document.getElementById('label-preview');
    
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            label.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
