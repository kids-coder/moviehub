<?php
// BDMovieHub - Admin Comment Delete (also handles contact messages and reports)
require_once __DIR__ . '/../config.php';
$adminPage = 'comments';
$pageTitle = 'Delete';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('comments.php');
}
requireCsrf();

$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : 'comment');

if ($id === '') {
    setFlash('error', 'ID required.');
    adminRedirect('comments.php');
}

if ($type === 'contact') {
    $file = DATA_DIR . '/contacts.json';
    $data = array();
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) { $data = $decoded; }
        }
    }
    $new = array();
    foreach ($data as $item) {
        if (!(isset($item['id']) && $item['id'] === $id)) { $new[] = $item; }
    }
    @file_put_contents($file, json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    setFlash('success', 'Message deleted.');
} elseif ($type === 'report') {
    $file = DATA_DIR . '/reports.json';
    $data = array();
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) { $data = $decoded; }
        }
    }
    $new = array();
    foreach ($data as $item) {
        if (!(isset($item['id']) && $item['id'] === $id)) { $new[] = $item; }
    }
    @file_put_contents($file, json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    setFlash('success', 'Report deleted.');
} else {
    $comments = getData(FILE_COMMENTS);
    $new = array();
    foreach ($comments as $c) {
        if (!(isset($c['id']) && $c['id'] === $id)) { $new[] = $c; }
    }
    saveData(FILE_COMMENTS, $new);
    setFlash('success', 'Comment deleted.');
}
adminRedirect('comments.php');
