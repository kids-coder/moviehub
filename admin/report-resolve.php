<?php
// BDMovieHub - Admin Broken-Video Report Resolve / Reopen
require_once __DIR__ . '/../config.php';
$adminPage = 'comments';
$pageTitle = 'Update Report';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('comments.php');
}
requireCsrf();

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : 'resolve';

if ($id === '') {
    setFlash('error', 'Report ID required.');
    adminRedirect('comments.php');
}
if (!in_array($action, array('resolve', 'reopen'), true)) {
    setFlash('error', 'Invalid action.');
    adminRedirect('comments.php');
}

$file = DATA_DIR . '/reports.json';
$data = array();
if (file_exists($file)) {
    $raw = @file_get_contents($file);
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) { $data = $decoded; }
    }
}

$found = false;
foreach ($data as $i => $r) {
    if (isset($r['id']) && $r['id'] === $id) {
        $data[$i]['resolved'] = ($action === 'resolve');
        $found = true;
        break;
    }
}

if (!$found) {
    setFlash('error', 'Report not found.');
    adminRedirect('comments.php');
}

if (@file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false) {
    setFlash('success', $action === 'resolve' ? 'Report marked as resolved.' : 'Report reopened.');
} else {
    setFlash('error', 'Failed to save report.');
}
adminRedirect('comments.php');
