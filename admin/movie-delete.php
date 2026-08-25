<?php
// BDMovieHub - Admin Movie Delete
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('movies.php');
}
requireCsrf();

$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : '');
if ($id === '') { adminRedirect('movies.php'); }

$movies = getData(FILE_MOVIES);
$newList = array();
$found = false;
foreach ($movies as $m) {
    if ($m['id'] === $id) { $found = true; continue; }
    $newList[] = $m;
}

if ($found) {
    saveData(FILE_MOVIES, $newList);
    // Remove from featured
    $feat = getData(FILE_FEATURED);
    $newFeat = array();
    foreach ($feat as $f) {
        if (isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'movie') { continue; }
        $newFeat[] = $f;
    }
    saveData(FILE_FEATURED, $newFeat);
    setFlash('success', 'Movie deleted.');
} else {
    setFlash('error', 'Movie not found.');
}
adminRedirect('movies.php');
