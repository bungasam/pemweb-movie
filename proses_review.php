<?php
// =============================================
// FILE: proses_review.php
// Fungsi: Memproses tambah review dari user
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
    
    // Simpan review ke database menggunakan PDO
    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, film_id, rating, komentar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $film_id, $rating, $komentar]);
        
        header("Location: detail.php?id=$film_id&pesan=Ulasan+berhasil+ditambahkan!&tipe=sukses");
    } catch (PDOException $e) {
        // Jika terjadi error (misal duplicate entry)
        header("Location: detail.php?id=$film_id&pesan=Gagal+menyimpan+ulasan&tipe=error");
    }
    exit;
}
 
// Kalau aksi tidak dikenal
header("Location: index.php");
exit;
?>