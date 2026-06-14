<?php
// =============================================
// FILE: detail.php
// Fungsi: Menampilkan detail film dan daftar review
// Database: PDO
// =============================================

session_start();
require_once 'koneksi.php';

// Ambil ID film dari URL.
$film_id = (int) ($_GET['id'] ?? 0);

if ($film_id <= 0) {
    header('Location: index.php');
    exit;
}

// =============================================
// AMBIL DATA FILM
// =============================================
$stmt_film = $pdo->prepare('SELECT * FROM films WHERE id = ? LIMIT 1');
$stmt_film->execute([$film_id]);
$film = $stmt_film->fetch(PDO::FETCH_ASSOC);

if (!$film) {
    header('Location: index.php');
    exit;
}

// =============================================
// HITUNG RATING FILM
// =============================================
$stmt_rating = $pdo->prepare(
    'SELECT ROUND(AVG(rating), 1) AS rata, COUNT(*) AS total
     FROM reviews
     WHERE film_id = ?'
);
$stmt_rating->execute([$film_id]);
$data_rating = $stmt_rating->fetch(PDO::FETCH_ASSOC);

$rata_rating = $data_rating['rata'] ?? null;
$total_review = (int) ($data_rating['total'] ?? 0);

// =============================================
// CEK REVIEW USER YANG SEDANG LOGIN
// =============================================
$sudah_review = false;
$review_user_id = 0;

if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['role'] ?? '') === 'user'
) {
    $stmt_cek = $pdo->prepare(
        'SELECT id
         FROM reviews
         WHERE user_id = ? AND film_id = ?
         LIMIT 1'
    );
    $stmt_cek->execute([
        (int) $_SESSION['user_id'],
        $film_id
    ]);

    $review_user = $stmt_cek->fetch(PDO::FETCH_ASSOC);

    if ($review_user) {
        $sudah_review = true;
        $review_user_id = (int) $review_user['id'];
    }
}

// =============================================
// AMBIL SEMUA REVIEW
// Foto selalu diambil dari tabel users agar foto terbaru
// langsung terlihat pada komentar lama maupun komentar baru.
// =============================================
$stmt_review = $pdo->prepare(
    'SELECT
        r.id,
        r.user_id,
        r.film_id,
        r.rating,
        r.komentar,
        r.created_at,
        u.username,
        u.foto AS foto_profil
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     WHERE r.film_id = ?
     ORDER BY r.created_at DESC'
);
$stmt_review->execute([$film_id]);
$semua_review = $stmt_review->fetchAll(PDO::FETCH_ASSOC);

