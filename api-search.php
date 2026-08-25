<?php
// BDMovieHub - AJAX Live Search Endpoint
// Returns JSON results for movies + anime matching the query
require_once __DIR__ . '/config.php';

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/* ---------- Simple IP-based rate limit (max 60 requests / minute) ---------- */
define('FILE_SEARCH_RATE', DATA_DIR . '/search_rate.json');
$__ip = clientIp();
$__rateData = getData(FILE_SEARCH_RATE);
$__now = time();
$__window = 60; // 1 minute
$__maxReq = 60;
if (!isset($__rateData[$__ip])) { $__rateData[$__ip] = array(); }
// Drop timestamps older than the window
$__rateData[$__ip] = array_values(array_filter($__rateData[$__ip], function ($t) use ($__now, $__window) {
    return ($__now - $t) < $__window;
}));
if (count($__rateData[$__ip]) >= $__maxReq) {
    http_response_code(429);
    echo json_encode(array('error' => 'Too many requests. Please slow down.', 'movies' => array(), 'anime' => array()));
    saveData(FILE_SEARCH_RATE, $__rateData);
    exit;
}
$__rateData[$__ip][] = $__now;
// Cap the file size: keep only last 50 IPs
if (count($__rateData) > 50) {
    $__rateData = array_slice($__rateData, -50, null, true);
}
saveData(FILE_SEARCH_RATE, $__rateData);
unset($__ip, $__rateData, $__now, $__window, $__maxReq);

/* ---------- Search ---------- */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '' || strlen($q) < 2) {
    echo json_encode(array('movies' => array(), 'anime' => array()));
    exit;
}
// Cap query length to prevent abuse
if (strlen($q) > 100) { $q = substr($q, 0, 100); }

$ql = strtolower($q);
$limit = 5;

$moviesOut = array();
foreach (getPublishedMovies() as $m) {
    $title = strtolower(isset($m['title']) ? $m['title'] : '');
    if (strpos($title, $ql) !== false) {
        $moviesOut[] = array(
            'id'     => isset($m['id']) ? $m['id'] : '',
            'title'  => isset($m['title']) ? $m['title'] : '',
            'slug'   => isset($m['slug']) ? $m['slug'] : '',
            'poster' => isset($m['poster']) ? $m['poster'] : '',
            'year'   => isset($m['year']) ? $m['year'] : '',
            'quality'=> isset($m['quality']) ? $m['quality'] : '',
            'rating' => isset($m['rating']) ? $m['rating'] : '',
            'url'    => BASE_URL . '/movie.php?slug=' . urlencode(isset($m['slug']) ? $m['slug'] : ''),
        );
        if (count($moviesOut) >= $limit) { break; }
    }
}

$animeOut = array();
foreach (getPublishedAnime() as $a) {
    $title = strtolower(isset($a['title']) ? $a['title'] : '');
    if (strpos($title, $ql) !== false) {
        $animeOut[] = array(
            'id'     => isset($a['id']) ? $a['id'] : '',
            'title'  => isset($a['title']) ? $a['title'] : '',
            'slug'   => isset($a['slug']) ? $a['slug'] : '',
            'poster' => isset($a['poster']) ? $a['poster'] : '',
            'status' => isset($a['status']) ? $a['status'] : '',
            'rating' => isset($a['rating']) ? $a['rating'] : '',
            'url'    => BASE_URL . '/anime-watch.php?slug=' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
        );
        if (count($animeOut) >= $limit) { break; }
    }
}

echo json_encode(array(
    'movies' => $moviesOut,
    'anime'  => $animeOut,
    'total'  => count($moviesOut) + count($animeOut),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
