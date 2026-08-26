<?php
// BDMovieHub Helper Functions
// All helper functions used throughout the site

if (!defined('BDMOVIEHUB')) { exit('Direct access denied'); }

/**
 * Read JSON data from file (with static cache for current request)
 */
function getData($file) {
    static $cache = array();
    $key = $file;
    if (isset($cache[$key])) { return $cache[$key]; }
    if (!file_exists($file)) { return $cache[$key] = array(); }
    $raw = @file_get_contents($file);
    if ($raw === false || $raw === '') { return $cache[$key] = array(); }
    $data = json_decode($raw, true);
    if (!is_array($data)) { return $cache[$key] = array(); }
    return $cache[$key] = $data;
}

/**
 * Save data to JSON file
 */
function saveData($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $json) !== false;
}

/**
 * Get settings merged with defaults
 */
function getSettings() {
    $s = getData(FILE_SETTINGS);
    $defaults = array(
        'site_name'     => SITE_NAME,
        'site_url'      => SITE_URL,
        'description'   => SITE_DESC,
        'primary_color' => PRIMARY_COLOR,
        'accent_color'  => ACCENT_COLOR,
        'anime_color'   => ANIME_COLOR,
    );
    return array_merge($defaults, $s);
}

/**
 * Generate next sequential ID with given prefix (m, a, ep, pg, sch, u, sl, cat, c, i)
 */
function generateId($list, $prefix = 'i') {
    $max = 0;
    foreach ($list as $item) {
        $id = isset($item['id']) ? $item['id'] : '0';
        if (strpos($id, $prefix) === 0) {
            $num = intval(substr($id, strlen($prefix)));
            if ($num > $max) { $max = $num; }
        }
    }
    return $prefix . ($max + 1);
}

/**
 * Find item by ID
 */
function getById($list, $id) {
    foreach ($list as $item) {
        if ((isset($item['id']) ? $item['id'] : '') == $id) { return $item; }
    }
    return null;
}

/**
 * Find item by slug
 */
function getBySlug($list, $slug) {
    foreach ($list as $item) {
        if ((isset($item['slug']) ? $item['slug'] : '') == $slug) { return $item; }
    }
    return null;
}

/**
 * Get all published movies (preserves keys for slicing)
 */
function getPublishedMovies() {
    $out = array();
    foreach (getData(FILE_MOVIES) as $m) {
        if ((isset($m['status']) ? $m['status'] : '') === 'published') { $out[] = $m; }
    }
    return $out;
}

/**
 * Get all published anime
 */
function getPublishedAnime() {
    $out = array();
    foreach (getData(FILE_ANIME) as $a) {
        if ((isset($a['status_pub']) ? $a['status_pub'] : '') === 'published') { $out[] = $a; }
    }
    return $out;
}

/**
 * Get featured movies (by featured.json)
 */
function getFeaturedMovies($limit = 10) {
    $featured = getData(FILE_FEATURED);
    $ids = array();
    foreach ($featured as $f) {
        if (isset($f['type']) && $f['type'] === 'movie' && isset($f['id'])) { $ids[] = $f['id']; }
    }
    $out = array();
    foreach (getData(FILE_MOVIES) as $m) {
        if (in_array($m['id'], $ids)) { $out[] = $m; }
    }
    return array_slice($out, 0, $limit);
}

/**
 * Get featured anime
 */
function getFeaturedAnime($limit = 10) {
    $featured = getData(FILE_FEATURED);
    $ids = array();
    foreach ($featured as $f) {
        if (isset($f['type']) && $f['type'] === 'anime' && isset($f['id'])) { $ids[] = $f['id']; }
    }
    $out = array();
    foreach (getData(FILE_ANIME) as $a) {
        if (in_array($a['id'], $ids)) { $out[] = $a; }
    }
    return array_slice($out, 0, $limit);
}

/**
 * Get episodes for an anime
 */
function getEpisodesByAnime($animeId) {
    $out = array();
    foreach (getData(FILE_EPISODES) as $ep) {
        if ((isset($ep['anime_id']) ? $ep['anime_id'] : '') == $animeId) { $out[] = $ep; }
    }
    // Sort by episode_number ascending
    usort($out, function($a, $b) {
        $na = isset($a['episode_number']) ? intval($a['episode_number']) : 0;
        $nb = isset($b['episode_number']) ? intval($b['episode_number']) : 0;
        return $na - $nb;
    });
    return $out;
}

/**
 * Get schedule grouped by day
 */