// Pesan notifikasi setelah tambah, edit, atau hapus review.
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($film['judul'], ENT_QUOTES, 'UTF-8') ?> — CineView</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">Cine<span>View</span></a>

        <ul class="navbar-menu">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="rekomendasi.php">Rekomendasi</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="user/dashboard.php">Profil</a></li>
                <?php endif; ?>

                <li>
                    <a href="logout.php" onclick="return confirmLogout(event, this.href)">
                        Logout
                    </a>
                </li>
            <?php else: ?>
                <li><a href="register.php">Daftar</a></li>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="detail-film-wrapper">

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $tipe === 'sukses' ? 'alert-sukses' : 'alert-error' ?>">
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="detail-film-atas">
        <div class="detail-poster">
            <img
                src="img/<?= htmlspecialchars(!empty($film['poster']) ? $film['poster'] : 'default.svg', ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($film['judul'], ENT_QUOTES, 'UTF-8') ?>"
                onerror="this.src='img/default.svg'"
            >
        </div>

        <div class="detail-info">
            <h1><?= htmlspecialchars($film['judul'], ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="detail-meta">
                <div class="meta-tag">🎬 <span><?= htmlspecialchars($film['genre'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div class="meta-tag">📅 <span><?= (int) $film['tahun'] ?></span></div>
                <div class="meta-tag">🎥 <span><?= htmlspecialchars($film['sutradara'], ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>

            <div class="detail-rating-besar">
                <div class="angka-rating">
                    <?= $rata_rating !== null ? htmlspecialchars((string) $rata_rating, ENT_QUOTES, 'UTF-8') : '—' ?>
                </div>

                <div>
                    <div class="rating-stars" style="font-size:1.3rem;">
                        <?php
                        $nilai_rata = (float) ($rata_rating ?? 0);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= round($nilai_rata) ? '★' : '☆';
                        }
                        ?>
                    </div>

                    <div style="font-size:0.82rem; color:#aaa; margin-top:0.2rem;">
                        Dari <?= $total_review ?> ulasan
                    </div>
                </div>
            </div>

            <p class="detail-sinopsis">
                <?= nl2br(htmlspecialchars($film['sinopsis'], ENT_QUOTES, 'UTF-8')) ?>
            </p>
        </div>
    </div>

    <!-- FORM TAMBAH REVIEW -->
    <div style="margin-bottom:3rem;">
        <?php if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin'): ?>
            <h2 class="section-title" style="margin-bottom:1rem;">
                Berikan <span>Rating</span>
            </h2>
            <div class="garis-dekorasi"></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="alert alert-info">
                <a href="login.php" style="color:#6b8fd4;">Login</a>
                terlebih dahulu untuk memberikan rating.
            </div>

        <?php elseif (($_SESSION['role'] ?? '') === 'admin'): ?>
            <!-- Admin hanya memoderasi review, tidak mengirim review. -->

        <?php elseif ($sudah_review): ?>
            <div class="alert alert-info">
                Kamu sudah memberikan rating untuk film ini.
                <a
                    href="user/edit_review.php?id=<?= $review_user_id ?>&asal=detail"
                    style="color:#6b8fd4;"
                >
                    Edit rating atau ulasanmu
                </a>.
            </div>

        <?php else: ?>
            <div class="form-review-card">
                <form method="POST" action="proses_review.php" onsubmit="return validasiReview()">
                    <input type="hidden" name="film_id" value="<?= $film_id ?>">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="form-group">
                        <label>Rating Bintang <span style="color:#BA3801;">*</span></label>

                        <div class="rating-input">
                            <input type="radio" name="rating" id="bintang5" value="5">
                            <label for="bintang5" title="5 Bintang">★</label>

                            <input type="radio" name="rating" id="bintang4" value="4">
                            <label for="bintang4" title="4 Bintang">★</label>

                            <input type="radio" name="rating" id="bintang3" value="3">
                            <label for="bintang3" title="3 Bintang">★</label>

                            <input type="radio" name="rating" id="bintang2" value="2">
                            <label for="bintang2" title="2 Bintang">★</label>

                            <input type="radio" name="rating" id="bintang1" value="1">
                            <label for="bintang1" title="1 Bintang">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="komentar">Ulasan <span style="color:#777;"></label>

                        <textarea
                            id="komentar"
                            name="komentar"
                            maxlength="1000"
                            placeholder="Tulis ulasanmu tentang film ini ..."
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-merah">
                        Kirim Rating
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- DAFTAR REVIEW -->
    <div>
        <h2 class="section-title" style="margin-bottom:1rem;">
            Semua <span>Ulasan</span>
            <small style="font-size:1rem; color:#aaa; font-family:'DM Sans',sans-serif;">
                (<?= $total_review ?>)
            </small>
        </h2>
        <div class="garis-dekorasi"></div>

        <?php if (count($semua_review) > 0): ?>
            <div class="review-list">
                <?php foreach ($semua_review as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-user">
                                <?php
                                $nama_foto = basename((string) ($review['foto_profil'] ?? ''));
                                $lokasi_foto = __DIR__ . '/img/' . $nama_foto;
                                $foto_tersedia = $nama_foto !== '' && is_file($lokasi_foto);
                                ?>

                                <?php if ($foto_tersedia): ?>
                                    <img
                                        src="img/<?= htmlspecialchars($nama_foto, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($lokasi_foto) ?>"
                                        alt="Foto <?= htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8') ?>"
                                        class="avatar-kecil avatar-kecil-foto"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="avatar-kecil avatar-kecil-fallback" style="display:none;">
                                        <?= htmlspecialchars(strtoupper(substr($review['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php else: ?>
                                    <div class="avatar-kecil avatar-kecil-fallback">
                                        <?= htmlspecialchars(strtoupper(substr($review['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>

                                <span><?= htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <div class="review-tanggal">
                                <?= date('d M Y', strtotime($review['created_at'])) ?>
                            </div>
                        </div>

                        <div class="rating-stars" style="margin-bottom:0.5rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= (int) $review['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>

                        <?php if (!empty($review['komentar'])): ?>
                            <p class="review-komentar">
                                <?= nl2br(htmlspecialchars($review['komentar'], ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                        <?php else: ?>
                            <p class="review-komentar review-tanpa-komentar">
                                Memberikan rating tanpa komentar.
                            </p>
                        <?php endif; ?>

                        <?php
                        $boleh_edit =
                            isset($_SESSION['user_id']) &&
                            (int) $_SESSION['user_id'] === (int) $review['user_id'] &&
                            ($_SESSION['role'] ?? '') === 'user';

                        $admin_login =
                            isset($_SESSION['user_id']) &&
                            ($_SESSION['role'] ?? '') === 'admin';
                        ?>

                        <?php if ($boleh_edit || $admin_login): ?>
                            <div class="review-actions">
                                <?php if ($boleh_edit): ?>
                                    <a
                                        href="user/edit_review.php?id=<?= (int) $review['id'] ?>&asal=detail"
                                        class="btn btn-biru btn-kecil"
                                    >
                                        Edit
                                    </a>
                                <?php endif; ?>

                                <?php if ($boleh_edit): ?>
                                    <a
                                        href="user/hapus_review.php?id=<?= (int) $review['id'] ?>&asal=detail"
                                        class="btn btn-hapus btn-kecil"
                                        onclick="return konfirmasiHapus('Hapus ulasan ini?')"
                                    >
                                        Hapus
                                    </a>
                                <?php elseif ($admin_login): ?>
                                    <a
                                        href="admin/hapus_review.php?id=<?= (int) $review['id'] ?>&asal=detail"
                                        class="btn btn-hapus btn-kecil"
                                        onclick="return konfirmasiHapus('Hapus ulasan pengguna ini?')"
                                    >
                                        Hapus
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="ikon">💬</div>
                <p>Belum ada rating untuk film ini. Jadilah yang pertama!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Footer hanya dipakai pada halaman utama yang berisi konten publik.
$footer_base = '';
include 'footer.php';
?>

<script src="script.js"></script>
</body>
</html>
