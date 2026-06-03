<?php
// =============================================
// FILE: koneksi.php
// Fungsi: Menghubungkan PHP ke database MySQL
// =============================================

// Pengaturan koneksi database
$host     = "localhost";      // Lokasi server database (biasanya localhost)
$username = "root";           // Username MySQL (default XAMPP: root)
$password = "";               // Password MySQL (default XAMPP: kosong)
$database = "movie_review1";   // Nama database yang kita buat

// Membuat koneksi menggunakan mysqli
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    // Jika gagal, tampilkan pesan error dan hentikan script
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8 agar karakter Indonesia tampil dengan benar
mysqli_set_charset($koneksi, "utf8");

// Koneksi berhasil, file ini siap digunakan
// Cara pakai di file lain: include 'koneksi.php';
?>