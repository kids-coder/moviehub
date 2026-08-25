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
    $kind         = isset($_POST['kind']) && in_array($_POST['kind'], array('movie', 'series'), true) ? $_POST['kind'] : 'movie';
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

    // Duplicate-title detection (same normalized title)
    $__normTitle = strtolower(preg_replace('/\s+/', ' ', $title));
    foreach (getData(FILE_MOVIES) as $__m) {
        if (strtolower(preg_replace('/\s+/', ' ', isset($__m['title']) ? $__m['title'] : '')) === $__normTitle) {
            $errors[] = 'A movie with this title already exists: "' . (isset($__m['title']) ? $__m['title'] : '') . '".';
            break;
        }
    }

    if (empty($errors)) {
        $movies = getData(FILE_MOVIES);
        // Check slug uniqueness
        if (getBySlug($movies, $slug)) {
            $slug = $slug . '-' . substr(md5(time()), 0, 4);
        }
        $newMovie = array(
            'id'           => generateId($movies, 'm'),
            'title'        => $title,
            'alternate_title' => $altTitle,
            'kind'         => $kind,
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
            'alt_sources'  => $alt_sources,
            'subtitle_tracks' => $subtitle_tracks,
            'featured'     => $featured,
            'created_at'   => date('Y-m-d'),
            'country'      => $country,
            'language'     => $language,
            'cast'         => $cast,
            'director'     => $director,
            'subtitles'    => $subtitles,
            'legal_providers' => $providers,
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
                <label>Alternate Title (Bangla/English)</label>
                <input type="text" name="alternate_title" value="<?php echo isset($_POST['alternate_title']) ? htmlspecialchars($_POST['alternate_title'], ENT_QUOTES) : ''; ?>" placeholder="বাংলা শিরোনাম বা English alias">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Kind</label>
                <select name="kind">
                    <?php $k = isset($_POST['kind']) ? $_POST['kind'] : 'movie'; ?>
                    <option value="movie" <?php echo $k === 'movie' ? 'selected' : ''; ?>>Movie</option>
                    <option value="series" <?php echo $k === 'series' ? 'selected' : ''; ?>>Series</option>
                </select>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country'], ENT_QUOTES) : ''; ?>" placeholder="Bangladesh, India, USA">
            </div>
            <div class="form-group">
                <label>Language</label>
                <input type="text" name="language" value="<?php echo isset($_POST['language']) ? htmlspecialchars($_POST['language'], ENT_QUOTES) : ''; ?>" placeholder="Bangla, Hindi, English">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Cast</label>
                <input type="text" name="cast" value="<?php echo isset($_POST['cast']) ? htmlspecialchars($_POST['cast'], ENT_QUOTES) : ''; ?>" placeholder="Actor 1, Actor 2">
            </div>
            <div class="form-group">
                <label>Director</label>
                <input type="text" name="director" value="<?php echo isset($_POST['director']) ? htmlspecialchars($_POST['director'], ENT_QUOTES) : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Subtitles</label>
                <input type="text" name="subtitles" value="<?php echo isset($_POST['subtitles']) ? htmlspecialchars($_POST['subtitles'], ENT_QUOTES) : ''; ?>" placeholder="Bangla, English">
            </div>
            <div class="form-group">
                <label>Legal Watch Providers</label>
                <input type="text" name="legal_providers" value="<?php echo isset($_POST['legal_providers']) ? htmlspecialchars($_POST['legal_providers'], ENT_QUOTES) : ''; ?>" placeholder="Netflix, Chorki, Hoichoi">
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
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save Movie</button>
            <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
