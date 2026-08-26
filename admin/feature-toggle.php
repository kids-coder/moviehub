<?php
// BDMovieHub - Admin Feature Toggle (single-key flip, used by dashboard + settings)
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('settings.php');
}
requireCsrf();

$key = isset($_POST['key']) ? $_POST['key'] : '';
$allowed = array(
    'enable_lowdata', 'enable_notifications', 'enable_top10', 'enable_az',
    'enable_tvguide', 'enable_downloads', 'enable_comment_votes', 'enable_ratings',
);

if (!in_array($key, $allowed, true)) {
    setFlash('error', 'Unknown feature.');
    adminRedirect('settings.php');
}

// Only admins may change site-wide features
requireAdmin();

$settings = getData(FILE_SETTINGS);
$newValue = !(isset($settings[$key]) && !empty($settings[$key]));
$settings[$key] = $newValue;

if (saveData(FILE_SETTINGS, $settings)) {
    setFlash('success', 'Feature ' . ($newValue ? 'enabled' : 'disabled') . '.');
} else {
    setFlash('error', 'Failed to save setting.');
}

// Safe redirect target whitelist
$back = isset($_POST['back']) ? $_POST['back'] : 'settings.php';
if (!in_array($back, array('index.php', 'settings.php'), true)) {
    $back = 'settings.php';
}
adminRedirect($back);
