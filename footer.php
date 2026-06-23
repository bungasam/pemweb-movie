<?php
$footer_base = $footer_base ?? '';
?>
<footer>
    <div class="footer-inner">
        <div class="footer-atas">
            <div class="footer-brand">
                <h3>Cine<span style="color:#BA3801">View</span></h3>
                <p>Temukan film favoritmu di sini. Dapatkan ulasan jujur, rating dari penonton, dan rekomendasi film yang sesuai dengan seleramu.</p>
            </div>

            <div class="footer-kolom">
                <h4>Navigasi</h4>
                <a href="<?= htmlspecialchars($footer_base) ?>index.php">Beranda</a>
                <a href="<?= htmlspecialchars($footer_base) ?>rekomendasi.php">Rekomendasi</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="<?= htmlspecialchars($footer_base) ?>admin/dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($footer_base) ?>user/dashboard.php">Profil</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($footer_base) ?>login.php">Login</a>
                    <a href="<?= htmlspecialchars($footer_base) ?>register.php">Daftar</a>
                <?php endif; ?>
            </div>

            <div class="footer-kolom">
                <h4>Kontak</h4>
                <a href="mailto:cineview@gmail.com">✉️ cineview@gmail.com</a>
                <a href="tel:+6281234567890">📞 +62 812-3456-7890</a>
            </div>
        </div>

        <div class="footer-bawah">
            &copy; <?= date('Y') ?> CineView &mdash; Platform Rating Film Terpercaya
        </div>
    </div>
</footer>
