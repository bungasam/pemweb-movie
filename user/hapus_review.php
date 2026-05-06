<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM review WHERE id='$id' AND user_id='{$_SESSION['user_id']}'");
header("Location: dashboard.php");
?>