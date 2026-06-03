<?php
// =============================================
// FILE: admin/hapus_film.php
// Fungsi: Menghapus data film dari database
// File ini hanya proses, tidak ada tampilan
// =============================================

session_start();
include '../koneksi.php';

// Proteksi: hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil ID dari URL
$film_id = intval($_GET['id'] ?? 0);

if ($film_id == 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data film (untuk hapus posternya)
$stmt = mysqli_prepare($koneksi, "SELECT poster FROM films WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $film_id);
mysqli_stmt_execute($stmt);
$film = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($film) {
    // Hapus poster dari folder img (kalau bukan gambar default)
    if ($film['poster'] != 'default.jpg') {
        $path_poster = '../img/' . $film['poster'];
        if (file_exists($path_poster)) {
            unlink($path_poster); // Hapus file fisik
        }
    }
    
    // Hapus data film dari database
    // Review terkait otomatis terhapus karena ON DELETE CASCADE di SQL
    $stmt2 = mysqli_prepare($koneksi, "DELETE FROM films WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $film_id);
    
    if (mysqli_stmt_execute($stmt2)) {
        header("Location: dashboard.php?pesan=Film+berhasil+dihapus&tipe=sukses");
    } else {
        header("Location: dashboard.php?pesan=Gagal+menghapus+film&tipe=error");
    }
} else {
    header("Location: dashboard.php?pesan=Film+tidak+ditemukan&tipe=error");
}
exit;
?>