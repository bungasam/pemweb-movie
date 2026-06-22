<?php
// =============================================
// FILE: admin/kelola_review.php
// Fungsi: Admin melihat, filter real-time, dan hapus review
// =============================================

session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua review (tanpa filter dulu, nanti difilter oleh JavaScript)
$semua_review = $pdo->query("
    SELECT r.*, u.username, u.foto AS foto_profil, f.judul AS judul_film, f.id AS film_id
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN films f ON r.film_id = f.id
    ORDER BY r.created_at DESC
");

// Ambil daftar film untuk dropdown (opsional, tapi bisa dipakai juga)
$daftar_film = $pdo->query("SELECT id, judul FROM films ORDER BY judul ASC");

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Review — CineView Admin</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Filter bar styling */
        .filter-bar {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #333;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 0.3rem;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.6rem 0.8rem;
            background: #242424;
            border: 1px solid #333;
            border-radius: 8px;
            color: #f0f0f0;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #BA3801;
        }
        
        .filter-group input::placeholder {
            color: #555;
        }
        
        .filter-info {
            margin-top: 0.8rem;
            font-size: 0.8rem;
            color: #888;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .badge-filter {
            background: rgba(186,56,1,0.2);
            color: #FFEC89;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-filter .remove-filter {
            margin-left: 0.3rem;
            cursor: pointer;
            font-weight: bold;
        }
        
        .badge-filter .remove-filter:hover {
            color: #BA3801;
        }
        
        .result-count {
            color: #FFEC89;
            font-weight: 600;
        }
        
        /* Sembunyikan baris yang tidak sesuai filter */
        .review-row.hidden {
            display: none;
        }
        
        /* Modal untuk lihat komentar lengkap */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.aktif {
            display: flex;
        }
        .modal-kotak {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        .modal-tutup {
            position: absolute;
            top: 0.8rem;
            right: 1rem;
            background: none;
            border: none;
            color: #aaa;
            font-size: 1.4rem;
            cursor: pointer;
        }
        .modal-tutup:hover { color: #fff; }
        
        /* Rating stars */
        .rating-stars {
            color: #FFEC89;
            letter-spacing: 2px;
        }

        .garis-dekorasi {
            width: 60px;
            height: 3px;
            background: #BA3801;
            margin-top: 0.5rem;
            border-radius: 3px;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-sukses {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="../index.php" class="navbar-logo">Cine<span>View</span></a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">← Dashboard</a></li>
            <li><a href="../index.php">Lihat Website</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_film.php">Kelola Film</a>
        <a href="kelola_review.php" class="active">Kelola Review</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="#" onclick="confirmLogout(event)">Logout</a>
    </aside>
    
    <main class="dashboard-konten">
        
        <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#FFEC89; margin-bottom:1rem;">
            Kelola Review
        </h1>
        <p style="color:#aaa; font-size:0.9rem;">Moderasi seluruh review pengguna di platform CineView</p>
        <div class="garis-dekorasi"></div>
        
        <?php if ($pesan): ?>
        <div class="alert <?= $tipe == 'sukses' ? 'alert-sukses' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <!-- ========== FILTER BAR REAL-TIME ========== -->
        <div class="filter-bar">
            <div class="filter-form">
                <div class="filter-group">
                    <label>🎬 Nama Film</label>
                    <input type="text" 
                           id="filterFilm" 
                           placeholder="Cari berdasarkan judul film..." 
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label>👤 User</label>
                    <input type="text" 
                           id="filterUser" 
                           placeholder="Cari berdasarkan nama user..." 
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label>⭐ Rating</label>
                    <select id="filterRating">
                        <option value="">Semua Rating</option>
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4)</option>
                        <option value="3">★★★☆☆ (3)</option>
                        <option value="2">★★☆☆☆ (2)</option>
                        <option value="1">★☆☆☆☆ (1)</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>📅 Tanggal</label>
                    <select id="filterTanggal">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">7 Hari Terakhir</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </div>
            </div>
            
            <!-- Informasi filter aktif -->
            <div id="filterInfo" class="filter-info" style="display:none;">
                <span>Filter aktif:</span>
                <div id="activeFilters" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
                <span id="resultCount" class="result-count"></span>
            </div>
        </div>
        
        <!-- ========== TABEL REVIEW ========== -->
        <div class="tabel-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Film</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php 
                    $no = 1;
                    foreach ($semua_review as $r): 
                        // Simpan data sebagai atribut data-* untuk filter JavaScript
                        $judul_lower = strtolower($r['judul_film']);
                        $user_lower = strtolower($r['username']);
                        $rating_val = $r['rating'];
                        $tgl = $r['created_at'];
                    ?>
                    <tr class="review-row" 
                        data-id="<?= $r['id'] ?>"
                        data-judul="<?= htmlspecialchars($judul_lower) ?>"
                        data-user="<?= htmlspecialchars($user_lower) ?>"
                        data-rating="<?= $rating_val ?>"
                        data-tanggal="<?= $tgl ?>">
                        
                        <td><?= $no++ ?></td>
                        <td style="color:#f0f0f0; font-weight:600;">
                            <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                                <?php
                                $nama_foto = basename((string) ($r['foto_profil'] ?? ''));
                                $lokasi_foto = __DIR__ . '/../img/' . $nama_foto;
                                ?>

                                <?php if ($nama_foto !== '' && is_file($lokasi_foto)): ?>
                                    <img
                                        src="../img/<?= htmlspecialchars($nama_foto, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($lokasi_foto) ?>"
                                        alt="Foto profil"
                                        class="avatar-kecil avatar-kecil-foto"
                                    >
                                <?php else: ?>
                                    <span class="avatar-kecil">
                                        <?= htmlspecialchars(strtoupper(substr($r['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>

                                <?= htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <a 
                                href="../detail.php?id=<?= $r['film_id'] ?>" 
                                style="color:#FFEC89; text-decoration:none;">
                                <?= htmlspecialchars($r['judul_film']) ?> ↗
                            </a>
                        </td>
                        <td>
                            <span class="rating-stars">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating']?'★':'☆'; ?>
                            </span>
                            <span style="color:#aaa; font-size:0.75rem;">(<?= $r['rating'] ?>)</span>
                        </td>
                        <td style="max-width:200px;">
                            <span style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;">
                                <?= htmlspecialchars(!empty($r['komentar']) ? $r['komentar'] : 'Tanpa komentar') ?>
                            </span>
                            <button type="button"
                                    class="btn btn-outline btn-kecil"
                                    style="margin-top:0.3rem; font-size:0.7rem;"
                                    onclick="lihatKomentar(
                                        <?= htmlspecialchars(json_encode($r['username']), ENT_QUOTES, 'UTF-8') ?>,
                                        <?= htmlspecialchars(json_encode($r['judul_film']), ENT_QUOTES, 'UTF-8') ?>,
                                        <?= (int) $r['rating'] ?>,
                                        <?= htmlspecialchars(json_encode(!empty($r['komentar']) ? $r['komentar'] : 'Tanpa komentar'), ENT_QUOTES, 'UTF-8') ?>
                                    )">
                                📖 Lihat Lengkap
                            </button>
                        </td>
                        <td style="font-size:0.8rem; white-space:nowrap;">
                            <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            <br><small style="color:#555;"><?= date('H:i', strtotime($r['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="hapus_review.php?id=<?= $r['id'] ?>&asal=kelola_review"
                               class="btn btn-hapus btn-kecil"
                               onclick="return konfirmasiHapus('Hapus review dari <?= addslashes($r['username']) ?>?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if ($semua_review->rowCount() == 0): ?>
                    <tr id="emptyRow">
                        <td colspan="7" style="text-align:center; padding:2rem; color:#aaa;">
                            Belum ada review.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal lihat komentar lengkap -->
<div class="modal-overlay" id="modal-komentar">
    <div class="modal-kotak">
        <button class="modal-tutup" onclick="tutupModal()">✕</button>
        <div style="margin-bottom:0.5rem; font-size:0.75rem; color:#BA3801; text-transform:uppercase; letter-spacing:1px;">Komentar Lengkap</div>
        <h3 id="modal-judul-film" style="font-family:'Playfair Display',serif; color:#FFEC89; margin-bottom:0.3rem;"></h3>
        <div style="font-size:0.85rem; color:#aaa; margin-bottom:0.5rem;">
            oleh <span id="modal-username" style="color:#f0f0f0; font-weight:600;"></span>
        </div>
        <div id="modal-rating" style="color:#FFEC89; font-size:1.1rem; margin-bottom:1rem;"></div>
        <div id="modal-komentar-isi" style="color:#ccc; line-height:1.8; white-space:pre-wrap;"></div>
    </div>
</div>

<script src="admin.js"></script>
</body>
</html>