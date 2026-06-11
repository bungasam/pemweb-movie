<?php
// =============================================
// FILE: register.php
// Fungsi: Halaman pendaftaran akun baru
// =============================================

session_start();
include 'koneksi.php';

// Kalau sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pesan  = '';
$sukses = false;

// =============================================
// PROSES REGISTER: saat form di-submit
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirm_password'];
    
    // ---- Validasi satu per satu ----
    if (empty($username) || empty($email) || empty($password)) {
        $pesan = "Semua kolom harus diisi!";
        
    } elseif (strlen($username) < 3) {
        $pesan = "Username minimal 3 karakter!";
        
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid!";
        
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter!";
        
    } elseif ($password !== $konfirm) {
        $pesan = "Password dan konfirmasi password tidak sama!";
        
    } else {
        // Cek apakah username atau email sudah dipakai
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $pesan = "Username atau email sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan user baru
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            
            if ($stmt->execute([$username, $email, $password_hash])) {
                $sukses = true;
                $pesan  = "Akun berhasil dibuat! Silakan login.";
            } else {
                $pesan = "Gagal membuat akun. Coba lagi.";
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
    <title>Daftar — CineView</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
    </div>
</nav>

<div class="form-container">
    <div class="form-box">
        
        <div class="form-logo">
            <h2>Buat Akun Baru</h2>
            <p>Bergabung dengan komunitas CineView</p>
        </div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($sukses): ?>
        <div style="text-align:center; margin-top:1rem;">
            <a href="login.php" class="btn btn-merah btn-submit">Pergi ke Login</a>
        </div>
        <?php else: ?>
        <form method="POST" action="register.php">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Minimal 3 karakter"
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="contoh@email.com"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Minimal 6 karakter"
                           oninput="cekKekuatanPassword(this.value)"
                           required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword('password', this)">👁️</button>
                </div>
                <!-- Indikator kekuatan password -->
                <div id="info-password" style="margin-top:0.4rem; font-size:0.8rem; color:#aaa;">
                    Minimal 6 karakter
                </div>
                <div id="bar-password" style="height:4px; border-radius:4px; background:#333; margin-top:0.3rem; transition:all 0.3s;">
                    <div id="isi-bar-password" style="height:100%; width:0%; border-radius:4px; transition:all 0.3s;"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="konfirm_password">Konfirmasi Password</label>
                <div class="input-password-wrapper">
                    <input type="password" 
                           id="konfirm_password" 
                           name="konfirm_password" 
                           placeholder="Ulangi password"
                           oninput="cekKonfirmasiPassword()"
                           required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword('konfirm_password', this)">👁️</button>
                </div>
                <div id="info-konfirm" style="margin-top:0.4rem; font-size:0.8rem; color:#aaa;"></div>
            </div>
            
            <button type="submit" class="btn btn-merah btn-submit">
                Buat Akun
            </button>
        </form>
        <?php endif; ?>
        
        <div class="form-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
