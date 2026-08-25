<?php
// BDMovieHub - Admin Episode Add
require_once __DIR__ . '/../config.php';
$adminPage = 'episodes';
$pageTitle = 'Add Episode';

$animeList = getData(FILE_ANIME);
$preselectAnime = isset($_GET['anime_id']) ? $_GET['anime_id'] : '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $anime_id       = isset($_POST['anime_id']) ? $_POST['anime_id'] : '';
    $episode_number = isset($_POST['episode_number']) ? intval($_POST['episode_number']) : 1;
    $title          = isset($_POST['title']) ? trim($_POST['title']) : '';
    $stream_url     = isset($_POST['stream_url']) ? trim($_POST['stream_url']) : '';
    $thumbnail      = isset($_POST['thumbnail']) ? trim($_POST['thumbnail']) : '';

    if ($anime_id === '') { $errors[] = 'Please select an anime.'; }
    if ($episode_number < 1) { $errors[] = 'Episode number must be at least 1.'; }

    if (empty($errors)) {
        $eps = getData(FILE_EPISODES);
        // Prevent duplicate episode numbers within same anime
        foreach ($eps as $ep) {
            if (isset($ep['anime_id']) && $ep['anime_id'] === $anime_id && intval($ep['episode_number']) === $episode_number) {
                $errors[] = 'Episode ' . $episode_number . ' already exists for this anime.';
                break;
            }
        }
    }

    if (empty($errors)) {
        $newEp = array(
            'id'             => generateId($eps, 'ep'),
            'anime_id'       => $anime_id,
            'episode_number' => $episode_number,
            'title'          => $title,
            'stream_url'     => $stream_url,
            'thumbnail'      => $thumbnail,
            'created_at'     => date('Y-m-d'),
        );
        $eps[] = $newEp;
        if (saveData(FILE_EPISODES, $eps)) {
            // Update anime episode count
            $animeList = getData(FILE_ANIME);
            foreach ($animeList as &$a) {
                if ($a['id'] === $anime_id) {
                    $a['episode_count'] = count(getEpisodesByAnime($anime_id));
                    break;
                }
            }
            unset($a);
            saveData(FILE_ANIME, $animeList);

            setFlash('success', 'Episode added successfully.');
            adminRedirect('episodes.php?anime_id=' . urlencode($anime_id));
        } else {
            $errors[] = 'Failed to save episode.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Add Episode</h2>
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

    <form method="POST" action="<?php e($adminUrl); ?>/episode-add.php">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Anime <span style="color:#e74c3c;">*</span></label>
                <select name="anime_id" required>
                    <option value="">-- Select Anime --</option>
                    <?php foreach ($animeList as $a): ?>
                        <option value="<?php e($a['id']); ?>" <?php echo $preselectAnime === $a['id'] ? 'selected' : ''; ?>>
                            <?php e($a['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Episode Number <span style="color:#e74c3c;">*</span></label>
                <input type="number" name="episode_number" min="1" required value="<?php echo isset($_POST['episode_number']) ? intval($_POST['episode_number']) : 1; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Episode Title</label>
            <input type="text" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES) : ''; ?>" placeholder="e.g. The Beginning">
        </div>

        <div class="form-group">
            <label>Stream URL (.m3u8) <span style="color:#9b59b6;">*</span></label>
            <input type="text" name="stream_url" required value="<?php echo isset($_POST['stream_url']) ? htmlspecialchars($_POST['stream_url'], ENT_QUOTES) : ''; ?>" placeholder="https://example.com/ep1.m3u8">
        </div>

        <div class="form-group">
            <label>Thumbnail URL</label>
            <input type="text" name="thumbnail" value="<?php echo isset($_POST['thumbnail']) ? htmlspecialchars($_POST['thumbnail'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-success"><i class="fas fa-save"></i> Save Episode</button>
            <a href="<?php e($adminUrl); ?>/episodes.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
