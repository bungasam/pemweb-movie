<?php
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - CineView 🍵</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .login-container h2 {
            color: #6f1d1b;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 30px;
            border: 2px solid #95d5b2;
            font-family: 'Quicksand', sans-serif;
        }
        .login-container button {
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
        .login-container button:hover {
            background: #40916c;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .register-link {
            margin-top: 20px;
        }
        .back-home {
            display: inline-block;
            margin-top: 20px;
            color: #40916c;
            text-decoration: none;
        }
    </style>
</head>
<body style="background: #f4faf4;">

<header>
    <div class="header-decoration">🍃 🍵 🍃</div>
    <h1>🍵 CineView</h1>
    <p>Login untuk mulai mereview film favoritmu 🎬</p>
</header>

<div class="login-container">
    <h2>🔐 Login</h2>
    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="register-link">
        <p>Belum punya akun? <a href="register.php" style="color:#6f1d1b;">Daftar di sini</a></p>
    </div>
    <a href="index.php" class="back-home">← Kembali ke Beranda</a>
</div>

<footer>
    <p>🍵 CineView — Sip manis seperti matcha latte 🍃</p>
</footer>

</body>
</html>