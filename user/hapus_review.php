<?php
// =============================================
// FILE: user/hapus_review.php
// Fungsi: User menghapus review mereka sendiri
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$review_id = intval($_GET['id'] ?? 0);
$film_id   = intval($_GET['film_id'] ?? 0);

if ($review_id == 0) {
    header("Location: dashboard.php");
    exit;
}

// Hapus review (pastikan milik user ini)
try {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    
    if ($film_id > 0) {
        header("Location: ../detail.php?id=$film_id&pesan=Review+berhasil+dihapus&tipe=sukses");
    } else {
        header("Location: dashboard.php?pesan=Review+berhasil+dihapus&tipe=sukses");
    }
} catch (PDOException $e) {
    header("Location: dashboard.php?pesan=Gagal+menghapus+review&tipe=error");
}
exit;
?>