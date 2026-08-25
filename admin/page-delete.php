<?php
// BDMovieHub - Admin Page Delete
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('pages.php');
}
requireCsrf();

$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : '');
if ($id === '') { adminRedirect('pages.php'); }

$pages = getData(FILE_PAGES);
$newList = array();
$found = false;
foreach ($pages as $p) {
    if ($p['id'] === $id) { $found = true; continue; }
    $newList[] = $p;
}

if ($found) {
    saveData(FILE_PAGES, $newList);
    setFlash('success', 'Page deleted.');
} else {
    setFlash('error', 'Page not found.');
}
adminRedirect('pages.php');
