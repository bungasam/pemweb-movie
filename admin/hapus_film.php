<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = (int)$_GET['id'];

// Ambil nama poster dulu
$film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT poster FROM film WHERE id='$id'"));
if ($film && $film['poster'] != 'default.jpg' && file_exists('../img/' . $film['poster'])) {
    unlink('../img/' . $film['poster']);
}

mysqli_query($conn, "DELETE FROM film WHERE id='$id'");
header("Location: dashboard.php");
?>