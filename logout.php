<?php
// =============================================
// FILE: logout.php
// Fungsi: Menghancurkan session dan logout user
// =============================================

session_start(); // Mulai session yang ada

// Hapus semua data session
session_destroy(); // Hancurkan session sepenuhnya

// Redirect ke halaman login
header("Location: login.php");
exit;
?>