<?php
// Pengaturan koneksi database
$host     = "localhost";
$dbname   = "movie_review1";
$username = "root";
$password = "";

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set mode error PDO menjadi exception (biar mudah menangkap error)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode menjadi associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "Koneksi berhasil";
    
} catch(PDOException $e) {
    // Jika gagal, tampilkan pesan error dan hentikan script
    die("Koneksi database gagal: " . $e->getMessage());
}
?>