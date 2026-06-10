<?php
// =============================================
// FILE: admin/hapus_review.php
// Fungsi: Admin menghapus review manapun
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$review_id = intval($_GET['id'] ?? 0);
$film_id   = intval($_GET['film_id'] ?? 0);

if ($review_id == 0) {
    header("Location: kelola_review.php");
    exit;
}

// Hapus review dari database
try {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    
    if ($film_id > 0) {
        header("Location: ../detail.php?id=$film_id&pesan=Review+berhasil+dihapus&tipe=sukses");
    } else {
        header("Location: kelola_review.php?pesan=Review+berhasil+dihapus&tipe=sukses");
    }
} catch (PDOException $e) {
    header("Location: kelola_review.php?pesan=Gagal+menghapus+review&tipe=error");
}
exit;
?>