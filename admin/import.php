<?php
// BDMovieHub - Admin Import (bulk JSON import)
require_once __DIR__ . '/../config.php';
$adminPage = 'import';
$pageTitle = 'Bulk Import';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $importType = isset($_POST['import_type']) ? $_POST['import_type'] : '';
    $jsonData = isset($_POST['json_data']) ? trim($_POST['json_data']) : '';

    if ($importType === '' || !in_array($importType, array('movies', 'anime', 'episodes', 'pages', 'slides'))) {
        $error = 'Invalid import type.';
    } elseif ($jsonData === '') {
        $error = 'JSON data is required.';
    } else {
        $decoded = json_decode($jsonData, true);
        if (!is_array($decoded)) {
            $error = 'Invalid JSON. Could not decode.';
        } else {
            // Map import type to file constant
            $fileMap = array(
                'movies'   => FILE_MOVIES,
                'anime'    => FILE_ANIME,
                'episodes' => FILE_EPISODES,
                'pages'    => FILE_PAGES,
                'slides'   => FILE_SLIDES,
            );
            $file = $fileMap[$importType];
            $existing = getData($file);

            $added = 0;
            $skipped = 0;
            foreach ($decoded as $item) {
                if (!is_array($item)) { $skipped++; continue; }

                // ---------- Minimum schema validation ----------
                // movies + anime MUST have a non-empty title (slug is auto-generated if missing)
                // episodes MUST have an anime_id
                // pages MUST have a title
                if (in_array($importType, array('movies', 'anime', 'pages'), true)) {
                    $title = isset($item['title']) ? trim((string)$item['title']) : '';
                    if ($title === '') { $skipped++; continue; }
                    $item['title'] = $title;
                }
                if ($importType === 'episodes') {
                    $aId = isset($item['anime_id']) ? trim((string)$item['anime_id']) : '';
                    if ($aId === '') { $skipped++; continue; }
                    $item['anime_id'] = $aId;
                }

                // Check for required slug field for movies/anime/pages
                if (in_array($importType, array('movies', 'anime', 'pages'))) {
                    $slug = isset($item['slug']) ? $item['slug'] : '';
                    if ($slug === '') {
                        $title = isset($item['title']) ? $item['title'] : '';
                        $slug = slugify($title);
                        $item['slug'] = $slug;
                    }
                    // Ensure uniqueness
                    $exists = false;
                    foreach ($existing as $e) {
                        if (isset($e['slug']) && $e['slug'] === $slug) { $exists = true; break; }
                    }
                    if ($exists) {
                        $item['slug'] = $slug . '-' . substr(md5(time() . $added), 0, 4);
                    }
                }
                // Ensure ID
                if (!isset($item['id']) || $item['id'] === '') {
                    $prefixMap = array('movies' => 'm', 'anime' => 'a', 'episodes' => 'ep', 'pages' => 'pg', 'slides' => 'sl');
                    $item['id'] = generateId($existing, $prefixMap[$importType]);
                }
                if (!isset($item['created_at'])) { $item['created_at'] = date('Y-m-d'); }
                // For movies/anime, default to 'published' status so imported items are visible
                if (in_array($importType, array('movies', 'anime'), true)) {
                    $statusKey = ($importType === 'anime') ? 'status_pub' : 'status';
                    if (!isset($item[$statusKey]) || $item[$statusKey] === '') {
                        $item[$statusKey] = 'published';
                    }
                }
                $existing[] = $item;
                $added++;
            }

            if (saveData($file, $existing)) {
                $message = "Imported {$added} items successfully. Skipped: {$skipped}.";
                setFlash('success', $message);
            } else {
                $error = 'Failed to save imported data.';
            }
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Bulk Import</h2>
    <p style="color:#a0a0b8; margin-bottom:20px;">
        Paste a JSON array of items to import them into the database. The importer will skip duplicate slugs
        and auto-generate missing IDs.
    </p>

    <?php if ($error): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <?php e($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div style="background:rgba(46,204,113,0.1); border:1px solid #2ecc71; color:#2ecc71; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <?php e($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php e($adminUrl); ?>/import.php">
        <?php echo csrfField(); ?>
        <div class="form-group">
            <label>Import Type</label>
            <select name="import_type">
                <option value="movies">Movies</option>
                <option value="anime">Anime</option>
                <option value="episodes">Episodes</option>
                <option value="pages">Pages</option>
                <option value="slides">Hero Slides</option>
            </select>
        </div>
        <div class="form-group">
            <label>JSON Data</label>
            <textarea name="json_data" rows="14" style="font-family: monospace; font-size:12px;" placeholder='[{"title":"Example","slug":"example","poster":"https://...","stream_url":"https://...","status":"published"}]'><?php echo isset($_POST['json_data']) ? htmlspecialchars($_POST['json_data'], ENT_QUOTES) : ''; ?></textarea>
            <div class="hint">Paste a JSON array. Each item must be a JSON object. Required fields vary by type.</div>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-file-import"></i> Import</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="font-size:16px; margin-bottom:10px;">Example JSON (Movies)</h3>
    <pre style="background:#0a0a0f; padding:14px; border-radius:6px; overflow-x:auto; color:#a0a0b8; font-size:11px;">[
  {
    "title": "Example Movie",
    "slug": "example-movie",
    "poster": "https://example.com/poster.jpg",
    "banner": "https://example.com/banner.jpg",
    "description": "Movie description here.",
    "year": "2024",
    "rating": "8.5",
    "duration": "2h 15m",
    "quality": "HD",
    "status": "published",
    "genre": ["Action", "Drama"],
    "stream_url": "https://example.com/movie.m3u8",
    "download_url": "",
    "trailer": ""
  }
]</pre>
</div>

<?php include __DIR__ . '/footer.php'; ?>
