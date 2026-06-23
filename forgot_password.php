<?php
session_start();
require 'koneksi.php';

// User yang sudah login tidak perlu memakai halaman lupa password.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $pesan = 'Email harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    } else {
        // Cari email langsung dari tabel users menggunakan PDO.
        $stmt = $pdo->prepare(
            'SELECT id, username, email
             FROM users
             WHERE email = ?
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Simpan data akun sementara di session.
            // Session ini hanya dipakai untuk membuka form password baru.
            $_SESSION['reset_user_id'] = (int) $user['id'];
            $_SESSION['reset_username'] = $user['username'];
            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_expired_at'] = time() + 600; // Berlaku 10 menit.

            header('Location: reset_password.php');
            exit;
        }

        $pesan = 'Email tidak ditemukan di database.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — CineView</title>
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
            <h2>Lupa Password</h2>
            <p>Masukkan email yang sudah terdaftar di database.</p>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Email Akun</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="contoh@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <button type="submit" class="btn btn-merah btn-submit">
                Cari Akun
            </button>
        </form>

        <div class="form-link">
            Ingat password? <a href="login.php">Kembali ke login</a>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
