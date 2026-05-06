<?php
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$film_id = $_POST['film_id'];
$nama_reviewer = htmlspecialchars($_POST['nama_reviewer']);
$review = htmlspecialchars($_POST['review']);
$rating = (int)$_POST['rating'];
$user_id = $_SESSION['user_id'];

if (!$film_id || !$nama_reviewer || !$review || !$rating) {
    die("Data tidak lengkap!");
}

// Cek apakah user sudah pernah review film ini
$check = mysqli_query($conn, "SELECT id FROM review WHERE film_id='$film_id' AND user_id='$user_id'");
if (mysqli_num_rows($check) > 0) {
    die("Anda sudah pernah mereview film ini!");
}

$query = mysqli_query($conn, 
    "INSERT INTO review (film_id, user_id, nama_reviewer, review, rating) 
     VALUES ('$film_id', '$user_id', '$nama_reviewer', '$review', '$rating')"
);

if (!$query) {
    die("Gagal simpan review: " . mysqli_error($conn));
}

header("Location: index.php?success=1");
?>