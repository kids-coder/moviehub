<?php
// BDMovieHub - Footer partial
// Included by frontend pages.

if (!defined('BDMOVIEHUB')) { exit('Direct access denied'); }

$settings = getSettings();
$pages    = getPublishedPages();
$year     = date('Y');

// Social links (from settings)
$socials = array(
    'facebook'  => isset($settings['social_facebook']) ? $settings['social_facebook'] : '',
    'twitter'   => isset($settings['social_twitter']) ? $settings['social_twitter'] : '',
    'instagram' => isset($settings['social_instagram']) ? $settings['social_instagram'] : '',
    'youtube'   => isset($settings['social_youtube']) ? $settings['social_youtube'] : '',
    'telegram'  => isset($settings['social_telegram']) ? $settings['social_telegram'] : '',
    'discord'   => isset($settings['social_discord']) ? $settings['social_discord'] : '',
);
$hasSocials = false;
foreach ($socials as $s) { if (!empty($s)) { $hasSocials = true; break; } }

// Contact info
$contactEmail = isset($settings['contact_email']) ? $settings['contact_email'] : '';
$footerText   = isset($settings['footer_text']) ? $settings['footer_text'] : '';
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>BD<span>Movie</span>Hub</h3>
                <p><?php e(isset($settings['description']) ? $settings['description'] : SITE_DESC); ?></p>
                <p style="margin-top:10px; font-size:12px;">
                    <?php if (!empty($footerText)): ?>
                        <?php e($footerText); ?>
                    <?php else: ?>
                        Streaming movies and anime for free, anytime, anywhere.
                    <?php endif; ?>
                </p>
                <?php if ($hasSocials): ?>
                <div class="footer-social" style="display:flex; gap:10px; margin-top:14px;">
                    <?php if (!empty($socials['facebook'])): ?>
                        <a href="<?php e($socials['facebook']); ?>" target="_blank" rel="noopener" aria-label="Facebook" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['twitter'])): ?>
                        <a href="<?php e($socials['twitter']); ?>" target="_blank" rel="noopener" aria-label="Twitter" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['instagram'])): ?>
                        <a href="<?php e($socials['instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['youtube'])): ?>
                        <a href="<?php e($socials['youtube']); ?>" target="_blank" rel="noopener" aria-label="YouTube" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['telegram'])): ?>
                        <a href="<?php e($socials['telegram']); ?>" target="_blank" rel="noopener" aria-label="Telegram" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-telegram"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['discord'])): ?>
                        <a href="<?php e($socials['discord']); ?>" target="_blank" rel="noopener" aria-label="Discord" style="width:36px; height:36px; border-radius:50%; background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text);"><i class="fab fa-discord"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="footer-col">
                <h4>Browse</h4>
                <ul>
                    <li><a href="<?php e(BASE_URL); ?>/index.php">Home</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/search.php">Movies</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/series.php">Series</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/anime.php">Anime</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/trending.php">Trending</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/top-rated.php">Top Rated</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/genres.php">Genres</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/anime-schedule.php">Schedule</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/favorites.php">My Favorites</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/request.php">Request a Title</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul>
                    <?php if (!empty($pages)): ?>
                        <?php foreach ($pages as $p): ?>
                            <li><a href="<?php e(BASE_URL); ?>/page.php?slug=<?php echo urlencode(isset($p['slug']) ? $p['slug'] : ''); ?>"><?php e(isset($p['title']) ? $p['title'] : 'Page'); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><a href="<?php e(BASE_URL); ?>/contact.php">Contact</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/dmca.php">DMCA</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/privacy.php">Privacy Policy</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/terms.php">Terms of Service</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/disclaimer.php">Disclaimer</a></li>
                    <li><a href="<?php e(BASE_URL); ?>/admin/login.php">Admin Login</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo $year; ?> <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?>. All rights reserved.
            <?php if (!empty($contactEmail)): ?>
                | <a href="mailto:<?php e($contactEmail); ?>" style="color:var(--muted);"><?php e($contactEmail); ?></a>
            <?php endif; ?>
        </div>
    </div>
</footer>

<script src="<?php e(ASSETS_URL); ?>/js/ui.js"></script>
<script src="<?php e(ASSETS_URL); ?>/js/features.js"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('<?php e(BASE_URL); ?>/sw.js').catch(function () {});
    });
}
</script>
<?php if (isset($loadPlayerJs) && $loadPlayerJs): ?>
<script src="<?php e(ASSETS_URL); ?>/js/player.js"></script>
<?php endif; ?>
<?php
// Optional custom JS from admin settings
if (!empty($settings['custom_js'])) {
    echo '<script>' . $settings['custom_js'] . '</script>' . "\n";
}
?>
</body>
</html>
<?php
// Flush output buffer (started in header.php)
if (ob_get_level() > 0) { ob_end_flush(); }
