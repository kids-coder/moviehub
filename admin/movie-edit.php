<?php
// BDMovieHub - Admin Movie Edit
require_once __DIR__ . '/../config.php';
$adminPage = 'movies';
$pageTitle = 'Edit Movie';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$movies = getData(FILE_MOVIES);
$movie = getById($movies, $id);

if (!$movie) {
    setFlash('error', 'Movie not found.');
    adminRedirect('movies.php');
}

$genres = getAllGenres();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title        = isset($_POST['title']) ? trim($_POST['title']) : '';
    $slug         = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $poster       = isset($_POST['poster']) ? trim($_POST['poster']) : '';
    $banner       = isset($_POST['banner']) ? trim($_POST['banner']) : '';
    $description  = isset($_POST['description']) ? trim($_POST['description']) : '';
    $year         = isset($_POST['year']) ? trim($_POST['year']) : '';
    $rating       = isset($_POST['rating']) ? trim($_POST['rating']) : '';
    $duration     = isset($_POST['duration']) ? trim($_POST['duration']) : '';
    $quality      = isset($_POST['quality']) ? trim($_POST['quality']) : 'HD';
    $trailer      = isset($_POST['trailer']) ? trim($_POST['trailer']) : '';
    $stream_url   = isset($_POST['stream_url']) ? trim($_POST['stream_url']) : '';
    $download_url = isset($_POST['download_url']) ? trim($_POST['download_url']) : '';
    $status       = isset($_POST['status']) ? $_POST['status'] : 'published';
    $featured     = isset($_POST['featured']) ? true : false;
    $genreArr     = isset($_POST['genres']) ? $_POST['genres'] : array();

    if ($title === '') { $errors[] = 'Title is required.'; }
    if ($slug === '') { $slug = slugify($title); }
    else { $slug = slugify($slug); }

    if (empty($errors)) {
        // Slug uniqueness (excluding current)
        foreach ($movies as $mm) {
            if ($mm['id'] !== $id && isset($mm['slug']) && $mm['slug'] === $slug) {
                $slug = $slug . '-' . substr(md5(time()), 0, 4);
                break;
            }
        }
        // Update fields
        foreach ($movies as &$mm) {
            if ($mm['id'] === $id) {
                $mm['title']        = $title;
                $mm['slug']         = $slug;
                $mm['poster']       = $poster;
                $mm['banner']       = $banner;
                $mm['description']  = $description;
                $mm['year']         = $year;
                $mm['genre']        = array_values($genreArr);
                $mm['rating']       = $rating;
                $mm['duration']     = $duration;
                $mm['quality']      = $quality;
                $mm['status']       = $status;
                $mm['trailer']      = $trailer;
                $mm['stream_url']   = $stream_url;
                $mm['download_url'] = $download_url;
                $mm['featured']     = $featured;
                break;
            }
        }
        unset($mm);

        if (saveData(FILE_MOVIES, $movies)) {
            // Update featured list
            $feat = getData(FILE_FEATURED);
            $exists = false;
            foreach ($feat as $f) {
                if (isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'movie') { $exists = true; break; }
            }
            if ($featured && !$exists) {
                $feat[] = array('id' => $id, 'type' => 'movie');
                saveData(FILE_FEATURED, $feat);
            } elseif (!$featured && $exists) {
                $newFeat = array();
                foreach ($feat as $f) {
                    if (!(isset($f['id']) && $f['id'] === $id && isset($f['type']) && $f['type'] === 'movie')) {
                        $newFeat[] = $f;
                    }
                }
                saveData(FILE_FEATURED, $newFeat);
            }
            setFlash('success', 'Movie updated successfully.');
            adminRedirect('movies.php');
        } else {
            $errors[] = 'Failed to save. Check file permissions.';
        }
    }
    // On error, re-populate from POST
    $movie = array_merge($movie, $_POST);
    $movie['genre'] = isset($_POST['genres']) ? $_POST['genres'] : array();
    $movie['featured'] = isset($_POST['featured']) ? true : false;
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Edit Movie</h2>
        <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back</a>
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

    <form method="POST" action="<?php e($adminUrl); ?>/movie-edit.php?id=<?php echo urlencode($id); ?>">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php e(isset($movie['title']) ? $movie['title'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php e(isset($movie['slug']) ? $movie['slug'] : ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Poster URL</label>
                <input type="text" name="poster" value="<?php e(isset($movie['poster']) ? $movie['poster'] : ''); ?>">
                <?php if (!empty($movie['poster'])): ?>
                    <div style="margin-top:8px;"><img src="<?php echo htmlspecialchars($movie['poster'], ENT_QUOTES); ?>" style="width:80px; height:120px; object-fit:cover; border-radius:6px; border:1px solid #2a2a3e;"></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Banner URL</label>
                <input type="text" name="banner" value="<?php e(isset($movie['banner']) ? $movie['banner'] : ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php e(isset($movie['description']) ? $movie['description'] : ''); ?></textarea>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Year</label>
                <input type="text" name="year" value="<?php e(isset($movie['year']) ? $movie['year'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Rating (0-10)</label>
                <input type="text" name="rating" value="<?php e(isset($movie['rating']) ? $movie['rating'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Duration</label>
                <input type="text" name="duration" value="<?php e(isset($movie['duration']) ? $movie['duration'] : ''); ?>">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Quality</label>
                <select name="quality">
                    <?php $q = isset($movie['quality']) ? $movie['quality'] : 'HD'; ?>
                    <?php foreach (array('HD', 'FHD', '4K', 'CAM', 'TS', 'SD') as $opt): ?>
                        <option value="<?php e($opt); ?>" <?php echo $q === $opt ? 'selected' : ''; ?>><?php e($opt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php $s = isset($movie['status']) ? $movie['status'] : 'published'; ?>
                    <option value="published" <?php echo $s === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $s === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="checkbox-row">
                    <input type="checkbox" name="featured" id="featured" value="1" <?php echo !empty($movie['featured']) ? 'checked' : ''; ?>>
                    <label for="featured" style="margin:0;">Mark as Featured</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Genres</label>
            <div class="genre-checkboxes">
                <?php $selGenres = isset($movie['genre']) ? $movie['genre'] : array(); ?>
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
            <label>Trailer URL</label>
            <input type="text" name="trailer" value="<?php e(isset($movie['trailer']) ? $movie['trailer'] : ''); ?>">
        </div>

        <div class="form-group">
            <label>Stream URL (.m3u8)</label>
            <input type="text" name="stream_url" value="<?php e(isset($movie['stream_url']) ? $movie['stream_url'] : ''); ?>">
        </div>

        <div class="form-group">
            <label>Download URL</label>
            <input type="text" name="download_url" value="<?php e(isset($movie['download_url']) ? $movie['download_url'] : ''); ?>">
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Update Movie</button>
            <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