function getScheduleByDay($day = null) {
    $schedule = getData(FILE_SCHEDULE);
    $anime = getData(FILE_ANIME);
    $animeMap = array();
    foreach ($anime as $a) { $animeMap[$a['id']] = $a; }
    $out = array();
    foreach ($schedule as $s) {
        if ($day !== null && (isset($s['day']) ? $s['day'] : '') !== $day) { continue; }
        $s['anime'] = isset($animeMap[$s['anime_id']]) ? $animeMap[$s['anime_id']] : null;
        $out[] = $s;
    }
    usort($out, function($a, $b) {
        $ta = isset($a['time']) ? $a['time'] : '00:00';
        $tb = isset($b['time']) ? $b['time'] : '00:00';
        return strcmp($ta, $tb);
    });
    return $out;
}

/**
 * Authentication helpers
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] != '';
}

function currentUser() {
    if (!isLoggedIn()) { return null; }
    $users = getData(FILE_USERS);
    return getById($users, $_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Determine if we're in admin context by checking the script path
        $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $inAdmin = (strpos($script, '/admin/') !== false) || (basename(dirname($script)) === 'admin');
        if ($inAdmin) {
            adminRedirect('login.php');
        } else {
            redirect('admin/login.php');
        }
    }
}

/**
 * Verify a password against a stored hash.
 * Supports BOTH bcrypt/argon2 hashes (from password_hash) AND legacy plaintext
 * passwords (auto-migrated to bcrypt on successful login).
 */
function verifyPassword($password, $storedHash) {
    // Modern hash from password_hash() — starts with $2y$, $2a$, $2b$,
    // $argon2i$, or $argon2id$. Use password_verify() for these.
    // Regex must be a SINGLE $ after the algorithm marker ($2y$), not $$.
    if (preg_match('/^\$2[aby]\$|^\$argon2/', $storedHash)) {
        return password_verify($password, $storedHash);
    }
    // Legacy plaintext (or empty) — direct comparison
    return hash_equals($storedHash, $password);
}

/**
 * Hash a password using bcrypt (default cost 10).
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Require the logged-in user to have the "admin" role.
 * Editors are redirected back to the dashboard with an error flash.
 */
function requireAdmin() {
    requireLogin();
    $u = currentUser();
    if (!$u || (isset($u['role']) ? $u['role'] : '') !== 'admin') {
        setFlash('error', 'Admin access required.');
        adminRedirect('index.php');
    }
    return $u;
}

