<?php
// BDMovieHub - Admin Episode Edit
require_once __DIR__ . '/../config.php';
$adminPage = 'episodes';
$pageTitle = 'Edit Episode';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$eps = getData(FILE_EPISODES);
$episode = getById($eps, $id);

if (!$episode) {
    setFlash('error', 'Episode not found.');
    adminRedirect('episodes.php');
}

$animeList = getData(FILE_ANIME);
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $anime_id       = isset($_POST['anime_id']) ? $_POST['anime_id'] : '';
    $episode_number = isset($_POST['episode_number']) ? intval($_POST['episode_number']) : 1;
    $title          = isset($_POST['title']) ? trim($_POST['title']) : '';
    $stream_url     = isset($_POST['stream_url']) ? trim($_POST['stream_url']) : '';
    $thumbnail      = isset($_POST['thumbnail']) ? trim($_POST['thumbnail']) : '';

    if ($anime_id === '') { $errors[] = 'Please select an anime.'; }

    if (empty($errors)) {
        foreach ($eps as &$ep) {
            if ($ep['id'] === $id) {
                $ep['anime_id']       = $anime_id;
                $ep['episode_number'] = $episode_number;
                $ep['title']          = $title;
                $ep['stream_url']     = $stream_url;
                $ep['thumbnail']      = $thumbnail;
                break;
            }
        }
        unset($ep);
        if (saveData(FILE_EPISODES, $eps)) {
            // Update anime count
            foreach ($animeList as &$a) {
                if ($a['id'] === $anime_id) {
                    $a['episode_count'] = count(getEpisodesByAnime($anime_id));
                    break;
                }
            }
            unset($a);
            saveData(FILE_ANIME, $animeList);
            setFlash('success', 'Episode updated.');
            adminRedirect('episodes.php?anime_id=' . urlencode($anime_id));
        } else {
            $errors[] = 'Failed to save.';
        }
    }
    $episode = array_merge($episode, $_POST);
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Edit Episode</h2>
        <a href="<?php e($adminUrl); ?>/episodes.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php e($adminUrl); ?>/episode-edit.php?id=<?php echo urlencode($id); ?>">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Anime</label>
                <select name="anime_id" required>
                    <?php foreach ($animeList as $a): ?>
                        <option value="<?php e($a['id']); ?>" <?php echo (isset($episode['anime_id']) && $episode['anime_id'] === $a['id']) ? 'selected' : ''; ?>>
                            <?php e($a['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Episode Number</label>
                <input type="number" name="episode_number" min="1" required value="<?php e(isset($episode['episode_number']) ? $episode['episode_number'] : 1); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Episode Title</label>
            <input type="text" name="title" value="<?php e(isset($episode['title']) ? $episode['title'] : ''); ?>">
        </div>

        <div class="form-group">
            <label>Stream URL (.m3u8)</label>
            <input type="text" name="stream_url" required value="<?php e(isset($episode['stream_url']) ? $episode['stream_url'] : ''); ?>">
        </div>

        <div class="form-group">
            <label>Thumbnail URL</label>
            <input type="text" name="thumbnail" value="<?php e(isset($episode['thumbnail']) ? $episode['thumbnail'] : ''); ?>">
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-success"><i class="fas fa-save"></i> Update Episode</button>
            <a href="<?php e($adminUrl); ?>/episodes.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
