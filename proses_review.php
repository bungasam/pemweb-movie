<?php
// =============================================
// FILE: proses_review.php
// Fungsi: Menyimpan rating dan ulasan baru
// Database: PDO
// =============================================

session_start();
require_once 'koneksi.php';

// User harus login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Admin tidak boleh memberikan review.
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: index.php');
    exit;
}

// File ini hanya menerima data POST dari form review.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$film_id = (int) ($_POST['film_id'] ?? 0);
$user_id = (int) $_SESSION['user_id'];
$rating = (int) ($_POST['rating'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');

if ($aksi !== 'tambah' || $film_id <= 0) {
    header('Location: index.php');
    exit;
}

// Rating wajib. Komentar boleh kosong.
if ($rating < 1 || $rating > 5) {
    header(
        'Location: detail.php?id=' . $film_id .
        '&pesan=' . urlencode('Pilih rating bintang dari 1 sampai 5!') .
        '&tipe=error'
    );
    exit;
}

if (strlen($komentar) > 1000) {
    header(
        'Location: detail.php?id=' . $film_id .
        '&pesan=' . urlencode('Ulasan maksimal 1000 karakter!') .
        '&tipe=error'
    );
    exit;
}

// Pastikan film masih tersedia.
$stmt_film = $pdo->prepare('SELECT id FROM films WHERE id = ? LIMIT 1');
$stmt_film->execute([$film_id]);

if (!$stmt_film->fetchColumn()) {
    header('Location: index.php');
    exit;
}

// Satu user hanya boleh memberi satu review untuk satu film.
$stmt_cek = $pdo->prepare(
    'SELECT id
     FROM reviews
     WHERE user_id = ? AND film_id = ?
     LIMIT 1'
);
$stmt_cek->execute([$user_id, $film_id]);

if ($stmt_cek->fetchColumn()) {
    header(
        'Location: detail.php?id=' . $film_id .
        '&pesan=' . urlencode('Kamu sudah memberikan rating untuk film ini!') .
        '&tipe=error'
    );
    exit;
}

try {
    // Komentar kosong disimpan sebagai string kosong agar tetap cocok
    // jika kolom komentar pada database menggunakan NOT NULL.
    $stmt_simpan = $pdo->prepare(
        'INSERT INTO reviews (user_id, film_id, rating, komentar)
         VALUES (?, ?, ?, ?)'
    );
    $stmt_simpan->execute([
        $user_id,
        $film_id,
        $rating,
        $komentar
    ]);

    header(
        'Location: detail.php?id=' . $film_id .
        '&pesan=' . urlencode('Rating berhasil ditambahkan!') .
        '&tipe=sukses'
    );
    exit;
} catch (PDOException $e) {
    error_log('Gagal menyimpan review: ' . $e->getMessage());

    header(
        'Location: detail.php?id=' . $film_id .
        '&pesan=' . urlencode('Gagal menyimpan rating!') .
        '&tipe=error'
    );
    exit;
}
