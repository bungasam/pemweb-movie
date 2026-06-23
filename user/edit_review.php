<?php
session_start();
require_once '../koneksi.php';

// Hanya user yang sudah login yang boleh mengedit review.
if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'user'
) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$review_id = (int) ($_GET['id'] ?? 0);
$film_id_dari_url = (int) ($_GET['film_id'] ?? 0);

// Halaman asal dipakai agar tombol batal dan simpan kembali
// ke halaman tempat user membuka form edit.
$asal = $_GET['asal'] ?? 'dashboard';

if (!in_array($asal, ['dashboard', 'detail'], true)) {
    $asal = 'dashboard';
}

// Dukungan untuk link lama yang masih mengirim film_id.
if ($review_id <= 0 && $film_id_dari_url > 0) {
    $stmt_cari_id = $pdo->prepare(
        'SELECT id
         FROM reviews
         WHERE user_id = ? AND film_id = ?
         LIMIT 1'
    );
    $stmt_cari_id->execute([$user_id, $film_id_dari_url]);
    $id_ditemukan = $stmt_cari_id->fetchColumn();
    $review_id = $id_ditemukan ? (int) $id_ditemukan : 0;
}

if ($review_id <= 0) {
    header(
        'Location: dashboard.php?' .
        'pesan=' . urlencode('Review tidak ditemukan!') .
        '&tipe=error'
    );
    exit;
}

// Ambil review dan pastikan review tersebut milik user yang login.
$stmt_review = $pdo->prepare(
    'SELECT
        r.id,
        r.user_id,
        r.film_id,
        r.rating,
        r.komentar,
        f.judul
     FROM reviews r
     JOIN films f ON r.film_id = f.id
     WHERE r.id = ? AND r.user_id = ?
     LIMIT 1'
);
$stmt_review->execute([$review_id, $user_id]);
$review = $stmt_review->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header(
        'Location: dashboard.php?' .
        'pesan=' . urlencode('Review tidak ditemukan atau bukan milikmu!') .
        '&tipe=error'
    );
    exit;
}

// Tentukan halaman tujuan sesuai halaman sebelumnya.
if ($asal === 'detail') {
    $halaman_kembali = '../detail.php?id=' . (int) $review['film_id'];
} else {
    $halaman_kembali = 'dashboard.php';
}

$pesan = '';
$rating_form = (int) $review['rating'];
$komentar_form = (string) ($review['komentar'] ?? '');

// =============================================
// PROSES SIMPAN PERUBAHAN
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating_form = (int) ($_POST['rating'] ?? 0);
    $komentar_form = trim($_POST['komentar'] ?? '');
    $asal = $_POST['asal'] ?? 'dashboard';

    if (!in_array($asal, ['dashboard', 'detail'], true)) {
        $asal = 'dashboard';
    }

    if ($asal === 'detail') {
        $halaman_kembali = '../detail.php?id=' . (int) $review['film_id'];
    } else {
        $halaman_kembali = 'dashboard.php';
    }

    if ($rating_form < 1 || $rating_form > 5) {
        $pesan = 'Silakan pilih rating dari 1 sampai 5!';
    } elseif (strlen($komentar_form) > 1000) {
        $pesan = 'Ulasan maksimal 1000 karakter!';
    } else {
        // Komentar boleh kosong dan disimpan sebagai string kosong.
        $komentar_database = $komentar_form;

        try {
            $stmt_update = $pdo->prepare(
                'UPDATE reviews
                 SET rating = ?, komentar = ?
                 WHERE id = ? AND user_id = ?'
            );
            $stmt_update->execute([
                $rating_form,
                $komentar_database,
                $review_id,
                $user_id
            ]);

            $pemisah = strpos($halaman_kembali, '?') !== false ? '&' : '?';

            header(
                'Location: ' . $halaman_kembali .
                $pemisah .
                'pesan=' . urlencode('Review berhasil diperbarui!') .
                '&tipe=sukses'
            );
            exit;
        } catch (PDOException $e) {
            $pesan = 'Gagal memperbarui review!';
            error_log('Gagal update review: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review — CineView</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>

        <ul class="navbar-menu">
            <li>
                <a href="<?= htmlspecialchars($halaman_kembali, ENT_QUOTES, 'UTF-8') ?>">
                    ← Kembali
                </a>
            </li>
            <li>
                <a href="../logout.php" onclick="return confirmLogout(event, this.href)">
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="form-container">
    <div class="form-box">
        <div class="form-logo">
            <h2>Edit Review</h2>
            <p>
                Film: <?= htmlspecialchars($review['judul'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input
                type="hidden"
                name="asal"
                value="<?= htmlspecialchars($asal, ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="form-group">
                <label>Rating Bintang</label>

                <div class="rating-input">
                    <input type="radio" name="rating" id="bintang5" value="5" <?= $rating_form === 5 ? 'checked' : '' ?>>
                    <label for="bintang5" title="5 Bintang">★</label>

                    <input type="radio" name="rating" id="bintang4" value="4" <?= $rating_form === 4 ? 'checked' : '' ?>>
                    <label for="bintang4" title="4 Bintang">★</label>

                    <input type="radio" name="rating" id="bintang3" value="3" <?= $rating_form === 3 ? 'checked' : '' ?>>
                    <label for="bintang3" title="3 Bintang">★</label>

                    <input type="radio" name="rating" id="bintang2" value="2" <?= $rating_form === 2 ? 'checked' : '' ?>>
                    <label for="bintang2" title="2 Bintang">★</label>

                    <input type="radio" name="rating" id="bintang1" value="1" <?= $rating_form === 1 ? 'checked' : '' ?>>
                    <label for="bintang1" title="1 Bintang">★</label>
                </div>
            </div>

            <div class="form-group">
                <label for="komentar">Ulasan <span style="color:#777;"></label>

                <textarea
                    id="komentar"
                    name="komentar"
                    maxlength="1000"
                    placeholder="Tulis ulasanmu tentang film ini ..."
                ><?= htmlspecialchars($komentar_form, ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <div style="display:flex; gap:1rem; margin-top:1rem;">
                <button type="submit" class="btn btn-biru">
                    Simpan Perubahan
                </button>

                <a
                    href="<?= htmlspecialchars($halaman_kembali, ENT_QUOTES, 'UTF-8') ?>"
                    class="btn btn-outline"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script src="../script.js"></script>
</body>
</html>
