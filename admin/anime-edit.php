<?php
// BDMovieHub - Admin Anime Edit
require_once __DIR__ . '/../config.php';
$adminPage = 'anime';
$pageTitle = 'Edit Anime';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$animeList = getData(FILE_ANIME);
$anime = getById($animeList, $id);

if (!$anime) {
    setFlash('error', 'Anime not found.');
    adminRedirect('anime.php');
}

$genres = getAllGenres();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title         = isset($_POST['title']) ? trim($_POST['title']) : '';
    $slug          = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $poster        = isset($_POST['poster']) ? trim($_POST['poster']) : '';
    $banner        = isset($_POST['banner']) ? trim($_POST['banner']) : '';
    $description   = isset($_POST['description']) ? trim($_POST['description']) : '';
    $rating        = isset($_POST['rating']) ? trim($_POST['rating']) : '';
    $status        = isset($_POST['status']) ? $_POST['status'] : 'ongoing';
    $status_pub    = isset($_POST['status_pub']) ? $_POST['status_pub'] : 'published';
    $episode_count = isset($_POST['episode_count']) ? intval($_POST['episode_count']) : 0;
    $aired         = isset($_POST['aired']) ? trim($_POST['aired']) : '';
    $studio        = isset($_POST['studio']) ? trim($_POST['studio']) : '';
    $featured      = isset($_POST['featured']) ? true : false;
    $genreArr      = isset($_POST['genres']) ? $_POST['genres'] : array();

    if ($title === '') { $errors[] = 'Title is required.'; }
    if ($slug === '') { $slug = slugify($title); }
    else { $slug = slugify($slug); }

    if (empty($errors)) {
        foreach ($animeList as &$a) {
            if ($a['id'] === $id) {
                $a['title']         = $title;
                $a['slug']          = $slug;
                $a['poster']        = $poster;
                $a['banner']        = $banner;
                $a['description']   = $description;
                $a['genre']         = array_values($genreArr);
                $a['rating']        = $rating;
                $a['status']        = $status;
                $a['status_pub']    = $status_pub;
                $a['episode_count'] = $episode_count;
                $a['aired']         = $aired;
                $a['studio']        = $studio;
                $a['featured']      = $featured;
                break;
            }
        }
        unset($a);

        if (saveData(FILE_ANIME, $animeList)) {
            $feat = getData(FILE_FEATURED);
            $exists = false;
            foreach ($feat as $f) {
                if (isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'anime') { $exists = true; break; }
            }
            if ($featured && !$exists) {
                $feat[] = array('id' => $id, 'type' => 'anime');
                saveData(FILE_FEATURED, $feat);
            } elseif (!$featured && $exists) {
                $newFeat = array();
                foreach ($feat as $f) {
                    if (!(isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'anime')) {
                        $newFeat[] = $f;
                    }
                }
                saveData(FILE_FEATURED, $newFeat);
            }
            setFlash('success', 'Anime updated successfully.');
            adminRedirect('anime.php');
        } else {
            $errors[] = 'Failed to save.';
        }
    }
    $anime = array_merge($anime, $_POST);
    $anime['genre'] = isset($_POST['genres']) ? $_POST['genres'] : array();
    $anime['featured'] = isset($_POST['featured']) ? true : false;
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Edit Anime</h2>
        <a href="<?php e($adminUrl); ?>/anime.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back</a>
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

    <form method="POST" action="<?php e($adminUrl); ?>/anime-edit.php?id=<?php echo urlencode($id); ?>">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php e(isset($anime['title']) ? $anime['title'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php e(isset($anime['slug']) ? $anime['slug'] : ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Poster URL</label>
                <input type="text" name="poster" value="<?php e(isset($anime['poster']) ? $anime['poster'] : ''); ?>">
                <?php if (!empty($anime['poster'])): ?>
                    <div style="margin-top:8px;"><img src="<?php echo htmlspecialchars($anime['poster'], ENT_QUOTES); ?>" style="width:80px; height:120px; object-fit:cover; border-radius:6px; border:1px solid #2a2a3e;"></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Banner URL</label>
                <input type="text" name="banner" value="<?php e(isset($anime['banner']) ? $anime['banner'] : ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php e(isset($anime['description']) ? $anime['description'] : ''); ?></textarea>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Rating (0-10)</label>
                <input type="text" name="rating" value="<?php e(isset($anime['rating']) ? $anime['rating'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Episode Count</label>
                <input type="number" name="episode_count" value="<?php e(isset($anime['episode_count']) ? $anime['episode_count'] : 0); ?>" min="0">
            </div>
            <div class="form-group">
                <label>Aired Date</label>
                <input type="text" name="aired" value="<?php e(isset($anime['aired']) ? $anime['aired'] : ''); ?>">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Studio</label>
                <input type="text" name="studio" value="<?php e(isset($anime['studio']) ? $anime['studio'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Airing Status</label>
                <select name="status">
                    <?php $s = isset($anime['status']) ? $anime['status'] : 'ongoing'; ?>
                    <option value="ongoing" <?php echo $s === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo $s === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="upcoming" <?php echo $s === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                </select>
            </div>
            <div class="form-group">
                <label>Publish Status</label>
                <select name="status_pub">
                    <?php $sp = isset($anime['status_pub']) ? $anime['status_pub'] : 'published'; ?>
                    <option value="published" <?php echo $sp === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $sp === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Genres</label>
            <div class="genre-checkboxes">
                <?php $selGenres = isset($anime['genre']) ? $anime['genre'] : array(); ?>
                <?php if (!is_array($selGenres)) { $selGenres = array(); } ?>
                <?php foreach ($genres as $g): ?>
                    <label>
                        <input type="checkbox" name="genres[]" value="<?php e($g); ?>" <?php echo in_array($g, $selGenres) ? 'checked' : ''; ?>>
                        <?php e($g); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-row">
                <input type="checkbox" name="featured" id="featured" value="1" <?php echo !empty($anime['featured']) ? 'checked' : ''; ?>>
                <label for="featured" style="margin:0;">Mark as Featured</label>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="background:#9b59b6;"><i class="fas fa-save"></i> Update Anime</button>
            <a href="<?php e($adminUrl); ?>/anime.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
