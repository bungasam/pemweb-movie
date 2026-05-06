<?php
$conn = mysqli_connect("localhost", "root", "", "movie_review");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Mulai session untuk login
session_start();
?>