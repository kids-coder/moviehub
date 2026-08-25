<?php
// BDMovieHub - About page
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle = 'About Us';
$ogTitle = SITE_NAME . ' - About Us';
$ogDescription = 'Learn about BDMovieHub, a catalog for discovering Bangla movies, series and anime.';

include __DIR__ . '/header.php';
?>
<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 820px;">
        <h1 class="section-title" style="margin-bottom:20px;">About BDMovieHub</h1>
        <div class="page-content">
            <p>BDMovieHub helps viewers discover Bangla movies, series, anime, trailers and related information in one searchable catalog.</p>
            <h2>Content and rights</h2>
            <p>We aim to publish accurate information and link only to content that we own, are licensed to distribute, or are legally permitted to reference. Availability, release details and quality labels may change.</p>
            <h2>Corrections and removals</h2>
            <p>Found inaccurate information or content that should be removed? Please use our <a href="<?php e(BASE_URL); ?>/contact.php">Contact page</a> or review the <a href="<?php e(BASE_URL); ?>/dmca.php">copyright policy</a>.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
