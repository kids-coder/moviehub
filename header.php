<?php
// BDMovieHub - Header partial
// Included by frontend pages. Expects $pageSection variable to mark active nav.
// $pageSection values: home, movies, anime, schedule, search, page, none
// $isAnimePage (bool) - if true, body class includes anime-page and anime.css is loaded
// Optional: $ogTitle, $ogDescription, $ogImage, $ogUrl for SEO meta tags

if (!defined('BDMOVIEHUB')) { exit('Direct access denied'); }

$pageSection = isset($pageSection) ? $pageSection : 'home';
$isAnimePage = isset($isAnimePage) ? $isAnimePage : false;
$pageTitle   = isset($pageTitle) ? $pageTitle : SITE_NAME;
$settings    = getSettings();
$pages       = getPublishedPages();

$bodyClass = '';
if ($isAnimePage) { $bodyClass .= ' anime-page'; }

// Start output buffering so we can catch fatal errors cleanly
if (ob_get_level() === 0) { ob_start(); }

// SVG favicon properly URL-encoded (so it doesn't break HTML parsing)
$faviconSvg = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20100%20100'%3E%3Crect%20width='100'%20height='100'%20rx='20'%20fill='%23469AFF'/%3E%3Ctext%20x='50'%20y='68'%20font-size='60'%20text-anchor='middle'%20fill='white'%20font-family='Arial'%20font-weight='bold'%3EB%3C/text%3E%3C/svg%3E";

// Optional logo URL from settings
$logoUrl = isset($settings['logo_url']) ? $settings['logo_url'] : '';

// SEO meta tags (optional - pages can set $ogTitle etc.)
$ogTitle = isset($ogTitle) ? $ogTitle : $pageTitle;
$ogDescription = isset($ogDescription) ? $ogDescription : (isset($settings['description']) ? $settings['description'] : SITE_DESC);
$ogImage = isset($ogImage) ? $ogImage : (isset($settings['seo_og_image']) ? $settings['seo_og_image'] : '');
$ogUrl = isset($ogUrl) ? $ogUrl : currentUrl();
$seoKeywords = !empty($settings['seo_keywords']) ? $settings['seo_keywords'] : 'Bangla movies, movies, series, anime, trailers, genres';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e($pageTitle); ?> - <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?></title>
    <meta name="description" content="<?php e($ogDescription); ?>">
    <meta name="keywords" content="<?php e($seoKeywords); ?>">
    <meta name="author" content="<?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?>">
    <meta name="robots" content="index, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php e($ogUrl); ?>">

    <!-- Open Graph / Twitter Card Meta -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php e($ogTitle); ?>">
    <meta property="og:description" content="<?php e($ogDescription); ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?php e($ogImage); ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php e($ogUrl); ?>">
    <meta property="og:site_name" content="<?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php e($ogTitle); ?>">
    <meta name="twitter:description" content="<?php e($ogDescription); ?>">
    <?php if (!empty($ogImage)): ?>
    <meta name="twitter:image" content="<?php e($ogImage); ?>">
    <?php endif; ?>

    <link rel="icon" type="image/svg+xml" href="<?php e($faviconSvg); ?>">
    <link rel="alternate" type="application/rss+xml" title="<?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?> RSS" href="<?php e(BASE_URL); ?>/rss.php">
    <link rel="sitemap" type="application/xml" href="<?php e(BASE_URL); ?>/sitemap.php">
    <link rel="manifest" href="<?php e(BASE_URL); ?>/manifest.json">
    <meta name="theme-color" content="#469AFF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php e(ASSETS_URL); ?>/css/style.css">
    <?php if ($isAnimePage): ?>
    <link rel="stylesheet" href="<?php e(ASSETS_URL); ?>/css/anime.css">
    <?php endif; ?>
    <?php if (isset($loadPlayerCss) && $loadPlayerCss): ?>
    <link rel="stylesheet" href="<?php e(ASSETS_URL); ?>/css/player.css">
    <?php endif; ?>
    <style>
        :root {
            --primary: <?php e(isset($settings['primary_color']) ? $settings['primary_color'] : PRIMARY_COLOR); ?>;
            --accent: <?php e(isset($settings['accent_color']) ? $settings['accent_color'] : ACCENT_COLOR); ?>;
            --anime-color: <?php e(isset($settings['anime_color']) ? $settings['anime_color'] : ANIME_COLOR); ?>;
        }
        <?php if (!empty($settings['custom_css'])): ?>
        /* Custom CSS from settings */
        <?php echo $settings['custom_css']; // raw CSS, admin-trusted ?>
        <?php endif; ?>
    </style>
    <?php
    // Optional: Google Analytics or custom JS (admin-controlled)
    if (!empty($settings['analytics_code'])):
    ?>
    <!-- Analytics -->
    <?php echo $settings['analytics_code']; // admin-trusted raw JS ?>
    <?php endif; ?>
