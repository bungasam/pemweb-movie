<?php
// =============================================
// FILE: user/hapus_review.php
// Fungsi: Menghapus review milik user
// Database: PDO
// =============================================

session_start();
require_once '../koneksi.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'user'
) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$review_id = (int) ($_GET['id'] ?? 0);
$asal = $_GET['asal'] ?? 'dashboard';

if (!in_array($asal, ['dashboard', 'detail'], true)) {
    $asal = 'dashboard';
}

if ($review_id <= 0) {
    header(
        'Location: dashboard.php?' .
        'pesan=' . urlencode('Review tidak ditemukan!') .
        '&tipe=error'
    );
    exit;
}

// Ambil film_id sebelum review dihapus.
// user_id ikut diperiksa agar user tidak bisa menghapus review orang lain.
$stmt_cek = $pdo->prepare(
    'SELECT id, film_id
     FROM reviews
     WHERE id = ? AND user_id = ?
     LIMIT 1'
);
$stmt_cek->execute([$review_id, $user_id]);
$review = $stmt_cek->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header(
        'Location: dashboard.php?' .
        'pesan=' . urlencode('Review tidak ditemukan atau bukan milikmu!') .
        '&tipe=error'
    );
    exit;
}

$film_id = (int) $review['film_id'];

try {
    $stmt_hapus = $pdo->prepare(
        'DELETE FROM reviews
         WHERE id = ? AND user_id = ?'
    );
    $stmt_hapus->execute([$review_id, $user_id]);

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
    error_log('Gagal menghapus review: ' . $e->getMessage());
}

// Kembali ke halaman tempat tombol hapus ditekan.
if ($asal === 'detail') {
    header(
        'Location: ../detail.php?id=' . $film_id .
        '&pesan=' . urlencode($pesan) .
        '&tipe=' . urlencode($tipe)
    );
    exit;
}

header(
    'Location: dashboard.php?' .
    'pesan=' . urlencode($pesan) .
    '&tipe=' . urlencode($tipe)
);
exit;
