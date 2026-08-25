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
    $kind         = isset($_POST['kind']) && in_array($_POST['kind'], array('movie', 'series'), true) ? $_POST['kind'] : (isset($movie['kind']) ? $movie['kind'] : 'movie');
    $altTitle     = isset($_POST['alternate_title']) ? trim($_POST['alternate_title']) : '';
    $country      = isset($_POST['country']) ? trim($_POST['country']) : '';
    $language     = isset($_POST['language']) ? trim($_POST['language']) : '';
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
    $cast         = isset($_POST['cast']) ? trim($_POST['cast']) : '';
    $director     = isset($_POST['director']) ? trim($_POST['director']) : '';
    $subtitles    = isset($_POST['subtitles']) ? trim($_POST['subtitles']) : '';
    $providers    = isset($_POST['legal_providers']) ? trim($_POST['legal_providers']) : '';

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
                $mm['alternate_title'] = $altTitle;
                $mm['kind']         = $kind;
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
                $mm['alt_sources'] = $alt_sources;
                $mm['subtitle_tracks'] = $subtitle_tracks;
                $mm['featured']     = $featured;
                $mm['country']      = $country;
                $mm['language']     = $language;
                $mm['cast']         = $cast;
                $mm['director']     = $director;
                $mm['subtitles']    = $subtitles;
                $mm['legal_providers'] = $providers;
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
                <label>Alternate Title (Bangla/English)</label>
                <input type="text" name="alternate_title" value="<?php e(isset($movie['alternate_title']) ? $movie['alternate_title'] : ''); ?>">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Kind</label>
                <select name="kind">
                    <?php $k = isset($movie['kind']) ? $movie['kind'] : 'movie'; ?>
                    <option value="movie" <?php echo $k === 'movie' ? 'selected' : ''; ?>>Movie</option>
                    <option value="series" <?php echo $k === 'series' ? 'selected' : ''; ?>>Series</option>
                </select>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" value="<?php e(isset($movie['country']) ? $movie['country'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Language</label>
                <input type="text" name="language" value="<?php e(isset($movie['language']) ? $movie['language'] : ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Cast</label>
                <input type="text" name="cast" value="<?php e(isset($movie['cast']) ? $movie['cast'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Director</label>
                <input type="text" name="director" value="<?php e(isset($movie['director']) ? $movie['director'] : ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Subtitles</label>
                <input type="text" name="subtitles" value="<?php e(isset($movie['subtitles']) ? $movie['subtitles'] : ''); ?>" placeholder="Bangla, English">
            </div>
            <div class="form-group">
                <label>Legal Watch Providers</label>
                <input type="text" name="legal_providers" value="<?php e(isset($movie['legal_providers']) ? $movie['legal_providers'] : ''); ?>" placeholder="Netflix, Chorki, Hoichoi">
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

        <div class="form-group">
            <label>Backup Stream URLs (one per line, optional)</label>
            <textarea name="alt_sources" rows="3"><?php echo htmlspecialchars(implode("\n", isset($movie['alt_sources']) && is_array($movie['alt_sources']) ? $movie['alt_sources'] : array()), ENT_QUOTES); ?></textarea>
            <small style="color:#8b8b9e;">Used automatically if the main stream fails.</small>
        </div>

        <div class="form-group">
            <label>Subtitle Tracks (one per line: lang|label|url)</label>
            <?php $__stLines = array(); foreach ((isset($movie['subtitle_tracks']) && is_array($movie['subtitle_tracks']) ? $movie['subtitle_tracks'] : array()) as $__t) { $__stLines[] = $__t['lang'] . '|' . $__t['label'] . '|' . $__t['src']; } ?>
            <textarea name="subtitle_tracks" rows="3" placeholder="en|English|https://example.com/en.vtt"><?php echo htmlspecialchars(implode("\n", $__stLines), ENT_QUOTES); ?></textarea>
            <small style="color:#8b8b9e;">WebVTT files. Example line: en|English|https://site.com/sub.vtt</small>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Update Movie</button>
            <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