</head>
<body class="<?php e(trim($bodyClass)); ?>">

<!-- Top Navigation -->
<nav class="navbar">
    <div class="nav-inner">
        <?php if (!empty($logoUrl)): ?>
            <a href="<?php e(BASE_URL); ?>/index.php" class="nav-logo"><img src="<?php e($logoUrl); ?>" alt="<?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?>" style="height:36px; max-width:160px; object-fit:contain;"></a>
        <?php else: ?>
            <a href="<?php e(BASE_URL); ?>/index.php" class="nav-logo">BD<span>Movie</span><span class="accent">Hub</span></a>
        <?php endif; ?>
        <ul class="nav-links">
            <li><a href="<?php e(BASE_URL); ?>/index.php" class="<?php echo $pageSection === 'home' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php e(BASE_URL); ?>/search.php" class="<?php echo $pageSection === 'movies' ? 'active' : ''; ?>">Movies</a></li>
            <li><a href="<?php e(BASE_URL); ?>/anime.php" class="<?php echo $pageSection === 'anime' ? 'active' : ''; ?>">Anime</a></li>
            <li><a href="<?php e(BASE_URL); ?>/trending.php" class="<?php echo $pageSection === 'trending' ? 'active' : ''; ?>">Trending</a></li>
            <li><a href="<?php e(BASE_URL); ?>/anime-schedule.php" class="<?php echo $pageSection === 'schedule' ? 'active' : ''; ?>">Schedule</a></li>
            <li><a href="<?php e(BASE_URL); ?>/genres.php" class="<?php echo $pageSection === 'genres' ? 'active' : ''; ?>">Genres</a></li>
            <li><a href="<?php e(BASE_URL); ?>/request.php" class="<?php echo $pageSection === 'request' ? 'active' : ''; ?>">Request</a></li>
        </ul>
        <div class="nav-actions">
            <div class="nav-search-wrap">
                <input type="text" class="nav-search" id="nav-search-input" placeholder="Search movies & anime..." aria-label="Search" autocomplete="off">
                <div class="live-search-dropdown" id="live-search-dropdown" style="display:none;"></div>
            </div>
            <a href="<?php e(BASE_URL); ?>/favorites.php" class="nav-action-btn" title="My Favorites" aria-label="Favorites"><i class="fas fa-heart"></i></a>
            <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme"><i class="fas fa-sun"></i></button>
            <button class="hamburger" aria-label="Menu"><i class="fas fa-bars"></i></button>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div class="menu-overlay"></div>
<div class="mobile-menu">
    <a href="<?php e(BASE_URL); ?>/index.php">Home</a>
    <a href="<?php e(BASE_URL); ?>/search.php">Movies</a>
    <a href="<?php e(BASE_URL); ?>/anime.php">Anime</a>
    <a href="<?php e(BASE_URL); ?>/trending.php">Trending</a>
    <a href="<?php e(BASE_URL); ?>/top-rated.php">Top Rated</a>
    <a href="<?php e(BASE_URL); ?>/genres.php">Genres</a>
    <a href="<?php e(BASE_URL); ?>/anime-schedule.php">Schedule</a>
    <a href="<?php e(BASE_URL); ?>/favorites.php">My Favorites</a>
    <a href="<?php e(BASE_URL); ?>/search.php">Search</a>
    <?php if (!empty($pages)): ?>
        <?php foreach ($pages as $p): ?>
            <a href="<?php e(BASE_URL); ?>/page.php?slug=<?php echo urlencode(isset($p['slug']) ? $p['slug'] : ''); ?>"><?php e(isset($p['title']) ? $p['title'] : 'Page'); ?></a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Flash message container -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
    <div class="flash-msg toast <?php e(isset($flash['type']) ? $flash['type'] : 'info'); ?>" style="position:fixed;top:80px;right:24px;z-index:9999;">
        <?php e(isset($flash['msg']) ? $flash['msg'] : ''); ?>
    </div>
<?php endif; ?>

<script>
// Pass BASE_URL to JS for live search
window.BDMH_BASE_URL = '<?php e(BASE_URL); ?>';
</script>
