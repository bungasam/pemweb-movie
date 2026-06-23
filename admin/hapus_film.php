<?php
session_start();
include '../koneksi.php';

// Proteksi: hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$film_id = intval($_GET['id'] ?? 0);

if ($film_id == 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data film (untuk hapus posternya)
$stmt = $pdo->prepare("SELECT poster FROM films WHERE id = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch();

if ($film) {
    // Hapus poster dari folder img
    if (!empty($film['poster']) && $film['poster'] != 'default.jpg' && $film['poster'] != 'default.svg') {
        $path_poster = '../img/' . $film['poster'];
        if (file_exists($path_poster)) {
            unlink($path_poster);
        }
    }
    
    // Hapus data film dari database
    try {
        $stmt2 = $pdo->prepare("DELETE FROM films WHERE id = ?");
        $stmt2->execute([$film_id]);
        
        header("Location: dashboard.php?pesan=Film+berhasil+dihapus&tipe=sukses");
    } catch (PDOException $e) {
        header("Location: dashboard.php?pesan=Gagal+menghapus+film&tipe=error");
    }
} else {
    header("Location: dashboard.php?pesan=Film+tidak+ditemukan&tipe=error");
}
exit;
?>