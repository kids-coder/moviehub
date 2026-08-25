<?php
// BDMovieHub - Admin Anime Delete
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('anime.php');
}
requireCsrf();

$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : '');
if ($id === '') { adminRedirect('anime.php'); }

$animeList = getData(FILE_ANIME);
$newList = array();
$found = false;
foreach ($animeList as $a) {
    if ($a['id'] === $id) { $found = true; continue; }
    $newList[] = $a;
}

if ($found) {
    saveData(FILE_ANIME, $newList);
    // Remove related episodes
    $eps = getData(FILE_EPISODES);
    $newEps = array();
    foreach ($eps as $ep) {
        if (isset($ep['anime_id']) && $ep['anime_id'] === $id) { continue; }
        $newEps[] = $ep;
    }
    saveData(FILE_EPISODES, $newEps);
    // Remove from featured
    $feat = getData(FILE_FEATURED);
    $newFeat = array();
    foreach ($feat as $f) {
        if (isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'anime') { continue; }
        $newFeat[] = $f;
    }
    saveData(FILE_FEATURED, $newFeat);
    // Remove from schedule
    $sch = getData(FILE_SCHEDULE);
    $newSch = array();
    foreach ($sch as $s) {
        if (isset($s['anime_id']) && $s['anime_id'] === $id) { continue; }
        $newSch[] = $s;
    }
    saveData(FILE_SCHEDULE, $newSch);
    setFlash('success', 'Anime and its episodes deleted.');
} else {
    setFlash('error', 'Anime not found.');
}
adminRedirect('anime.php');
