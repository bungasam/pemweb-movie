<?php
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Password tidak cocok!";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $query = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')");
            if ($query) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Pendaftaran gagal: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar - MatchaFlix 🍵</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .register-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .register-container h2 {
            color: #6f1d1b;
            margin-bottom: 20px;
        }
        .register-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 30px;
            border: 2px solid #95d5b2;
            font-family: 'Quicksand', sans-serif;
        }
        .register-container button {
            background: #6f1d1b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .register-container button:hover {
            background: #40916c;
        }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .login-link { margin-top: 20px; }
    </style>
</head>
<body style="background: #f4faf4;">

<header>
    <div class="header-decoration">🍃 🍵 🍃</div>
    <h1>🍵 MatchaFlix</h1>
    <p>Daftar akun baru ✨</p>
</header>

<div class="register-container">
    <h2>📝 Daftar Akun</h2>
    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
        <button type="submit">Daftar</button>
    </form>
    <div class="login-link">
        <p>Sudah punya akun? <a href="login.php" style="color:#6f1d1b;">Login di sini</a></p>
    </div>
    <a href="index.php" class="back-home">← Kembali ke Beranda</a>
</div>

<footer>
    <p>🍵 MatchaFlix — Sip manis seperti matcha latte 🍃</p>
</footer>

</body>
</html>