<?php
// =============================================
// FILE: proses_review.php
// Fungsi: Memproses tambah review dari user
// File ini tidak punya tampilan (hanya proses)
// =============================================
 
session_start();
include 'koneksi.php';
 
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
 
// Pastikan bukan admin
if ($_SESSION['role'] == 'admin') {
    header("Location: index.php");
    exit;
}
 
// Pastikan request dari form POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}
 
// Ambil data dari form
$aksi    = $_POST['aksi'] ?? '';
$film_id = intval($_POST['film_id'] ?? 0);
$user_id = $_SESSION['user_id'];
 
if ($aksi == 'tambah') {
    $rating   = intval($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');
    
    // Validasi
    if ($rating < 1 || $rating > 5) {
        header("Location: detail.php?id=$film_id&pesan=Rating+tidak+valid&tipe=error");
        exit;
    }
    
    if (empty($komentar)) {
        header("Location: detail.php?id=$film_id&pesan=Komentar+tidak+boleh+kosong&tipe=error");
        exit;
    }
    
    // Simpan review ke database
    // ON DUPLICATE KEY UPDATE: jika sudah ada review, update saja
    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO reviews (user_id, film_id, rating, komentar) 
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "iiis", $user_id, $film_id, $rating, $komentar);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: detail.php?id=$film_id&pesan=Ulasan+berhasil+ditambahkan!&tipe=sukses");
    } else {
        header("Location: detail.php?id=$film_id&pesan=Gagal+menyimpan+ulasan&tipe=error");
    }
    exit;
}
 
// Kalau aksi tidak dikenal
header("Location: index.php");
exit;
?>
 
