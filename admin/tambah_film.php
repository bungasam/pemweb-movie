<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $tahun = (int)$_POST['tahun'];
    
    // Upload poster
    $poster = 'default.jpg';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['poster']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $poster = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], '../img/' . $poster);
        }
    }
    
    $query = mysqli_query($conn, "INSERT INTO film (judul, poster, deskripsi, genre, tahun) VALUES ('$judul', '$poster', '$deskripsi', '$genre', '$tahun')");
    
    if ($query) {
        header("Location: dashboard.php?success=1");
    } else {
        $error = "Gagal menambah film: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tambah Film - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Quicksand:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            color: #6f1d1b;
            margin-bottom: 20px;
        }
        .form-container input, .form-container textarea, .form-container select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 30px;
            border: 2px solid #95d5b2;
            font-family: 'Quicksand', sans-serif;
        }
        .form-container button {
            background: #6f1d1b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        .form-container button:hover {
            background: #40916c;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<header>
    <h1>🍵 MatchaFlix - Admin</h1>
    <p>Tambah Film Baru</p>
</header>

<nav>
    <a href="../index.php">🏠 Home</a>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="form-container">
    <h2>➕ Tambah Film</h2>
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="judul" placeholder="Judul Film" required>
        <input type="text" name="genre" placeholder="Genre (contoh: Action, Drama)">
        <input type="number" name="tahun" placeholder="Tahun Rilis">
        <textarea name="deskripsi" placeholder="Deskripsi film..." rows="5" required></textarea>
        <input type="file" name="poster" accept="image/*">
        <small style="color:#666;">Biarkan kosong untuk menggunakan poster default</small>
        <button type="submit">💾 Simpan Film</button>
    </form>
    <a href="dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#40916c;">← Kembali ke Dashboard</a>
</div>

<footer>
    <p>🍵 MatchaFlix — Kelola film dengan mudah 🍃</p>
</footer>

</body>
</html>