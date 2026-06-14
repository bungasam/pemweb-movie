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

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? 'error';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipe = 'error';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $pesan = "Username dan password harus diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                
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

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>
    </div>
</nav>

<div class="form-container">
    <div class="form-box">
        
        <div class="form-logo">
            <h2>Selamat Datang</h2>
            <p>Login untuk melanjutkan ke CineView</p>
        </div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe === 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
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
                <div class="input-password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan password"
                           required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword('password', this)">⌣</button>
                </div>
            </div>
            
            <div style="text-align:right; margin:-0.3rem 0 1rem;">
                <a href="forgot_password.php" style="color:#6b8fd4; font-size:0.88rem;">Lupa password?</a>
            </div>

            <button type="submit" class="btn btn-merah btn-submit">
                Masuk
            </button>
        </form>
        
        <div class="form-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
        
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
