<?php
// BDMovieHub - Admin Episode Delete
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('episodes.php');
}
requireCsrf();

$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : '');
if ($id === '') { adminRedirect('episodes.php'); }

$eps = getData(FILE_EPISODES);
$newList = array();
$found = false;
$animeId = '';
foreach ($eps as $ep) {
    if ($ep['id'] === $id) { $found = true; $animeId = isset($ep['anime_id']) ? $ep['anime_id'] : ''; continue; }
    $newList[] = $ep;
}

if ($found) {
    saveData(FILE_EPISODES, $newList);
    // Update anime episode count
    if ($animeId) {
        $animeList = getData(FILE_ANIME);
        foreach ($animeList as &$a) {
            if ($a['id'] === $animeId) {
                $a['episode_count'] = count(getEpisodesByAnime($animeId));
                break;
            }
        }
        unset($a);
        saveData(FILE_ANIME, $animeList);
    }
    setFlash('success', 'Episode deleted.');
} else {
    setFlash('error', 'Episode not found.');
}
adminRedirect('episodes.php' . ($animeId ? '?anime_id=' . urlencode($animeId) : ''));
