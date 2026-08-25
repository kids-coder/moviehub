<?php
// BDMovieHub Bootstrap - auto-creates data files if missing
// Runs on every page load (included from config.php)

if (!defined('BDMOVIEHUB')) { exit('Direct access denied'); }

function ensureDataDir() {
    $dir = defined('DATA_DIR') ? DATA_DIR : __DIR__ . '/data';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    return is_dir($dir) && is_writable($dir);
}

function writeJson($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return @file_put_contents($file, $json) !== false;
}

function ensureFile($file, $default) {
    if (!file_exists($file) || filesize($file) === 0) {
        writeJson($file, $default);
    }
}

// Ensure data directory exists - if not, show a clear error
if (!ensureDataDir()) {
    $dir = defined('DATA_DIR') ? DATA_DIR : __DIR__ . '/data';
    // Don't die here - the user might be on the diagnostics page
    // Instead, set a global flag that the diagnostics page can check
    $GLOBALS['DATA_DIR_ERROR'] = 'Cannot create or write to data directory: ' . $dir;
}

// Ensure each data file exists with sensible defaults (silently fail if not writable)
ensureFile(FILE_SETTINGS, array(
    'site_name'     => SITE_NAME,
    'site_url'      => SITE_URL,
    'description'   => SITE_DESC,
    'primary_color' => PRIMARY_COLOR,
    'accent_color'  => ACCENT_COLOR,
    'anime_color'   => ANIME_COLOR,
));

ensureFile(FILE_MOVIES, array());
ensureFile(FILE_ANIME, array());
ensureFile(FILE_EPISODES, array());
ensureFile(FILE_PAGES, array());
ensureFile(FILE_SCHEDULE, array());

ensureFile(FILE_USERS, array(
    array(
        'id'         => 'u1',
        'username'   => DEFAULT_ADMIN_USER,
        // Hash the default password so it's never stored in plaintext.
        // password_hash() is available on PHP 5.5+ (InfinityFree runs PHP 7.4+).
        'password'   => password_hash(DEFAULT_ADMIN_PASS, PASSWORD_DEFAULT),
        'role'       => 'admin',
        'created_at' => date('Y-m-d'),
    ),
));

// One-time migration: if any existing user has a plaintext password, hash it.
// This runs only when the file was created before this version of bootstrap.php.
$__usersMigrated = false;
$__usersList = getData(FILE_USERS);
foreach ($__usersList as $__i => $__u) {
    $__pw = isset($__u['password']) ? $__u['password'] : '';
    if ($__pw === '' || preg_match('/^\$2[aby]\$|^\$argon2/', $__pw)) { continue; }
    // Plaintext password found — hash it
    $__usersList[$__i]['password'] = password_hash($__pw, PASSWORD_DEFAULT);
    $__usersMigrated = true;
}
if ($__usersMigrated) {
    saveData(FILE_USERS, $__usersList);
}
// Clear temp variable so it doesn't leak into global scope
unset($__usersList, $__usersMigrated, $__i, $__u, $__pw);

ensureFile(FILE_CATEGORIES, array(
    array('id' => 'cat1', 'name' => 'Hollywood', 'slug' => 'hollywood'),
    array('id' => 'cat2', 'name' => 'Bollywood', 'slug' => 'bollywood'),
    array('id' => 'cat3', 'name' => 'Anime',     'slug' => 'anime'),
));

ensureFile(FILE_FEATURED, array());
ensureFile(FILE_COMMENTS, array());

ensureFile(FILE_SLIDES, array(
    array(
        'id'    => 'sl1',
        'title' => 'Welcome to BDMovieHub',
        'image' => 'https://via.placeholder.com/1600x600/469AFF/ffffff?text=BDMovieHub',
        'url'   => 'index.php',
        'order' => 1,
    ),
));

ensureFile(FILE_GENRES, array(
    'Action', 'Adventure', 'Animation', 'Comedy', 'Crime', 'Documentary',
    'Drama', 'Fantasy', 'Horror', 'Mystery', 'Romance', 'Sci-Fi', 'Thriller',
));
