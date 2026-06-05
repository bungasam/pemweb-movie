<?php
// =============================================
// FILE: login.php
// Fungsi: Halaman login untuk user dan admin
// =============================================

session_start();
include 'koneksi.php';

// Kalau sudah login, langsung ke halaman sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$pesan = ''; // Variabel untuk menyimpan pesan error

// =============================================
// PROSES LOGIN: hanya berjalan saat form di-submit
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil dan bersihkan input dari form
    $username = trim($_POST['username']); // trim() hapus spasi di ujung
    $password = $_POST['password'];
    
    // Validasi: pastikan tidak kosong
    if (empty($username) || empty($password)) {
        $pesan = "Username dan password harus diisi!";
    } else {
        // Cari user berdasarkan username di database
        // Gunakan prepared statement untuk keamanan (hindari SQL Injection)
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username); // "s" = string
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        
        if ($user) {
            // User ditemukan, cek password
            // password_verify() membandingkan password asli dengan hash
            if (password_verify($password, $user['password'])) {
                // Password cocok! Simpan data ke SESSION
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                
                // Arahkan ke halaman sesuai role
                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $pesan = "Password salah!";
            }
        } else {
            $pesan = "Username tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CineView</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar sederhana -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
    </div>
</nav>

<!-- Form Login -->
<div class="form-container">
    <div class="form-box">
        
        <!-- Logo & judul form -->
        <div class="form-logo">
            <h2>Selamat Datang</h2>
            <p>Login untuk melanjutkan ke CineView</p>
        </div>
        
        <!-- Tampilkan pesan error jika ada -->
        <?php if ($pesan): ?>
        <div class="alert alert-error"><?= $pesan ?></div>
        <?php endif; ?>
        
        <!-- Form login -->
        <form method="POST" action="login.php">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Masukkan username"
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Masukkan password"
                       required>
            </div>
            
            <button type="submit" class="btn btn-merah btn-submit">
                Masuk
            </button>
        </form>
        
        <!-- Link ke halaman register -->
        <div class="form-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
        
    </div>
</div>

<script src="script.js"></script>
</body>
</html>