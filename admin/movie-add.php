<?php
// BDMovieHub - Admin Movie Add
require_once __DIR__ . '/../config.php';
$adminPage = 'movies';
$pageTitle = 'Add Movie';

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
        $movies = getData(FILE_MOVIES);
        // Check slug uniqueness
        if (getBySlug($movies, $slug)) {
            $slug = $slug . '-' . substr(md5(time()), 0, 4);
        }
        $newMovie = array(
            'id'           => generateId($movies, 'm'),
            'title'        => $title,
            'slug'         => $slug,
            'poster'       => $poster,
            'banner'       => $banner,
            'description'  => $description,
            'year'         => $year,
            'genre'        => array_values($genreArr),
            'rating'       => $rating,
            'duration'     => $duration,
            'quality'      => $quality,
            'status'       => $status,
            'trailer'      => $trailer,
            'stream_url'   => $stream_url,
            'download_url' => $download_url,
            'featured'     => $featured,
            'created_at'   => date('Y-m-d'),
        );
        $movies[] = $newMovie;
        if (saveData(FILE_MOVIES, $movies)) {
            // Also add to featured list if marked
            if ($featured) {
                $feat = getData(FILE_FEATURED);
                $feat[] = array('id' => $newMovie['id'], 'type' => 'movie');
                saveData(FILE_FEATURED, $feat);
            }
            setFlash('success', 'Movie added successfully.');
            adminRedirect('movies.php');
        } else {
            $errors[] = 'Failed to save movie. Check file permissions.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Add New Movie</h2>
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

    <form method="POST" action="<?php e($adminUrl); ?>/movie-add.php">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug'], ENT_QUOTES) : ''; ?>" placeholder="auto-generated from title">
                <div class="hint">Leave blank to auto-generate from title.</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Poster URL</label>
                <input type="text" name="poster" value="<?php echo isset($_POST['poster']) ? htmlspecialchars($_POST['poster'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Banner URL</label>
                <input type="text" name="banner" value="<?php echo isset($_POST['banner']) ? htmlspecialchars($_POST['banner'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : ''; ?></textarea>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Year</label>
                <input type="text" name="year" value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year'], ENT_QUOTES) : ''; ?>" placeholder="2024">
            </div>
            <div class="form-group">
                <label>Rating (0-10)</label>
                <input type="text" name="rating" value="<?php echo isset($_POST['rating']) ? htmlspecialchars($_POST['rating'], ENT_QUOTES) : ''; ?>" placeholder="8.5">
            </div>
            <div class="form-group">
                <label>Duration</label>
                <input type="text" name="duration" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration'], ENT_QUOTES) : ''; ?>" placeholder="2h 15m">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Quality</label>
                <select name="quality">
                    <?php $q = isset($_POST['quality']) ? $_POST['quality'] : 'HD'; ?>
                    <?php foreach (array('HD', 'FHD', '4K', 'CAM', 'TS', 'SD') as $opt): ?>
                        <option value="<?php e($opt); ?>" <?php echo $q === $opt ? 'selected' : ''; ?>><?php e($opt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php $s = isset($_POST['status']) ? $_POST['status'] : 'published'; ?>
                    <option value="published" <?php echo $s === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $s === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="checkbox-row">
                    <input type="checkbox" name="featured" id="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                    <label for="featured" style="margin:0;">Mark as Featured</label>
                </div>
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
            <label>Trailer URL (.m3u8 or YouTube)</label>
            <input type="text" name="trailer" value="<?php echo isset($_POST['trailer']) ? htmlspecialchars($_POST['trailer'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
        </div>

        <div class="form-group">
            <label>Stream URL (.m3u8) <span style="color:#469AFF;">*</span></label>
            <input type="text" name="stream_url" value="<?php echo isset($_POST['stream_url']) ? htmlspecialchars($_POST['stream_url'], ENT_QUOTES) : ''; ?>" placeholder="https://example.com/movie.m3u8">
            <div class="hint">HLS (.m3u8) format URL for the video player.</div>
        </div>

        <div class="form-group">
            <label>Download URL (optional)</label>
            <input type="text" name="download_url" value="<?php echo isset($_POST['download_url']) ? htmlspecialchars($_POST['download_url'], ENT_QUOTES) : ''; ?>" placeholder="https://...">
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save Movie</button>
            <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
