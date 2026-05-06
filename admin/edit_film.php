<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = (int)$_GET['id'];
$film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM film WHERE id='$id'"));

if (!$film) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $tahun = (int)$_POST['tahun'];
    
    $poster = $film['poster'];
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['poster']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Hapus poster lama jika bukan default
            if ($poster != 'default.jpg' && file_exists('../img/' . $poster)) {
                unlink('../img/' . $poster);
            }
            $poster = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], '../img/' . $poster);
        }
    }
    
    $query = mysqli_query($conn, "UPDATE film SET judul='$judul', poster='$poster', deskripsi='$deskripsi', genre='$genre', tahun='$tahun' WHERE id='$id'");
    
    if ($query) {
        header("Location: dashboard.php?success=1");
    } else {
        $error = "Gagal update film: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Film - Admin</title>
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
        .current-poster {
            text-align: center;
            margin: 15px 0;
        }
        .current-poster img {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<header>
    <h1>🍵 MatchaFlix - Admin</h1>
    <p>Edit Film</p>
</header>

<nav>
    <a href="../index.php">🏠 Home</a>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="form-container">
    <h2>✏️ Edit Film</h2>
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="current-poster">
        <img src="../img/<?= $film['poster'] ?>" width="150" height="200" style="object-fit:cover;" onerror="this.src='../img/default.jpg'">
        <p>Poster saat ini</p>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="judul" value="<?= htmlspecialchars($film['judul']) ?>" required>
        <input type="text" name="genre" value="<?= htmlspecialchars($film['genre'] ?? '') ?>" placeholder="Genre">
        <input type="number" name="tahun" value="<?= $film['tahun'] ?? '' ?>" placeholder="Tahun Rilis">
        <textarea name="deskripsi" rows="5" required><?= htmlspecialchars($film['deskripsi']) ?></textarea>
        <input type="file" name="poster" accept="image/*">
        <small style="color:#666;">Upload poster baru jika ingin mengganti</small>
        <button type="submit">💾 Update Film</button>
    </form>
    <a href="dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#40916c;">← Kembali ke Dashboard</a>
</div>

<footer>
    <p>🍵 MatchaFlix — Kelola film dengan mudah 🍃</p>
</footer>

</body>
</html>