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
    
    // Ambil data dari form
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
        // filter_var() mengecek apakah email valid formatnya
        $pesan = "Format email tidak valid!";
        
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter!";
        
    } elseif ($password !== $konfirm) {
        $pesan = "Password dan konfirmasi password tidak sama!";
        
    } else {
        // Cek apakah username sudah dipakai
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($cek, "ss", $username, $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        
        if (mysqli_stmt_num_rows($cek) > 0) {
            $pesan = "Username atau email sudah terdaftar!";
        } else {
            // Hash password sebelum disimpan ke database
            // JANGAN simpan password asli! Selalu hash dulu.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan user baru ke database
            $simpan = mysqli_prepare($koneksi, 
                "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')"
            );
            mysqli_stmt_bind_param($simpan, "sss", $username, $email, $password_hash);
            
            if (mysqli_stmt_execute($simpan)) {
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
        
        <!-- Pesan sukses atau error -->
        <?php if ($pesan): ?>
        <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>">
            <?= $pesan ?>
        </div>
        <?php endif; ?>
        
        <?php if ($sukses): ?>
        <!-- Kalau sukses, tampilkan tombol ke login -->
        <div style="text-align:center; margin-top:1rem;">
            <a href="login.php" class="btn btn-merah btn-submit">Pergi ke Login</a>
        </div>
        <?php else: ?>
        <!-- Form registrasi -->
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
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 6 karakter"
                       required>
            </div>
            
            <div class="form-group">
                <label for="konfirm_password">Konfirmasi Password</label>
                <input type="password" 
                       id="konfirm_password" 
                       name="konfirm_password" 
                       placeholder="Ulangi password"
                       required>
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