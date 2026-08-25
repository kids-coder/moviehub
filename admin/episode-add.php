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
    $download_url   = isset($_POST['download_url']) ? trim($_POST['download_url']) : '';
    // Backup streams: one URL per line. Subtitles: one "lang|label|url" per line.
    $alt_sources    = array();
    foreach (preg_split('/\r?\n/', isset($_POST['alt_sources']) ? $_POST['alt_sources'] : '') as $__l) {
        $__l = trim($__l);
        if ($__l !== '' && $__l !== $stream_url) { $alt_sources[] = $__l; }
    }
    $subtitle_tracks = array();
    foreach (preg_split('/\r?\n/', isset($_POST['subtitle_tracks']) ? $_POST['subtitle_tracks'] : '') as $__l) {
        $__l = trim($__l);
        if ($__l === '') { continue; }
        $__p = array_map('trim', explode('|', $__l));
        if (count($__p) === 3 && filter_var($__p[2], FILTER_VALIDATE_URL)) {
            $subtitle_tracks[] = array('lang' => $__p[0], 'label' => $__p[1], 'src' => $__p[2]);
        }
    }

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
            'download_url'   => $download_url,
            'alt_sources'    => $alt_sources,
            'subtitle_tracks' => $subtitle_tracks,
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

        <div class="form-group">
            <label>Download URL (optional)</label>
            <input type="text" name="download_url" value="<?php echo isset($_POST['download_url']) ? htmlspecialchars($_POST['download_url'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
            <small style="color:#8b8b9e;">Shown as a “Download Episode” button on the watch page.</small>
        </div>

        <div class="form-group">
            <label>Backup Stream URLs (one per line, optional)</label>
            <textarea name="alt_sources" rows="3"><?php echo isset($_POST['alt_sources']) ? htmlspecialchars($_POST['alt_sources'], ENT_QUOTES) : ''; ?></textarea>
            <small style="color:#8b8b9e;">Used automatically if the main stream fails.</small>
        </div>

        <div class="form-group">
            <label>Subtitle Tracks (one per line: lang|label|url)</label>
            <textarea name="subtitle_tracks" rows="3" placeholder="en|English|https://example.com/en.vtt"><?php echo isset($_POST['subtitle_tracks']) ? htmlspecialchars($_POST['subtitle_tracks'], ENT_QUOTES) : ''; ?></textarea>
            <small style="color:#8b8b9e;">WebVTT files. Example line: en|English|https://site.com/sub.vtt</small>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-success"><i class="fas fa-save"></i> Save Episode</button>
            <a href="<?php e($adminUrl); ?>/episodes.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
