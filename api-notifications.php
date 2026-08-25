<?php
// BDMovieHub - New Content Notification Endpoint
// Returns the newest published titles so the client can show a
// "New episodes / releases" toast. Read-only, rate-limited, cache-friendly.
require_once __DIR__ . '/config.php';

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/* ---------- Simple IP-based rate limit (max 20 requests / minute) ---------- */
define('FILE_NOTIF_RATE', DATA_DIR . '/notif_rate.json');
$__ip = clientIp();
$__rateData = getData(FILE_NOTIF_RATE);
$__now = time();
$__window = 60;
$__maxReq = 20;
if (!isset($__rateData[$__ip])) { $__rateData[$__ip] = array(); }
$__rateData[$__ip] = array_values(array_filter($__rateData[$__ip], function ($t) use ($__now, $__window) {
    return ($__now - $t) < $__window;
}));
if (count($__rateData[$__ip]) >= $__maxReq) {
    http_response_code(429);
    echo json_encode(array('error' => 'Too many requests.', 'items' => array()));
    saveData(FILE_NOTIF_RATE, $__rateData);
    exit;
}
$__rateData[$__ip][] = $__now;
if (count($__rateData) > 50) {
    $__rateData = array_slice($__rateData, -50, null, true);
}
saveData(FILE_NOTIF_RATE, $__rateData);
unset($__ip, $__rateData, $__now, $__window, $__maxReq);

/* ---------- Build latest items ---------- */
$items = array();

foreach (getPublishedMovies() as $m) {
    $items[] = array(
        'type'  => 'movie',
        'title' => isset($m['title']) ? $m['title'] : '',
        'slug'  => isset($m['slug']) ? $m['slug'] : '',
        'url'   => BASE_URL . '/movie.php?slug=' . urlencode(isset($m['slug']) ? $m['slug'] : ''),
        'ts'    => isset($m['created_at']) ? strtotime($m['created_at']) : 0,
        'label' => 'New Movie',
    );
}
foreach (getPublishedAnime() as $a) {
    // Latest episode timestamp counts as "new content" for series.
    $latestEp = 0;
    foreach (getEpisodesByAnime(isset($a['id']) ? $a['id'] : '') as $ep) {
        $t = isset($ep['created_at']) ? strtotime($ep['created_at']) : 0;
        if ($t > $latestEp) { $latestEp = $t; }
    }
    $items[] = array(
        'type'  => 'anime',
        'title' => isset($a['title']) ? $a['title'] : '',
        'slug'  => isset($a['slug']) ? $a['slug'] : '',
        'url'   => BASE_URL . '/anime-watch.php?slug=' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
        'ts'    => max((isset($a['created_at']) ? strtotime($a['created_at']) : 0), $latestEp),
        'label' => 'Series Update',
    );
}

usort($items, function ($x, $y) { return ($y['ts'] > $x['ts']) ? 1 : -1; });
$items = array_slice($items, 0, 8);

echo json_encode(
    array('items' => $items, 'server_time' => time()),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
exit;
