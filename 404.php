<?php
// BDMovieHub - Custom 404 Page
require_once __DIR__ . '/config.php';

$pageSection = 'none';
$pageTitle   = 'Page Not Found';
http_response_code(404);

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h); padding: 80px 0;">
    <div class="container" style="text-align:center; max-width: 720px;">
        <div style="font-size: 120px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 16px;">404</div>
        <h1 style="font-size: 32px; margin-bottom: 12px;">Page Not Found</h1>
        <p style="color: var(--muted); margin-bottom: 32px; font-size: 16px;">
            The page you are looking for doesn't exist, has been moved, or is no longer available.
            Try searching for what you need, or return to the homepage.
        </p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="<?php e(BASE_URL); ?>/index.php" class="btn btn-primary"><i class="fas fa-home"></i> Back to Home</a>
            <a href="<?php e(BASE_URL); ?>/search.php" class="btn btn-outline"><i class="fas fa-search"></i> Search Content</a>
            <a href="<?php e(BASE_URL); ?>/genres.php" class="btn btn-outline"><i class="fas fa-th"></i> Browse Genres</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
