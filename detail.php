<?php
require_once 'koneksi.php';

$id = $_GET['id'];

$query = mysqli_query($conn, "SELECT * FROM film WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

$review = mysqli_query($conn, "SELECT * FROM review WHERE film_id='$id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $data['judul']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1><?= $data['judul']; ?></h1>
</header>

<div class="detail-container">

    <img src="img/<?= $data['poster']; ?>" class="detail-poster">

    <div class="detail-text">
        <h2>Sinopsis</h2>
        <p><?= $data['deskripsi']; ?></p>

        <h2>Review User</h2>

        <?php while($r = mysqli_fetch_assoc($review)){ ?>
            <div class="review-box">
                <h4><?= $r['nama_reviewer']; ?></h4>
                <p><?= $r['review']; ?></p>
                <small>Rating: <?= $r['rating']; ?>/5</small>
            </div>
        <?php } ?>

    </div>

</div>

</body>
</html>