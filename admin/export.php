<?php
// BDMovieHub - Admin Export (download all data as single JSON)
require_once __DIR__ . '/../config.php';
$adminPage = 'export';
$pageTitle = 'Export Data';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    if (isset($_POST['download'])) {
        // Build full export
        $__expSettings = getSettings();
        $export = array(
            'generated_at' => date('Y-m-d H:i:s'),
            'site_url'     => isset($__expSettings['site_url']) ? $__expSettings['site_url'] : (defined('SITE_URL') ? SITE_URL : ''),
            'settings'     => getData(FILE_SETTINGS),
            'movies'       => getData(FILE_MOVIES),
            'anime'        => getData(FILE_ANIME),
            'episodes'     => getData(FILE_EPISODES),
            'pages'        => getData(FILE_PAGES),
            'schedule'     => getData(FILE_SCHEDULE),
            'users'        => getData(FILE_USERS),
            'categories'   => getData(FILE_CATEGORIES),
            'featured'     => getData(FILE_FEATURED),
            'comments'     => getData(FILE_COMMENTS),
            'slides'       => getData(FILE_SLIDES),
            'genres'       => getData(FILE_GENRES),
        );

        // Remove sensitive info (user passwords)
        foreach ($export['users'] as $i => $u) {
            unset($export['users'][$i]['password']);
        }

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'bdmoviehub-export-' . date('Y-m-d-His') . '.json';

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    // Download single file
    if (isset($_POST['file'])) {
        $fileKey = $_POST['file'];
        $fileMap = array(
            'settings'   => FILE_SETTINGS,
            'movies'     => FILE_MOVIES,
            'anime'      => FILE_ANIME,
            'episodes'   => FILE_EPISODES,
            'pages'      => FILE_PAGES,
            'schedule'   => FILE_SCHEDULE,
            'users'      => FILE_USERS,
            'categories' => FILE_CATEGORIES,
            'featured'   => FILE_FEATURED,
            'comments'   => FILE_COMMENTS,
            'slides'     => FILE_SLIDES,
            'genres'     => FILE_GENRES,
        );
        if (!isset($fileMap[$fileKey])) {
            setFlash('error', 'Invalid file.');
            adminRedirect('export.php');
        }
        $file = $fileMap[$fileKey];
        if (!file_exists($file)) {
            setFlash('error', 'File does not exist.');
            adminRedirect('export.php');
        }
        $json = file_get_contents($file);
        $filename = $fileKey . '-' . date('Y-m-d') . '.json';

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }
}

$files = array(
    'settings'   => 'Settings (site config, colors)',
    'movies'     => 'Movies catalog',
    'anime'      => 'Anime catalog',
    'episodes'   => 'Episodes list',
    'pages'      => 'Static pages',
    'schedule'   => 'Anime schedule',
    'users'      => 'Users (passwords excluded)',
    'categories' => 'Categories',
    'featured'   => 'Featured items',
    'comments'   => 'User comments',
    'slides'     => 'Hero slides',
    'genres'     => 'Genres list',
);

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Export Data</h2>
    <p style="color:#a0a0b8; margin-bottom:20px;">
        Download all your site data as JSON files. Useful for backup or migration.
    </p>

    <form method="POST" action="<?php e($adminUrl); ?>/export.php" style="display:inline-block; margin-bottom: 24px;">
        <?php echo csrfField(); ?>
        <input type="hidden" name="download" value="1">
        <button type="submit" class="btn-admin btn-admin-success">
            <i class="fas fa-download"></i> Download Full Backup (All Data)
        </button>
    </form>

    <h3 style="font-size:16px; margin-bottom:12px;">Or download individual files:</h3>
    <div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>File</th><th>Description</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($files as $key => $desc): ?>
            <tr>
                <td><code><?php e($key); ?>.json</code></td>
                <td><?php e($desc); ?></td>
                <td>
                    <form method="POST" action="<?php e($adminUrl); ?>/export.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="file" value="<?php e($key); ?>">
                        <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-download"></i> Download</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
