<?php
// BDMovieHub - RSS Feed
require_once __DIR__ . '/config.php';

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/rss+xml; charset=utf-8');

$settings = getSettings();
$siteName = isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME;
$siteDesc = isset($settings['description']) ? $settings['description'] : SITE_DESC;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$siteBase = $scheme . '://' . $host . BASE_URL;

// Combine recently added items
$items = array();
foreach (getPublishedMovies() as $m) {
    $__path = ((isset($m['kind']) ? $m['kind'] : 'movie') === 'series') ? '/series/' : '/movie/';
    $items[] = array(
        'title'     => isset($m['title']) ? $m['title'] : 'Untitled',
        'link'      => $siteBase . $__path . urlencode(isset($m['slug']) ? $m['slug'] : ''),
        'desc'      => isset($m['description']) ? $m['description'] : '',
        'date'      => isset($m['created_at']) ? $m['created_at'] : date('Y-m-d'),
        'category'  => (isset($m['kind']) ? $m['kind'] : 'movie') === 'series' ? 'Series' : 'Movie',
    );
}
foreach (getPublishedAnime() as $a) {
    $items[] = array(
        'title'     => isset($a['title']) ? $a['title'] : 'Untitled',
        'link'      => $siteBase . '/anime/' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
        'desc'      => isset($a['description']) ? $a['description'] : '',
        'date'      => isset($a['created_at']) ? $a['created_at'] : date('Y-m-d'),
        'category'  => 'Anime',
    );
}

usort($items, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

$items = array_slice($items, 0, 50);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
echo '<channel>' . "\n";
echo '  <title>' . htmlspecialchars($siteName, ENT_XML1) . '</title>' . "\n";
echo '  <link>' . htmlspecialchars($siteBase, ENT_XML1) . '</link>' . "\n";
echo '  <description>' . htmlspecialchars($siteDesc, ENT_XML1) . '</description>' . "\n";
echo '  <language>en-us</language>' . "\n";
echo '  <lastBuildDate>' . date('D, d M Y H:i:s O') . '</lastBuildDate>' . "\n";
echo '  <atom:link href="' . htmlspecialchars($siteBase . '/rss.php', ENT_XML1) . '" rel="self" type="application/rss+xml" />' . "\n";

foreach ($items as $item) {
    $pubDate = date('D, d M Y H:i:s O', strtotime($item['date']));
    echo "  <item>\n";
    echo "    <title>" . htmlspecialchars($item['title'], ENT_XML1) . "</title>\n";
    echo "    <link>" . htmlspecialchars($item['link'], ENT_XML1) . "</link>\n";
    echo "    <guid isPermaLink=\"true\">" . htmlspecialchars($item['link'], ENT_XML1) . "</guid>\n";
    echo "    <description>" . htmlspecialchars(mb_substr($item['desc'], 0, 300, 'UTF-8'), ENT_XML1) . "</description>\n";
    echo "    <category>" . htmlspecialchars($item['category'], ENT_XML1) . "</category>\n";
    echo "    <pubDate>" . $pubDate . "</pubDate>\n";
    echo "  </item>\n";
}

echo '</channel>' . "\n";
echo '</rss>' . "\n";
exit;
