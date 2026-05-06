<?php
require 'koneksi.php';

$film_id = $_POST['film_id'];
$nama_reviewer = htmlspecialchars($_POST['nama_reviewer']);
$review = htmlspecialchars($_POST['review']);
$rating = (int)$_POST['rating'];

if (!$film_id || !$nama_reviewer || !$review || !$rating) {
    die("Data tidak lengkap!");
}

$query = mysqli_query($conn, 
    "INSERT INTO review (film_id, nama_reviewer, review, rating) 
     VALUES ('$film_id', '$nama_reviewer', '$review', '$rating')"
);

if (!$query) {
    die("Gagal simpan review: " . mysqli_error($conn));
}

header("Location: index.php?success=1");
?>