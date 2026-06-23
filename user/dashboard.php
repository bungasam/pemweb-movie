<?php
session_start();
require_once '../koneksi.php';

// User harus login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Admin diarahkan ke dashboard admin
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ambil data user dari database
$stmt_user = $pdo->prepare(
    'SELECT * FROM users WHERE id = ? LIMIT 1'
);

$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
// ambil semua review user dari database
$stmt_review = $pdo->prepare(
    'SELECT
        r.id,
        r.user_id,
        r.film_id,
        r.rating,
        r.komentar,
        r.created_at,
        f.judul AS judul_film
     FROM reviews r
     JOIN films f ON r.film_id = f.id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC'
);

$stmt_review->execute([$user_id]);

// Ambil semua review menjadi array
$review_user = $stmt_review->fetchAll(PDO::FETCH_ASSOC);

// Hitung jumlah review
$jml_review = count($review_user);

// Pesan notifikasi
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil Saya — CineView</title>

    <link rel="stylesheet" href="../style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<nav class="navbar">
    <div class="navbar-inner">

        <a href="../index.php" class="navbar-logo">
            Cine<span>View</span>
        </a>

        <ul class="navbar-menu">
            <li>
                <a href="../index.php">Beranda</a>
            </li>

            <li>
                <a href="../rekomendasi.php">Rekomendasi</a>
            </li>

            <li>
                <a href="dashboard.php">Profil</a>
            </li>

            <li>
                <a
                    href="../logout.php"
                    onclick="return confirmLogout(event, this.href)"
                >
                    Logout
                </a>
            </li>
        </ul>

    </div>
</nav>

<div class="profil-wrapper">

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $tipe === 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- PROFIL USER -->
    <div class="profil-header">

        <div
            class="profil-avatar"
            style="overflow:hidden; padding:0;"
        >

            <?php if (!empty($user['foto'])): ?>

                <img
                    src="../img/<?= htmlspecialchars(
                        $user['foto'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>?v=<?= !empty($user['foto']) && file_exists('../img/' . $user['foto']) ? filemtime('../img/' . $user['foto']) : time() ?>"
                    alt="Foto Profil"
                    style="
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        border-radius:50%;
                    "
                    onerror="
                        this.style.display='none';
                        document.getElementById('inisial-avatar').style.display='flex';
                    "
                >

                <div
                    id="inisial-avatar"
                    style="
                        display:none;
                        width:100%;
                        height:100%;
                        align-items:center;
                        justify-content:center;
                        font-size:2rem;
                        font-weight:700;
                    "
                >
                    <?= htmlspecialchars(
                        strtoupper(substr($user['username'], 0, 1)),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php else: ?>

                <div
                    style="
                        display:flex;
                        width:100%;
                        height:100%;
                        align-items:center;
                        justify-content:center;
                        font-size:2rem;
                        font-weight:700;
                    "
                >
                    <?= htmlspecialchars(
                        strtoupper(substr($user['username'], 0, 1)),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>

        </div>

        <div>

            <div class="profil-nama">
                <?= htmlspecialchars(
                    $user['username'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="profil-email">
                📧
                <?= htmlspecialchars(
                    $user['email'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="profil-badge">
                User
            </div>

            <div
                style="
                    font-size:0.82rem;
                    color:#aaa;
                    margin-top:0.5rem;
                "
            >
                📅 Bergabung
                <?= date(
                    'd F Y',
                    strtotime($user['created_at'])
                ) ?>
            </div>

            <div style="margin-top:0.8rem;">
                <a
                    href="edit_profil.php"
                    class="btn btn-biru btn-kecil"
                >
                    ✏️ Edit Profil
                </a>
            </div>

        </div>

    </div>

    <!-- JUMLAH REVIEW -->
    <div style="margin-bottom:2rem;">

        <div
            style="
                background:var(--card);
                border:0.5px solid var(--card-border);
                border-radius:5px;
                padding:0.3rem;
                text-align:center;
            "
        >

            <div
                style="
                    font-family:'Playfair Display',serif;
                    font-size:5rem;
                    font-weight:950;
                    color:#FFEC89;
                "
            >
                <?= $jml_review ?>
            </div>

            <div
                style="
                    font-size:0.85rem;
                    color:#aaa;
                    text-transform:uppercase;
                    letter-spacing:1px;
                    margin-top:5px;
                "
            >
                Review Ditulis
            </div>

        </div>

    </div>

    <h2
        style="
            font-family:'Playfair Display',serif;
            font-size:1.5rem;
            margin-bottom:1rem;
        "
    >
        Riwayat
        <span style="color:#FFEC89;">
            Ulasan
        </span>
    </h2>

    <div class="garis-dekorasi"></div>

    <?php if ($jml_review > 0): ?>

        <div class="review-list">

            <?php foreach ($review_user as $r): ?>

                <div class="review-card">

                    <div class="review-header">

                        <div>

                            <a
                                href="../detail.php?id=<?= (int) $r['film_id'] ?>"
                                style="
                                    color:#FFEC89;
                                    font-weight:700;
                                    text-decoration:none;
                                    font-size:1rem;
                                "
                            >
                                🎬
                                <?= htmlspecialchars(
                                    $r['judul_film'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>

                            <div
                                class="rating-stars"
                                style="
                                    margin-top:0.3rem;
                                    font-size:1rem;
                                "
                            >

                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= (int) $r['rating'] ? '★' : '☆' ?>
                                <?php endfor; ?>

                                <span class="rating-angka">
                                    (<?= (int) $r['rating'] ?>/5)
                                </span>

                            </div>

                        </div>

                        <div class="review-tanggal">
                            <?= date(
                                'd M Y',
                                strtotime($r['created_at'])
                            ) ?>
                        </div>

                    </div>

                    <?php if (!empty($r['komentar'])): ?>

                        <p class="review-komentar">
                            <?= nl2br(
                                htmlspecialchars(
                                    $r['komentar'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>
                        </p>

                    <?php else: ?>

                        <p class="review-komentar review-tanpa-komentar">
                            Memberikan rating tanpa komentar.
                        </p>

                    <?php endif; ?>

                    <div
                        class="review-actions"
                        style="margin-top:0.8rem;"
                    >

                        <!-- Dari dashboard, kembali ke dashboard -->
                        <a
                            href="edit_review.php?id=<?= (int) $r['id'] ?>&asal=dashboard"
                            class="btn btn-biru btn-kecil"
                        >
                            Edit
                        </a>

                        <a
                            href="hapus_review.php?id=<?= (int) $r['id'] ?>&asal=dashboard"
                            class="btn btn-hapus btn-kecil"
                            onclick="return konfirmasiHapus('Hapus ulasan ini?')"
                        >
                            Hapus
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <div class="ikon">
                💬
            </div>

            <p>
                Kamu belum menulis ulasan apa pun.
            </p>

            <a
                href="../rekomendasi.php"
                class="btn btn-merah"
                style="margin-top:1rem;"
            >
                Cari Film
            </a>

        </div>

    <?php endif; ?>

    <div
        style="
            margin-top:3rem;
            padding-top:1.5rem;
            border-top:1px solid var(--card-border);
        "
    >

        <a
            href="../logout.php"
            onclick="return confirmLogout(event, this.href)"
            class="btn btn-outline"
        >
            🚪 Logout
        </a>

    </div>

</div>


<script src="../script.js"></script>

</body>
</html>
