<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id = (int)$_GET['id'];
$review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM review WHERE id='$id' AND user_id='{$_SESSION['user_id']}'"));

if (!$review) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_text = mysqli_real_escape_string($conn, $_POST['review']);
    $rating = (int)$_POST['rating'];
    
    $query = mysqli_query($conn, "UPDATE review SET review='$review_text', rating='$rating' WHERE id='$id'");
    
    if ($query) {
        header("Location: dashboard.php?success=1");
    } else {
        $error = "Gagal update review: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Review - CineView</title>
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
        .form-container textarea, .form-container select {
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
    </style>
</head>
<body>

<header>
    <h1>🍵 CineView</h1>
    <p>Edit Review Kamu</p>
</header>

<nav>
    <a href="../index.php">🏠 Home</a>
    <a href="dashboard.php">📝 Reviewku</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="form-container">
    <h2 style="color:#6f1d1b;">✏️ Edit Review</h2>
    <form method="POST">
        <textarea name="review" rows="5" required><?= htmlspecialchars($review['review']) ?></textarea>
        <select name="rating" required>
            <option value="5" <?= $review['rating'] == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5/5</option>
            <option value="4" <?= $review['rating'] == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ 4/5</option>
            <option value="3" <?= $review['rating'] == 3 ? 'selected' : '' ?>>⭐⭐⭐ 3/5</option>
            <option value="2" <?= $review['rating'] == 2 ? 'selected' : '' ?>>⭐⭐ 2/5</option>
            <option value="1" <?= $review['rating'] == 1 ? 'selected' : '' ?>>⭐ 1/5</option>
        </select>
        <button type="submit">💾 Update Review</button>
    </form>
    <a href="dashboard.php" style="display:block; text-align:center; margin-top:20px;">← Kembali</a>
</div>

<footer>
    <p>🍵 CineView — Terima kasih sudah berkontribusi 🍃</p>
</footer>

</body>
</html>