<?php
// BDMovieHub - Admin Comment Approve
require_once __DIR__ . '/../config.php';
$adminPage = 'comments';
$pageTitle = 'Approve Comment';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('comments.php');
}
requireCsrf();

$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
if ($id === '') {
    setFlash('error', 'Comment ID required.');
    adminRedirect('comments.php');
}

$comments = getData(FILE_COMMENTS);
$found = false;
foreach ($comments as $i => $c) {
    if (isset($c['id']) && $c['id'] === $id) {
        $comments[$i]['status'] = 'approved';
        $found = true;
        break;
    }
}

if (!$found) {
    setFlash('error', 'Comment not found.');
    adminRedirect('comments.php');
}

if (saveData(FILE_COMMENTS, $comments)) {
    setFlash('success', 'Comment approved.');
} else {
    setFlash('error', 'Failed to approve comment.');
}
adminRedirect('comments.php');
