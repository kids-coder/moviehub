<?php
// BDMovieHub Configuration
// This file defines all site constants and loads helper functions + bootstrap

if (!defined('BDMOVIEHUB')) {
    define('BDMOVIEHUB', true);
}

// ===== ERROR HANDLING =====
// Always show errors during initial setup. Once the site is working, change to false.
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

// Custom error log path (use /data if writable, otherwise default)
$_dataDir0 = __DIR__ . '/data';
if (!is_dir($_dataDir0)) { @mkdir($_dataDir0, 0755, true); }
if (is_dir($_dataDir0) && is_writable($_dataDir0)) {
    @ini_set('error_log', $_dataDir0 . '/php-error.log');
}

// ===== FATAL ERROR HANDLER =====
// Catches fatal errors and shows a friendly message instead of a blank white page.
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true)) {
        // Clean any partial output that may have been sent
        if (ob_get_level() > 0) { ob_end_clean(); }
        http_response_code(500);
        $msg = DEBUG_MODE ? htmlspecialchars($err['message'] . "\n\nFile: " . $err['file'] . "\nLine: " . $err['line']) : 'Internal server error. Set DEBUG_MODE=true in config.php to see details.';
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Site Error</title>";
        echo "<style>body{font-family:Arial,sans-serif;background:#0a0a0f;color:#fff;padding:40px;line-height:1.6;}";
        echo ".err{max-width:800px;margin:0 auto;background:#1a1a2e;border:1px solid #e74c3c;border-radius:12px;padding:30px;}";
        echo "h1{color:#e74c3c;margin-top:0;}pre{background:#0a0a0f;padding:14px;border-radius:6px;overflow-x:auto;color:#ffa502;white-space:pre-wrap;}";
        echo "a{color:#469AFF;}</style></head><body><div class='err'>";
        echo "<h1>&#9888; Site Error</h1>";
        echo "<p>The site encountered an error while loading. Details below:</p>";
        echo "<pre>" . $msg . "</pre>";
        echo "<p style='margin-top:24px;color:#a0a0b8;font-size:13px;'>If you are the site owner, check the file mentioned above. ";
        echo "Visit <a href='diagnostics.php'>diagnostics.php</a> or <a href='test.php'>test.php</a> for help.</p>";
        echo "</div></body></html>";
    }
});

// Convert all PHP errors/warnings/notices into ErrorException (catchable)
// ONLY when debug mode is OFF, so the fatal handler above can still report cleanly.
// Actually, leaving native errors as-is is safer for InfinityFree compatibility.

// ===== SESSIONS =====
if (session_status() === PHP_SESSION_NONE) {
    // Use a private session path under /data if possible
    $sessPath = __DIR__ . '/data/sessions';
    if (!is_dir($sessPath)) { @mkdir($sessPath, 0755, true); }
    if (is_dir($sessPath) && is_writable($sessPath)) {
        @ini_set('session.save_path', $sessPath);
    }
    // Harden session cookie: HttpOnly + SameSite=Lax + Secure (when HTTPS)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $isHttps,
        ));
    } else {
        session_set_cookie_params(86400, '/; SameSite=Lax', null, $isHttps, true);
    }
    @session_start();
}

// ===== SITE IDENTITY =====
define('SITE_NAME', 'BDMovieHub');
define('SITE_URL', 'https://moviehub.gamer.gd');
define('SITE_DESC', 'Free Movies & Anime Streaming');

// ===== THEME COLORS =====
define('PRIMARY_COLOR', '#469AFF');   // Blue - Movies
define('ACCENT_COLOR', '#FF6B6B');    // Red accent
define('ANIME_COLOR', '#9b59b6');     // Purple - Anime
define('DARK_BG', '#0a0a0f');
define('CARD_BG', '#1a1a2e');

// ===== BROWSER STORAGE KEYS =====
define('THEME_KEY', 'moviehub-theme');
define('FAV_KEY', 'moviehub-favs');

// ===== DATA DIRECTORY =====
define('DATA_DIR', __DIR__ . '/data');

// ===== JSON DATA FILE PATHS =====
define('FILE_SETTINGS',  DATA_DIR . '/settings.json');
define('FILE_MOVIES',    DATA_DIR . '/movies.json');
define('FILE_ANIME',     DATA_DIR . '/anime.json');
define('FILE_EPISODES',  DATA_DIR . '/episodes.json');
define('FILE_PAGES',     DATA_DIR . '/pages.json');
define('FILE_SCHEDULE',  DATA_DIR . '/schedule.json');
define('FILE_USERS',     DATA_DIR . '/users.json');
define('FILE_CATEGORIES', DATA_DIR . '/categories.json');
define('FILE_FEATURED',  DATA_DIR . '/featured.json');
define('FILE_COMMENTS',  DATA_DIR . '/comments.json');
define('FILE_SLIDES',    DATA_DIR . '/slides.json');
define('FILE_GENRES',    DATA_DIR . '/genres.json');

// ===== DEFAULT ADMIN CREDENTIALS =====
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');

// ===== BASE_URL DETECTION =====
// Auto-detect the base URL so CSS/JS paths work whether the site is in the
// document root or in a subdirectory.
if (!defined('BASE_URL')) {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $scriptDir  = str_replace('\\', '/', dirname($scriptName));
    // If we're inside /admin, go up one level
    if (basename($scriptDir) === 'admin') {
        $scriptDir = str_replace('\\', '/', dirname($scriptDir));
    }
    // Normalize: root = empty string (so BASE_URL . '/index.php' = '/index.php')
    if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
        $scriptDir = '';
    }
    $scriptDir = str_replace('\\', '/', $scriptDir);
    define('BASE_URL', $scriptDir);
}

// ===== ASSETS_URL =====
define('ASSETS_URL', BASE_URL . '/assets');

// ===== LOAD HELPERS + AUTO-CREATOR =====
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/bootstrap.php';
