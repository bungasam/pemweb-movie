<?php
// =============================================
// FILE: register.php
// Fungsi: Halaman pendaftaran akun baru
// Catatan: Menggunakan PDO, bukan mysqli
// =============================================

session_start();
include 'koneksi.php';

// Kalau pengguna sudah login, arahkan ke beranda.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pesan  = '';
$sukses = false;

// =============================================
// PROSES REGISTER
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form.
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $noHpForm = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirm_password'] ?? '';

    // Nomor HP hanya disimpan dalam bentuk angka.
    // Contoh: +62 812-3456-7890 menjadi 081234567890.
    $noHp = preg_replace('/[^0-9]/', '', $noHpForm);

    if (substr($noHp, 0, 2) === '62') {
        $noHp = '0' . substr($noHp, 2);
    } elseif (substr($noHp, 0, 1) === '8') {
        $noHp = '0' . $noHp;
    }

    // Validasi data satu per satu agar mudah dipahami.
    if ($username === '' || $email === '' || $noHp === '' || $password === '' || $konfirm === '') {
        $pesan = 'Semua kolom harus diisi!';

    } elseif (strlen($username) < 3) {
        $pesan = 'Username minimal 3 karakter!';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid!';

    } elseif (!preg_match('/^08[0-9]{8,12}$/', $noHp)) {
        $pesan = 'Nomor HP tidak valid. Gunakan nomor Indonesia, contohnya 081234567890.';

    } elseif (strlen($password) < 6) {
        $pesan = 'Password minimal 6 karakter!';

    } elseif ($password !== $konfirm) {
        $pesan = 'Password dan konfirmasi password tidak sama!';

    } else {
        // Cek username agar tidak digunakan oleh dua akun.
        $cekUsername = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $cekUsername->execute([$username]);

        // Cek email agar tidak digunakan oleh dua akun.
        $cekEmail = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $cekEmail->execute([$email]);

        // Cek nomor HP agar tidak digunakan oleh dua akun.
        $cekNoHp = $pdo->prepare('SELECT id FROM users WHERE no_hp = ? LIMIT 1');
        $cekNoHp->execute([$noHp]);

        if ($cekUsername->fetch()) {
            $pesan = 'Username sudah digunakan. Silakan pilih username lain.';

        } elseif ($cekEmail->fetch()) {
            $pesan = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';

        } elseif ($cekNoHp->fetch()) {
            $pesan = 'Nomor HP sudah terdaftar. Silakan gunakan nomor HP lain.';

        } else {
            // Password tidak disimpan secara langsung, tetapi diubah menjadi hash.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Simpan akun baru menggunakan prepared statement PDO.
                $tambahUser = $pdo->prepare(
                    "INSERT INTO users (username, email, no_hp, password, role)
                     VALUES (?, ?, ?, ?, 'user')"
                );

                $tambahUser->execute([
                    $username,
                    $email,
                    $noHp,
                    $passwordHash
                ]);

                $sukses = true;
                $pesan  = 'Akun berhasil dibuat! Silakan login.';

            } catch (PDOException $e) {
                // Pesan aman untuk pengguna jika database menolak data ganda.
                if ($e->getCode() === '23000') {
                    $pesan = 'Email atau nomor HP sudah digunakan oleh akun lain.';
                } else {
                    $pesan = 'Gagal membuat akun. Silakan coba lagi.';
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

        <?php if ($pesan !== ''): ?>
            <div class="alert <?= $sukses ? 'alert-sukses' : 'alert-error' ?>">
                <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($sukses): ?>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" class="btn btn-merah btn-submit">Pergi ke Login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="register.php">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Minimal 3 karakter"
                        value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="contoh@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP</label>
                    <input
                        type="tel"
                        id="no_hp"
                        name="no_hp"
                        placeholder="Contoh: 081234567890"
                        value="<?= htmlspecialchars($_POST['no_hp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        inputmode="numeric"
                        maxlength="16"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
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

                    <div id="info-password" style="margin-top: 0.4rem; font-size: 0.8rem; color: #aaa;">
                        Minimal 6 karakter
                    </div>
                    <div id="bar-password" style="height: 4px; border-radius: 4px; background: #333; margin-top: 0.3rem; transition: all 0.3s;">
                        <div id="isi-bar-password" style="height: 100%; width: 0%; border-radius: 4px; transition: all 0.3s;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirm_password">Konfirmasi Password</label>
                    <div class="input-password-wrapper">
                        <input
                            type="password"
                            id="konfirm_password"
                            name="konfirm_password"
                            placeholder="Ulangi password"
                            oninput="cekKonfirmasiPassword()"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-password"
                            onclick="togglePassword('konfirm_password', this)"
                        >⌣</button>
                    </div>
                    <div id="info-konfirm" style="margin-top: 0.4rem; font-size: 0.8rem; color: #aaa;"></div>
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