function login($username, $password) {
    $users = getData(FILE_USERS);
    foreach ($users as $i => $u) {
        if ((isset($u['username']) ? $u['username'] : '') === $username) {
            $storedHash = isset($u['password']) ? $u['password'] : '';
            if (verifyPassword($password, $storedHash)) {
                // Migrate legacy plaintext password to bcrypt on successful login
                if (!preg_match('/^\$2[aby]\$|^\$argon2/', $storedHash)) {
                    $users[$i]['password'] = hashPassword($password);
                    saveData(FILE_USERS, $users);
                }
                // Regenerate session ID to prevent session fixation
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['login_time'] = time();
                return true;
            }
            return false;
        }
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}

/* ---------- CSRF Protection ---------- */

/**
 * Generate a CSRF token, store it in the session, and return it.
 * Re-uses an existing token if one is already set.
 */
function generateCsrfToken() {
    if (session_status() !== PHP_SESSION_ACTIVE) { return ''; }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden <input> field with the CSRF token.
 * Call this inside every POST <form>.
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Echo the CSRF field.
 */
function csrfInput() {
    echo csrfField();
}

/**
 * Verify the CSRF token sent via POST.
 * Returns true if valid, false otherwise.
 */
function verifyCsrf() {
    if (session_status() !== PHP_SESSION_ACTIVE) { return false; }
    $sent = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $stored = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if (empty($sent) || empty($stored)) { return false; }
    return hash_equals($stored, $sent);
}

/**
 * Verify CSRF token from POST or kill the request with 403.
 */
function requireCsrf() {
    if (!verifyCsrf()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Security Error</title>';
        echo '<style>body{font-family:Arial,sans-serif;background:#0a0a0f;color:#fff;padding:40px;text-align:center;}';
        echo '.err{max-width:600px;margin:0 auto;background:#1a1a2e;border:1px solid #e74c3c;border-radius:12px;padding:30px;}';
        echo 'h1{color:#e74c3c;margin-top:0;}a{color:#469AFF;}</style></head>';
        echo '<body><div class="err"><h1>&#9888; Security Check Failed</h1>';
        echo '<p>Your session expired or this request came from an unauthorized source.</p>';
        echo '<p>Please go back, refresh the page, and try again.</p>';
        echo '<p style="margin-top:20px;"><a href="' . htmlspecialchars((defined('BASE_URL') ? BASE_URL : '') . '/index.php', ENT_QUOTES) . '">Go to Homepage</a></p>';
        echo '</div></body></html>';
        exit;
    }
}

/* ---------- Login Rate Limiting ---------- */

define('FILE_LOGIN_ATTEMPTS', DATA_DIR . '/login_attempts.json');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 900); // 15 minutes

/**
 * Get the visitor's real IP (accounting for proxies).
 */
function clientIp() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/**
 * Check if the current IP is locked out. Returns array(locked, retry_after_seconds).
 */
function checkLoginLockout() {
    $ip = clientIp();
    $attempts = getData(FILE_LOGIN_ATTEMPTS);
    $list = isset($attempts[$ip]) ? $attempts[$ip] : array();
    $now = time();
    // Keep only attempts in the lockout window
    $recent = array();
    foreach ($list as $t) {
        if ($now - $t < LOGIN_LOCKOUT_SECONDS) { $recent[] = $t; }
    }
    if (count($recent) >= LOGIN_MAX_ATTEMPTS) {
        $oldest = min($recent);
        $retry = LOGIN_LOCKOUT_SECONDS - ($now - $oldest);
        return array(true, max(0, $retry));
    }
    return array(false, 0);
}

/**
 * Record a failed login attempt for the current IP.
 */
function recordFailedLogin() {
    $ip = clientIp();
    $attempts = getData(FILE_LOGIN_ATTEMPTS);
    if (!isset($attempts[$ip])) { $attempts[$ip] = array(); }
    $attempts[$ip][] = time();
    // Trim to last 20 attempts per IP to keep file small
    $attempts[$ip] = array_slice($attempts[$ip], -20);
    saveData(FILE_LOGIN_ATTEMPTS, $attempts);
}

/**
 * Clear failed login attempts for the current IP (called on successful login).
 */
function clearFailedLogins() {
    $ip = clientIp();
    $attempts = getData(FILE_LOGIN_ATTEMPTS);
    if (isset($attempts[$ip])) {
        unset($attempts[$ip]);
        saveData(FILE_LOGIN_ATTEMPTS, $attempts);
    }
}

function redirect($url) {
    // Accept either absolute URLs (http://...) or relative paths.
    // For relative paths, make them absolute relative to BASE_URL so HTTP/1.1
    // compliant and works from any subdirectory.
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        // Already absolute - use as-is
    } elseif (strpos($url, '/') === 0) {
        // Absolute path from web root - use as-is
    } else {
        // Relative - prepend base URL
        $base = defined('BASE_URL') ? BASE_URL : '';
        $url = $base . '/' . $url;
    }
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        // Fallback to JS redirect if headers already sent
        echo '<script>window.location.href="' . addslashes($url) . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES) . '"></noscript>';
    }
    exit;
}

/**
 * Redirect within the admin panel (prepends /admin to the URL)
 */
function adminRedirect($url) {
    $adminBase = (defined('BASE_URL') ? BASE_URL : '') . '/admin';
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        redirect($url);
        return;
    }
    if (strpos($url, '/') === 0) {
        redirect($url);
        return;
    }
    redirect($adminBase . '/' . $url);
}

/**
 * Sanitize output for HTML
 */
function sanitize($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}

