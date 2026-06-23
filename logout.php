<?php
session_start();
// Kosongkan semua data yang tersimpan di session.
$_SESSION = [];
session_destroy();
header('Location: login.php?pesan=Anda+berhasil+logout.&tipe=sukses');
exit;
?>
