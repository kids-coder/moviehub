<?php
// BDMovieHub - XML Sitemap (auto-generated)
require_once __DIR__ . '/config.php';

// Disable output buffering and set XML header
while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

// Read site_url from settings (admin-configurable), fall back to constant, then to detected URL
$__settings = getSettings();
$siteBase = isset($__settings['site_url']) ? $__settings['site_url'] : SITE_URL;
// Fall back to detected BASE_URL if site_url is empty
if (empty($siteBase)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $siteBase = $scheme . '://' . $host . BASE_URL;
}
unset($__settings);

$urls = array();
$today = date('Y-m-d');

// Static pages
$staticPages = array(
    array('loc' => $siteBase . '/index.php', 'priority' => '1.0', 'freq' => 'daily'),
    array('loc' => $siteBase . '/search.php', 'priority' => '0.8', 'freq' => 'weekly'),
    array('loc' => $siteBase . '/anime.php', 'priority' => '0.9', 'freq' => 'daily'),
    array('loc' => $siteBase . '/anime-schedule.php', 'priority' => '0.6', 'freq' => 'weekly'),
    array('loc' => $siteBase . '/genres.php', 'priority' => '0.7', 'freq' => 'weekly'),
    array('loc' => $siteBase . '/trending.php', 'priority' => '0.8', 'freq' => 'daily'),
    array('loc' => $siteBase . '/top-rated.php', 'priority' => '0.7', 'freq' => 'weekly'),
    array('loc' => $siteBase . '/favorites.php', 'priority' => '0.4', 'freq' => 'monthly'),
    array('loc' => $siteBase . '/contact.php', 'priority' => '0.5', 'freq' => 'monthly'),
    array('loc' => $siteBase . '/dmca.php', 'priority' => '0.3', 'freq' => 'yearly'),
    array('loc' => $siteBase . '/privacy.php', 'priority' => '0.3', 'freq' => 'yearly'),
    array('loc' => $siteBase . '/terms.php', 'priority' => '0.3', 'freq' => 'yearly'),
    array('loc' => $siteBase . '/disclaimer.php', 'priority' => '0.3', 'freq' => 'yearly'),
);

// Custom pages
foreach (getPublishedPages() as $p) {
    $staticPages[] = array(
        'loc' => $siteBase . '/page.php?slug=' . urlencode(isset($p['slug']) ? $p['slug'] : ''),
        'priority' => '0.6', 'freq' => 'monthly',
    );
}

// Movies
foreach (getPublishedMovies() as $m) {
    $staticPages[] = array(
        'loc' => $siteBase . '/movie.php?slug=' . urlencode(isset($m['slug']) ? $m['slug'] : ''),
        'priority' => '0.8', 'freq' => 'weekly',
        'lastmod' => isset($m['created_at']) ? $m['created_at'] : $today,
    );
}

// Anime
foreach (getPublishedAnime() as $a) {
    $staticPages[] = array(
        'loc' => $siteBase . '/anime-watch.php?slug=' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
        'priority' => '0.8', 'freq' => 'weekly',
        'lastmod' => isset($a['created_at']) ? $a['created_at'] : $today,
    );
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($staticPages as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if (isset($u['lastmod'])) {
        echo "    <lastmod>" . htmlspecialchars($u['lastmod'], ENT_XML1) . "</lastmod>\n";
    } else {
        echo "    <lastmod>" . $today . "</lastmod>\n";
    }
    echo "    <changefreq>" . $u['freq'] . "</changefreq>\n";
    echo "    <priority>" . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
exit;
