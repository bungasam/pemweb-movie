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

    } elseif (strlen($password) < 8) {
        $pesan = 'Password minimal 8 karakter!';

    } elseif (!preg_match('/[0-9]/', $password)) {
        $pesan = 'Password harus mengandung minimal 1 angka!';

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
    <style>
        /* Style untuk password hint bertahap */
        .password-hint-container {
            margin-top: 0.3rem;
            min-height: 55px;
        }
        .password-hint {
            font-size: 0.8rem;
            padding: 0.5rem 0.7rem;
            border-radius: 4px;
            background: #1a1a1a;
            transition: all 0.3s ease;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        .password-hint .hint-text {
            transition: all 0.3s ease;
        }
        .password-hint.hint-step1 {
            border-left: 3px solid #ff9800;
        }
        .password-hint.hint-step2 {
            border-left: 3px solid #ff9800;
        }
        .password-hint.hint-success {
            border-left: 3px solid #4caf50;
            background: #1a2a1a;
        }
        .password-hint .hint-step {
            color: #ff9800;
            font-weight: 500;
        }
        .password-hint .hint-success-text {
            color: #4caf50;
            font-weight: 500;
        }
        .password-hint .hint-info {
            color: #888;
        }
        .password-hint .highlight {
            color: #fff;
            font-weight: 600;
        }
        .password-strength-bar {
            height: 3px;
            border-radius: 3px;
            background: #333;
            margin-top: 0.5rem;
            transition: all 0.3s;
            overflow: hidden;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: all 0.5s ease;
        }
        .strength-weak .password-strength-fill {
            width: 50%;
            background: #f44336;
        }
        .strength-strong .password-strength-fill {
            width: 100%;
            background: #4caf50;
        }
        #strength-text {
            font-size: 0.7rem;
            color: #666;
            margin-top: 0.2rem;
        }
        .konfirm-status {
            font-size: 0.75rem;
            margin-top: 0.3rem;
        }
    </style>
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
            <form method="POST" action="register.php" id="registerForm">

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
                            placeholder="Minimal 8 karakter, harus ada angka"
                            oninput="cekPasswordBertahap(this.value)"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-password"
                            onclick="togglePassword('password', this)"
                        >⌣</button>
                    </div>

                    <!-- HINT PASSWORD BERTAHAP (HANYA HURUF & ANGKA) -->
                    <div class="password-hint-container">
                        <div class="password-hint" id="passwordHint">
                            <span class="hint-text" id="hintText">Mulai ketik password...</span>
                        </div>
                    </div>

                    <!-- BAR KEKUATAN -->
                    <div class="password-strength-bar" id="strengthBar">
                        <div class="password-strength-fill" id="strengthFill"></div>
                    </div>
                    <div id="strength-text"></div>
                </div>

                <div class="form-group">
                    <label for="konfirm_password">Konfirmasi Password</label>
                    <div class="input-password-wrapper">
                        <input
                            type="password"
                            id="konfirm_password"
                            name="konfirm_password"
                            placeholder="Ulangi password"
                            oninput="cekKonfirmasi()"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-password"
                            onclick="togglePassword('konfirm_password', this)"
                        >⌣</button>
                    </div>
                    <div id="konfirm-status" class="konfirm-status"></div>
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

<script>
// =============================================
// TOGGLE PASSWORD VISIBILITY
// =============================================
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '⌵';
    } else {
        input.type = 'password';
        btn.textContent = '⌣';
    }
}

// =============================================
// CEK PASSWORD BERTAHAP (HANYA HURUF & ANGKA)
// =============================================
function cekPasswordBertahap(password) {
    const hasLength = password.length >= 8;
    const hasNumber = /[0-9]/.test(password);
    
    const hint = document.getElementById('passwordHint');
    const text = document.getElementById('hintText');

    // Hapus semua class
    hint.className = 'password-hint';

    // STEP 1: Cek panjang karakter
    if (password.length === 0) {
        // Belum ada input
        hint.className = 'password-hint hint-step1';
        text.innerHTML = '<span class="hint-info">Mulai ketik password...</span>';
    }
    else if (!hasLength) {
        // Belum mencapai 8 karakter
        hint.className = 'password-hint hint-step1';
        text.innerHTML = '<span class="hint-step">Langkah 1:</span> Password minimal <span class="highlight">8 karakter</span> (kurang ' + (8 - password.length) + ' lagi)';
    }
    // STEP 2: Sudah 8 karakter, cek angka
    else if (hasLength && !hasNumber) {
        hint.className = 'password-hint hint-step2';
        text.innerHTML = '<span class="hint-step">Langkah 2:</span> Tambahkan <span class="highlight">angka</span> (0-9)';
    }
    // STEP 3: Semua terpenuhi
    else if (hasLength && hasNumber) {
        hint.className = 'password-hint hint-success';
        text.innerHTML = '<span class="hint-success-text">Password kuat! Semua syarat terpenuhi</span>';
    }

    // Update strength bar (hanya 2 level)
    const bar = document.getElementById('strengthBar');
    const fill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strength-text');

    bar.className = 'password-strength-bar';

    if (password.length === 0) {
        fill.style.width = '0%';
        strengthText.textContent = '';
        return;
    }

    let level = '';
    let label = '';
    let progress = 0;
    
    if (hasLength && hasNumber) {
        level = 'strength-strong';
        label = 'Kuat';
        progress = 100;
    } else if (hasLength) {
        level = 'strength-weak';
        label = 'Sedang - tambahkan angka';
        progress = 50;
    } else {
        level = 'strength-weak';
        label = 'Lemah - minimal 8 karakter';
        progress = 25;
    }
    
    bar.classList.add(level);
    fill.style.width = progress + '%';
    strengthText.textContent = 'Kekuatan: ' + label;

    // Cek konfirmasi
    cekKonfirmasi();
}

// =============================================
// CEK KONFIRMASI PASSWORD
// =============================================
function cekKonfirmasi() {
    const password = document.getElementById('password').value;
    const konfirm = document.getElementById('konfirm_password').value;
    const status = document.getElementById('konfirm-status');

    if (konfirm.length === 0) {
        status.textContent = '';
        return;
    }

    if (password === konfirm) {
        status.innerHTML = 'Password cocok';
        status.style.color = '#4caf50';
    } else {
        status.innerHTML = 'Password tidak cocok';
        status.style.color = '#f44336';
    }
}

// =============================================
// VALIDASI FORM SEBELUM SUBMIT
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const konfirm = document.getElementById('konfirm_password').value;
            let errors = [];

            if (password.length < 8) errors.push('Password minimal 8 karakter');
            if (!/[0-9]/.test(password)) errors.push('Password harus mengandung angka');
            if (password !== konfirm) errors.push('Password dan konfirmasi tidak sama');

            if (errors.length > 0) {
                e.preventDefault();
                alert('Perbaiki error berikut:\n- ' + errors.join('\n- '));
                return false;
            }
        });
    }
});
</script>

</body>
</html>