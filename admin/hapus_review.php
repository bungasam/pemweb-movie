<?php
// =============================================
// FILE: admin/hapus_review.php
// Fungsi: Admin menghapus review pengguna
// Database: PDO
// =============================================

session_start();
require_once '../koneksi.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}

$review_id = (int) ($_GET['id'] ?? 0);
$asal = $_GET['asal'] ?? 'kelola_review';

// Admin bisa menghapus dari detail film, dashboard admin,
// atau halaman kelola review.
if (!in_array($asal, ['detail', 'dashboard', 'kelola_review'], true)) {
    $asal = 'kelola_review';
}

if ($review_id <= 0) {
    header(
        'Location: kelola_review.php?' .
        'pesan=' . urlencode('Review tidak ditemukan!') .
        '&tipe=error'
    );
    exit;
}

$stmt_cek = $pdo->prepare(
    'SELECT id, film_id
     FROM reviews
     WHERE id = ?
     LIMIT 1'
);
$stmt_cek->execute([$review_id]);
$review = $stmt_cek->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    $tujuan_gagal = $asal === 'dashboard' ? 'dashboard.php' : 'kelola_review.php';

    header(
        'Location: ' . $tujuan_gagal . '?' .
        'pesan=' . urlencode('Review tidak ditemukan!') .
        '&tipe=error'
    );
    exit;
}

$film_id = (int) $review['film_id'];

try {
    $stmt_hapus = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt_hapus->execute([$review_id]);

    if ($stmt_hapus->rowCount() > 0) {
        $pesan = 'Review berhasil dihapus!';
        $tipe = 'sukses';
    } else {
        $pesan = 'Review gagal dihapus!';
        $tipe = 'error';
    }
} catch (PDOException $e) {
    $pesan = 'Terjadi kesalahan saat menghapus review!';
    $tipe = 'error';
    error_log('Admin gagal menghapus review: ' . $e->getMessage());
}

// Kembali ke halaman tempat admin menekan tombol hapus.
if ($asal === 'detail') {
    header(
        'Location: ../detail.php?id=' . $film_id .
        '&pesan=' . urlencode($pesan) .
        '&tipe=' . urlencode($tipe)
    );
    exit;
}

if ($asal === 'dashboard') {
    header(
        'Location: dashboard.php?' .
        'pesan=' . urlencode($pesan) .
        '&tipe=' . urlencode($tipe)
    );
    exit;
}

header(
    'Location: kelola_review.php?' .
    'pesan=' . urlencode($pesan) .
    '&tipe=' . urlencode($tipe)
);
exit;
