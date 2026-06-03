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
$film_id   = intval($_GET['film_id'] ?? 0); // Untuk redirect balik ke detail film

if ($review_id == 0) {
    header("Location: kelola_review.php");
    exit;
}

// Hapus review dari database
$stmt = mysqli_prepare($koneksi, "DELETE FROM reviews WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $review_id);

if (mysqli_stmt_execute($stmt)) {
    // Redirect ke halaman yang sesuai
    if ($film_id > 0) {
        header("Location: ../detail.php?id=$film_id&pesan=Review+berhasil+dihapus&tipe=sukses");
    } else {
        header("Location: kelola_review.php?pesan=Review+berhasil+dihapus&tipe=sukses");
    }
} else {
    header("Location: kelola_review.php?pesan=Gagal+menghapus+review&tipe=error");
}
exit;
?>