function e($str) {
    echo htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Convert text to URL slug (no iconv dependency - works on all hosts)
 */
function slugify($text) {
    $text = (string)$text;
    // Replace common accented characters with ASCII equivalents
    $translit = array(
        // Latin-1 Supplement
        "\xC3\x80" => 'A', "\xC3\x81" => 'A', "\xC3\x82" => 'A', "\xC3\x83" => 'A',
        "\xC3\x84" => 'A', "\xC3\x85" => 'A', "\xC3\x86" => 'AE', "\xC3\x87" => 'C',
        "\xC3\x88" => 'E', "\xC3\x89" => 'E', "\xC3\x8A" => 'E', "\xC3\x8B" => 'E',
        "\xC3\x8C" => 'I', "\xC3\x8D" => 'I', "\xC3\x8E" => 'I', "\xC3\x8F" => 'I',
        "\xC3\x90" => 'D', "\xC3\x91" => 'N', "\xC3\x92" => 'O', "\xC3\x93" => 'O',
        "\xC3\x94" => 'O', "\xC3\x95" => 'O', "\xC3\x96" => 'O', "\xC3\x98" => 'O',
        "\xC3\x99" => 'U', "\xC3\x9A" => 'U', "\xC3\x9B" => 'U', "\xC3\x9C" => 'U',
        "\xC3\x9D" => 'Y', "\xC3\x9F" => 'ss',
        "\xC3\xA0" => 'a', "\xC3\xA1" => 'a', "\xC3\xA2" => 'a', "\xC3\xA3" => 'a',
        "\xC3\xA4" => 'a', "\xC3\xA5" => 'a', "\xC3\xA6" => 'ae', "\xC3\xA7" => 'c',
        "\xC3\xA8" => 'e', "\xC3\xA9" => 'e', "\xC3\xAA" => 'e', "\xC3\xAB" => 'e',
        "\xC3\xAC" => 'i', "\xC3\xAD" => 'i', "\xC3\xAE" => 'i', "\xC3\xAF" => 'i',
        "\xC3\xB0" => 'd', "\xC3\xB1" => 'n', "\xC3\xB2" => 'o', "\xC3\xB3" => 'o',
        "\xC3\xB4" => 'o', "\xC3\xB5" => 'o', "\xC3\xB6" => 'o', "\xC3\xB8" => 'o',
        "\xC3\xB9" => 'u', "\xC3\xBA" => 'u', "\xC3\xBB" => 'u', "\xC3\xBC" => 'u',
        "\xC3\xBD" => 'y', "\xC3\xBF" => 'y',
    );
    $text = strtr($text, $translit);
    // Replace anything that's not a letter, number, or hyphen with hyphen
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Remove anything that's not alphanumeric or hyphen (catches remaining unicode)
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    // Collapse multiple hyphens
    $text = preg_replace('~-+~', '-', $text);
    if ($text === '' || $text === '-') { $text = 'item-' . substr(md5(time() . mt_rand()), 0, 6); }
    return $text;
}

/**
 * Paginate an array
 */
function paginate($items, $page, $perPage = 20) {
    $total = count($items);
    $totalPages = max(1, ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    return array(
        'items'       => array_slice($items, $offset, $perPage),
        'page'        => $page,
        'total_pages' => $totalPages,
        'total'       => $total,
        'per_page'    => $perPage,
    );
}

/**
 * Get related movies by shared genre
 */
function getRelatedMovies($movie, $limit = 6) {
    $genres = isset($movie['genre']) ? $movie['genre'] : array();
    if (!is_array($genres) || empty($genres)) { return array(); }
    $out = array();
    foreach (getPublishedMovies() as $m) {
        if ($m['id'] === $movie['id']) { continue; }
        $mg = isset($m['genre']) ? $m['genre'] : array();
        if (!is_array($mg)) { $mg = array(); }
        $intersect = array_intersect($genres, $mg);
        if (count($intersect) > 0) { $out[] = $m; }
        if (count($out) >= $limit) { break; }
    }
    return $out;
}

/**
 * Get custom pages (published only)
 */
function getPublishedPages() {
    $out = array();
    foreach (getData(FILE_PAGES) as $p) {
        if ((isset($p['status']) ? $p['status'] : '') === 'published') { $out[] = $p; }
    }
    return $out;
}

/**
 * Get all genres (from genres.json + movies + anime) - unique
 */
function getAllGenres() {
    $out = getData(FILE_GENRES);
    foreach (getData(FILE_MOVIES) as $m) {
        $g = isset($m['genre']) ? $m['genre'] : array();
        if (is_array($g)) { $out = array_merge($out, $g); }
    }
    foreach (getData(FILE_ANIME) as $a) {
        $g = isset($a['genre']) ? $a['genre'] : array();
        if (is_array($g)) { $out = array_merge($out, $g); }
    }
    $out = array_unique($out);
    sort($out);
    return $out;
}

/**
 * Flash message helpers
 */
function setFlash($type, $msg) {
    $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Output JSON response (for AJAX endpoints if needed)
 */
function jsonOut($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get approved comments for an item (movie or anime)
 */
function getApprovedComments($itemType, $itemId) {
    $out = array();
    foreach (getData(FILE_COMMENTS) as $c) {
        if ((isset($c['item_type']) ? $c['item_type'] : '') === $itemType &&
            (isset($c['item_id']) ? $c['item_id'] : '') === $itemId &&
            (isset($c['status']) ? $c['status'] : 'pending') === 'approved') {
            $out[] = $c;
        }
    }
    usort($out, function ($a, $b) {
        $ta = isset($a['date']) ? $a['date'] : '';
        $tb = isset($b['date']) ? $b['date'] : '';
        return strcmp($tb, $ta);
    });
    return $out;
}

/**
 * Add a new comment.
 * Comment approval follows the admin setting `auto_approve_comments`:
 *   - If true (default): comment is 'approved' (visible immediately).
 *   - If false: comment is 'pending' (requires admin moderation).
 */
function addComment($data) {
    $comments = getData(FILE_COMMENTS);
    $data['id'] = 'c' . (count($comments) + 1) . '-' . substr(md5(time() . mt_rand()), 0, 6);
    $data['date'] = date('Y-m-d H:i:s');
    if (!isset($data['status'])) {
        $settings = getSettings();
        $autoApprove = isset($settings['auto_approve_comments']) ? (bool)$settings['auto_approve_comments'] : true;
        $data['status'] = $autoApprove ? 'approved' : 'pending';
    }
    $comments[] = $data;
    return saveData(FILE_COMMENTS, $comments);
}

/**
 * Get related anime by shared genre
 */
function getRelatedAnime($anime, $limit = 6) {
    $genres = isset($anime['genre']) ? $anime['genre'] : array();
    if (!is_array($genres) || empty($genres)) { return array(); }
    $out = array();
    foreach (getPublishedAnime() as $a) {
        if (isset($a['id']) && $anime['id'] === $a['id']) { continue; }
        $ag = isset($a['genre']) ? $a['genre'] : array();
        if (!is_array($ag)) { $ag = array(); }
        if (count(array_intersect($genres, $ag)) > 0) {
            $out[] = $a;
            if (count($out) >= $limit) { break; }
        }
    }
    return $out;
}

/**
 * Increment a movie/anime's view count (best-effort, non-blocking)
 */
function incrementViews($type, $id) {
    $file = ($type === 'anime') ? FILE_ANIME : FILE_MOVIES;
    $items = getData($file);
    $changed = false;
    foreach ($items as $i => $it) {
        if (isset($it['id']) && $it['id'] === $id) {
            $items[$i]['views'] = (isset($it['views']) ? intval($it['views']) : 0) + 1;
            $changed = true;
            break;
        }
    }
    if ($changed) { saveData($file, $items); }
}

/**
 * Output Open Graph + Twitter Card meta tags
 */
function outputMetaTags($title, $description, $image, $url) {
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(mb_substr($description, 0, 160, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    $image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . $title . '">' . "\n";
    echo '<meta property="og:description" content="' . $description . '">' . "\n";
    echo '<meta property="og:image" content="' . $image . '">' . "\n";
    echo '<meta property="og:url" content="' . $url . '">' . "\n";
    echo '<meta property="og:site_name" content="' . htmlspecialchars(SITE_NAME, ENT_QUOTES) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . $title . '">' . "\n";
    echo '<meta name="twitter:description" content="' . $description . '">' . "\n";
    echo '<meta name="twitter:image" content="' . $image . '">' . "\n";
}

/**
 * Output JSON-LD structured data for a movie/anime
 */
function outputJsonLd($item, $type) {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => ($type === 'anime') ? 'TVSeries' : 'Movie',
        'name'        => isset($item['title']) ? $item['title'] : '',
        'description' => isset($item['description']) ? mb_substr($item['description'], 0, 300, 'UTF-8') : '',
        'image'       => isset($item['poster']) ? $item['poster'] : '',
    );
    if (isset($item['year'])) { $schema['datePublished'] = $item['year']; }
    $ratingCount = isset($item['rating_count']) ? intval($item['rating_count']) : 0;
    if (isset($item['rating']) && $item['rating'] !== '' && $ratingCount > 0) {
        $schema['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => $item['rating'],
            'bestRating' => '10',
            'ratingCount' => $ratingCount,
        );
    }
    $schema['url'] = SITE_URL . (($type === 'anime') ? '/anime-watch.php?slug=' : '/movie.php?slug=') . urlencode(isset($item['slug']) ? $item['slug'] : '');
    if (isset($item['genre']) && is_array($item['genre'])) {
        $schema['genre'] = implode(', ', $item['genre']);
    }
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

/**
 * Output homepage WebSite schema with a real site search action.
 */
function outputWebsiteJsonLd() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'description' => SITE_DESC,
        'potentialAction' => array(
            '@type' => 'SearchAction',
            'target' => SITE_URL . '/search.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ),
    );
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

/**
 * Get current absolute URL (for canonical link / OG tags)
 */
function currentUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    return $scheme . '://' . $host . $uri;
}

/**
 * Truncate text to specified length with ellipsis
 */
function truncate($text, $length = 100) {
    if (mb_strlen($text, 'UTF-8') <= $length) { return $text; }
    return mb_substr($text, 0, $length, 'UTF-8') . '...';
}
