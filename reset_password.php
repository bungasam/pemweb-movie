<?php
// =====================================================
// FILE: reset_password.php
// Fungsi: Membuat password baru untuk akun yang emailnya
//         sudah ditemukan pada forgot_password.php.
// Database tetap menggunakan PDO.
// =====================================================

session_start();
require 'koneksi.php';

$pesan = '';

// Pastikan halaman ini hanya dapat dibuka setelah email ditemukan.
$resetUserId = $_SESSION['reset_user_id'] ?? null;
$resetUsername = $_SESSION['reset_username'] ?? '';
$resetEmail = $_SESSION['reset_email'] ?? '';
$resetExpiredAt = $_SESSION['reset_expired_at'] ?? 0;

// Hapus session reset jika sudah melewati batas 10 menit.
if ($resetUserId && time() > $resetExpiredAt) {
    unset(
        $_SESSION['reset_user_id'],
        $_SESSION['reset_username'],
        $_SESSION['reset_email'],
        $_SESSION['reset_expired_at']
    );

    $resetUserId = null;
    $pesan = 'Waktu untuk mengganti password sudah habis. Masukkan email kembali.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetUserId) {
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirm_password'] ?? '';

    if (strlen($password) < 6) {
        $pesan = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $pesan = 'Password dan konfirmasi password tidak sama.';
    } else {
        // Password tidak disimpan dalam bentuk teks biasa.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update password menggunakan prepared statement PDO.
        $ubah = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $ubah->execute([$passwordHash, $resetUserId]);

        // Hapus session reset agar form tidak dapat dipakai berulang kali.
        unset(
            $_SESSION['reset_user_id'],
            $_SESSION['reset_username'],
            $_SESSION['reset_email'],
            $_SESSION['reset_expired_at']
        );

        header('Location: login.php?pesan=Password+berhasil+diubah.+Silakan+login.&tipe=sukses');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru — CineView</title>
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
            <h2>Buat Password Baru</h2>

            <?php if ($resetUserId): ?>
                <p>
                    Akun: <?= htmlspecialchars($resetUsername) ?><br>
                    Email: <?= htmlspecialchars($resetEmail) ?>
                </p>
            <?php else: ?>
                <p>Masukkan email akun terlebih dahulu.</p>
            <?php endif; ?>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <?php if ($resetUserId): ?>
            <form method="POST" action="reset_password.php">
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <div class="input-password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 6 karakter"
                            oninput="cekKekuatanPassword(this.value)"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-password"
                            onclick="togglePassword('password', this)"
                        >⌣</button>
                    </div>

                    <div id="info-password" style="margin-top:0.4rem;font-size:0.8rem;color:#aaa;">
                        Minimal 6 karakter
                    </div>
                    <div style="height:4px;border-radius:4px;background:#333;margin-top:0.3rem;">
                        <div id="isi-bar-password" style="height:100%;width:0%;border-radius:4px;transition:all 0.3s;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirm_password">Konfirmasi Password Baru</label>
                    <div class="input-password-wrapper">
                        <input
                            type="password"
                            id="konfirm_password"
                            name="konfirm_password"
                            placeholder="Ulangi password baru"
                            oninput="cekKonfirmasiPassword()"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-password"
                            onclick="togglePassword('konfirm_password', this)"
                        >⌣</button>
                    </div>
                    <div id="info-konfirm" style="margin-top:0.4rem;font-size:0.8rem;color:#aaa;"></div>
                </div>

                <button type="submit" class="btn btn-merah btn-submit">
                    Simpan Password Baru
                </button>
            </form>
        <?php else: ?>
            <div class="form-link">
                <a href="forgot_password.php">Masukkan email kembali</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
