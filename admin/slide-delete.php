<?php
// BDMovieHub - Admin Slide Delete
require_once __DIR__ . '/../config.php';
$adminPage = 'slides';
$pageTitle = 'Delete Slide';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('slides.php');
}
requireCsrf();

$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
if ($id === '') {
    setFlash('error', 'Slide ID required.');
    adminRedirect('slides.php');
}

$slides = getData(FILE_SLIDES);
$new = array();
$found = false;
foreach ($slides as $s) {
    if (isset($s['id']) && $s['id'] === $id) {
        $found = true;
    } else {
        $new[] = $s;
    }
}

if (!$found) {
    setFlash('error', 'Slide not found.');
    adminRedirect('slides.php');
}

if (saveData(FILE_SLIDES, $new)) {
    setFlash('success', 'Slide deleted.');
} else {
    setFlash('error', 'Failed to delete slide.');
}
adminRedirect('slides.php');
