<?php
// BDMovieHub - Admin Anime Add
require_once __DIR__ . '/../config.php';
$adminPage = 'anime';
$pageTitle = 'Add Anime';

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
        $animeList = getData(FILE_ANIME);
        if (getBySlug($animeList, $slug)) {
            $slug = $slug . '-' . substr(md5(time()), 0, 4);
        }
        $newAnime = array(
            'id'            => generateId($animeList, 'a'),
            'title'         => $title,
            'slug'          => $slug,
            'poster'        => $poster,
            'banner'        => $banner,
            'description'   => $description,
            'genre'         => array_values($genreArr),
            'rating'        => $rating,
            'status'        => $status,
            'episode_count' => $episode_count,
            'aired'         => $aired,
            'studio'        => $studio,
            'status_pub'    => $status_pub,
            'featured'      => $featured,
            'created_at'    => date('Y-m-d'),
        );
        $animeList[] = $newAnime;
        if (saveData(FILE_ANIME, $animeList)) {
            if ($featured) {
                $feat = getData(FILE_FEATURED);
                $feat[] = array('id' => $newAnime['id'], 'type' => 'anime');
                saveData(FILE_FEATURED, $feat);
            }
            setFlash('success', 'Anime added successfully.');
            adminRedirect('anime.php');
        } else {
            $errors[] = 'Failed to save. Check file permissions.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Add New Anime</h2>
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

    <form method="POST" action="<?php e($adminUrl); ?>/anime-add.php">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug'], ENT_QUOTES) : ''; ?>" placeholder="auto-generated">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Poster URL</label>
                <input type="text" name="poster" value="<?php echo isset($_POST['poster']) ? htmlspecialchars($_POST['poster'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Banner URL</label>
                <input type="text" name="banner" value="<?php echo isset($_POST['banner']) ? htmlspecialchars($_POST['banner'], ENT_QUOTES) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : ''; ?></textarea>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Rating (0-10)</label>
                <input type="text" name="rating" value="<?php echo isset($_POST['rating']) ? htmlspecialchars($_POST['rating'], ENT_QUOTES) : ''; ?>" placeholder="9.0">
            </div>
            <div class="form-group">
                <label>Episode Count</label>
                <input type="number" name="episode_count" value="<?php echo isset($_POST['episode_count']) ? intval($_POST['episode_count']) : 0; ?>" min="0">
            </div>
            <div class="form-group">
                <label>Aired Date</label>
                <input type="text" name="aired" value="<?php echo isset($_POST['aired']) ? htmlspecialchars($_POST['aired'], ENT_QUOTES) : ''; ?>" placeholder="2024-01">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Studio</label>
                <input type="text" name="studio" value="<?php echo isset($_POST['studio']) ? htmlspecialchars($_POST['studio'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Airing Status</label>
                <select name="status">
                    <?php $s = isset($_POST['status']) ? $_POST['status'] : 'ongoing'; ?>
                    <option value="ongoing" <?php echo $s === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo $s === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="upcoming" <?php echo $s === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                </select>
            </div>
            <div class="form-group">
                <label>Publish Status</label>
                <select name="status_pub">
                    <?php $sp = isset($_POST['status_pub']) ? $_POST['status_pub'] : 'published'; ?>
                    <option value="published" <?php echo $sp === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $sp === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Genres</label>
            <div class="genre-checkboxes">
                <?php $selGenres = isset($_POST['genres']) ? $_POST['genres'] : array(); ?>
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
                <input type="checkbox" name="featured" id="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                <label for="featured" style="margin:0;">Mark as Featured</label>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="background:#9b59b6;"><i class="fas fa-save"></i> Save Anime</button>
            <a href="<?php e($adminUrl); ?>/anime.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
