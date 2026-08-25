<?php
// BDMovieHub - Diagnostics Page
// Visit this page to check if your site setup is working correctly.
// URL: https://yoursite.com/diagnostics.php
// After debugging, DELETE this file for security.

require_once __DIR__ . '/config.php';

// Run all checks
$checks = array();

// 1. PHP version
$phpVersion = phpversion();
$checks['php_version'] = array(
    'label' => 'PHP Version',
    'value' => $phpVersion,
    'ok' => version_compare($phpVersion, '7.0.0', '>='),
    'message' => version_compare($phpVersion, '7.0.0', '>=') ? 'Supported' : 'PHP 7.0+ required, you have ' . $phpVersion,
);

// 2. Required PHP extensions
$requiredExts = array('json', 'mbstring', 'session', 'ctype', 'pcre');
$missingExts = array();
foreach ($requiredExts as $ext) {
    if (!extension_loaded($ext)) { $missingExts[] = $ext; }
}
$checks['extensions'] = array(
    'label' => 'Required PHP Extensions',
    'value' => implode(', ', $requiredExts),
    'ok' => empty($missingExts),
    'message' => empty($missingExts) ? 'All required extensions loaded' : 'Missing: ' . implode(', ', $missingExts),
);

// 3. Data directory writable
$dataDirWritable = is_dir(DATA_DIR) && is_writable(DATA_DIR);
$checks['data_dir'] = array(
    'label' => 'Data Directory Writable',
    'value' => DATA_DIR,
    'ok' => $dataDirWritable,
    'message' => $dataDirWritable ? 'Writable - JSON files can be created' : 'NOT writable - cannot save data. CHMOD to 755 or 777.',
);

// 4. All JSON data files exist
$expectedFiles = array(
    'settings.json', 'movies.json', 'anime.json', 'episodes.json',
    'pages.json', 'schedule.json', 'users.json', 'categories.json',
    'featured.json', 'comments.json', 'slides.json', 'genres.json',
);
$missingFiles = array();
foreach ($expectedFiles as $f) {
    $path = DATA_DIR . '/' . $f;
    if (!file_exists($path)) {
        $missingFiles[] = $f;
    }
}
$checks['data_files'] = array(
    'label' => 'JSON Data Files Created',
    'value' => count($expectedFiles) - count($missingFiles) . ' / ' . count($expectedFiles),
    'ok' => empty($missingFiles),
    'message' => empty($missingFiles) ? 'All data files exist' : 'Missing: ' . implode(', ', $missingFiles),
);

// 5. Default admin user exists
$users = getData(FILE_USERS);
$hasAdmin = false;
foreach ($users as $u) {
    if (isset($u['username']) && $u['username'] === 'admin') {
        $hasAdmin = true;
        break;
    }
}
$checks['admin_user'] = array(
    'label' => 'Admin User Created',
    'value' => count($users) . ' user(s)',
    'ok' => $hasAdmin,
    'message' => $hasAdmin ? 'Admin user account is configured.' : 'Admin user missing - data directory may not be writable',
);

// 6. Sessions working
$sessionWorking = isset($_SESSION);
$checks['sessions'] = array(
    'label' => 'PHP Sessions Working',
    'value' => $sessionWorking ? 'Active' : 'Failed',
    'ok' => $sessionWorking,
    'message' => $sessionWorking ? 'Session support available' : 'Session support not available - admin login will not work',
);

// 7. Session save path writable
$sessPath = session_save_path();
if (empty($sessPath)) { $sessPath = sys_get_temp_dir(); }
$sessWritable = is_writable($sessPath);
$checks['session_path'] = array(
    'label' => 'Session Save Path Writable',
    'value' => $sessPath,
    'ok' => $sessWritable,
    'message' => $sessWritable ? 'Writable - sessions will work' : 'NOT writable - may cause login issues',
);

// 8. CSS files exist
$cssFiles = array(
    'assets/css/style.css',
    'assets/css/anime.css',
    'assets/css/player.css',
);
$missingCss = array();
foreach ($cssFiles as $f) {
    if (!file_exists(__DIR__ . '/' . $f) || filesize(__DIR__ . '/' . $f) === 0) {
        $missingCss[] = $f;
    }
}
$checks['css_files'] = array(
    'label' => 'CSS Files Present',
    'value' => count($cssFiles) - count($missingCss) . ' / ' . count($cssFiles),
    'ok' => empty($missingCss),
    'message' => empty($missingCss) ? 'All CSS files exist and have content' : 'Missing or empty: ' . implode(', ', $missingCss),
);

// 9. JS files exist
$jsFiles = array(
    'assets/js/features.js',
    'assets/js/player.js',
    'assets/js/ui.js',
);
$missingJs = array();
foreach ($jsFiles as $f) {
    if (!file_exists(__DIR__ . '/' . $f) || filesize(__DIR__ . '/' . $f) === 0) {
        $missingJs[] = $f;
    }
}
$checks['js_files'] = array(
    'label' => 'JS Files Present',
    'value' => count($jsFiles) - count($missingJs) . ' / ' . count($jsFiles),
    'ok' => empty($missingJs),
    'message' => empty($missingJs) ? 'All JS files exist and have content' : 'Missing or empty: ' . implode(', ', $missingJs),
);

// 10. BASE_URL detection
$checks['base_url'] = array(
    'label' => 'BASE_URL Detected',
    'value' => BASE_URL,
    'ok' => true,
    'message' => 'Site will be served from: ' . BASE_URL . '/',
);

// 11. PHP short tags check
$shortTagFound = false;
$shortTagFiles = array();
$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($phpFiles as $file) {
    if ($file->getExtension() !== 'php') { continue; }
    if (strpos($file->getPathname(), 'diagnostics.php') !== false) { continue; }
    $content = @file_get_contents($file->getPathname());
    // Search for the short open echo tag (written as concat to avoid phply parser confusion)
    $shortTag = '<' . '?=';
    if (strpos($content, $shortTag) !== false) {
        $shortTagFound = true;
        $shortTagFiles[] = str_replace(__DIR__ . '/', '', $file->getPathname());
    }
}
$checks['short_tags'] = array(
    'label' => 'PHP Short Tags',
    'value' => $shortTagFound ? 'Found!' : 'None',
    'ok' => !$shortTagFound,
    'message' => $shortTagFound ? 'PROBLEM: Short tags found in: ' . implode(', ', $shortTagFiles) : 'No short tags - InfinityFree-safe',
);

// 12. JSON files content sample
$sampleJson = array();
foreach (array('movies.json', 'anime.json', 'slides.json') as $jf) {
    $path = DATA_DIR . '/' . $jf;
    if (file_exists($path)) {
        $data = json_decode(@file_get_contents($path), true);
        $count = is_array($data) ? count($data) : 'INVALID JSON';
        $sampleJson[$jf] = $count;
    } else {
        $sampleJson[$jf] = 'MISSING';
    }
}

// 13. Sample data installed?
$movieCount = count(getData(FILE_MOVIES));
$animeCount = count(getData(FILE_ANIME));
$checks['sample_data'] = array(
    'label' => 'Sample Data Installed',
    'value' => $movieCount . ' movies, ' . $animeCount . ' anime',
    'ok' => $movieCount > 0 || $animeCount > 0,
    'message' => ($movieCount > 0 || $animeCount > 0)
        ? 'Content found'
        : 'No content yet - run setup.php to install sample data',
);

// 14. Server info
$serverInfo = array(
    'PHP Version' => phpversion(),
    'Server Software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
    'Document Root' => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'Unknown',
    'Script Location' => __DIR__,
    'BASE_URL' => BASE_URL,
    'ASSETS_URL' => ASSETS_URL,
    'Current URL' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''),
);

// Calculate overall status
$allOk = true;
foreach ($checks as $c) {
    if (!$c['ok']) { $allOk = false; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostics - BDMovieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #fff;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .header h1 span { color: #469AFF; }
        .header p { color: #a0a0b8; }
        .status-banner {
            background: <?php echo $allOk ? 'rgba(46,204,113,0.1)' : 'rgba(231,76,60,0.1)'; ?>;
            border: 1px solid <?php echo $allOk ? '#2ecc71' : '#e74c3c'; ?>;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            margin-bottom: 24px;
            font-size: 16px;
            font-weight: 600;
        }
        .status-banner.success { color: #2ecc71; }
        .status-banner.error { color: #e74c3c; }
        .check-list {
            display: grid;
            gap: 12px;
            margin-bottom: 30px;
        }
        .check-item {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .check-item.failed {
            border-color: #e74c3c;
            background: rgba(231, 76, 60, 0.05);
        }
        .check-item.passed {
            border-color: rgba(46, 204, 113, 0.3);
        }
        .check-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }
        .check-icon.ok { background: rgba(46,204,113,0.15); color: #2ecc71; }
        .check-icon.fail { background: rgba(231,76,60,0.15); color: #e74c3c; }
        .check-content { flex: 1; min-width: 0; }
        .check-label {
            font-weight: 600;
            margin-bottom: 2px;
        }
        .check-value {
            font-size: 13px;
            color: #a0a0b8;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin-bottom: 4px;
        }
        .check-message {
            font-size: 13px;
            color: #a0a0b8;
        }
        .check-item.failed .check-message { color: #e74c3c; }
        .info-card {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-card h2 {
            font-size: 18px;
            margin-bottom: 14px;
            color: #469AFF;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 8px 16px;
            font-size: 13px;
        }
        .info-grid .key { color: #a0a0b8; font-family: 'Courier New', monospace; }
        .info-grid .val { color: #fff; font-family: 'Courier New', monospace; word-break: break-all; }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-primary { background: #469AFF; color: #fff; }
        .btn-primary:hover { background: #2d7dd2; }
        .btn-success { background: #2ecc71; color: #fff; }
        .btn-success:hover { background: #27ae60; }
        .btn-outline { background: transparent; color: #fff; border: 1px solid #2a2a3e; }
        .btn-outline:hover { border-color: #469AFF; }
        .warning {
            background: rgba(255,165,2,0.1);
            border: 1px solid #ffa502;
            color: #ffa502;
            padding: 14px 18px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BD<span>Movie</span>Hub Diagnostics</h1>
            <p>System health check &amp; troubleshooting</p>
        </div>

        <div class="status-banner <?php echo $allOk ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $allOk ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php if ($allOk): ?>
                All checks passed! Your site should be working.
            <?php else: ?>
                Some checks failed. Review the issues below.
            <?php endif; ?>
        </div>

        <div class="check-list">
            <?php foreach ($checks as $key => $c): ?>
                <div class="check-item <?php echo $c['ok'] ? 'passed' : 'failed'; ?>">
                    <div class="check-icon <?php echo $c['ok'] ? 'ok' : 'fail'; ?>">
                        <i class="fas fa-<?php echo $c['ok'] ? 'check' : 'times'; ?>"></i>
                    </div>
                    <div class="check-content">
                        <div class="check-label"><?php e($c['label']); ?></div>
                        <div class="check-value"><?php e($c['value']); ?></div>
                        <div class="check-message"><?php e($c['message']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="info-card">
            <h2><i class="fas fa-server"></i> Server Information</h2>
            <div class="info-grid">
                <?php foreach ($serverInfo as $k => $v): ?>
                    <div class="key"><?php e($k); ?>:</div>
                    <div class="val"><?php e($v); ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-card">
            <h2><i class="fas fa-database"></i> JSON File Contents</h2>
            <div class="info-grid">
                <?php foreach ($sampleJson as $f => $count): ?>
                    <div class="key"><?php e($f); ?>:</div>
                    <div class="val"><?php echo is_int($count) ? $count . ' records' : $count; ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="actions">
            <a href="<?php e(BASE_URL); ?>/index.php" class="btn btn-primary"><i class="fas fa-home"></i> Visit Homepage</a>
            <a href="<?php e(BASE_URL); ?>/setup.php" class="btn btn-success"><i class="fas fa-magic"></i> Install Sample Data</a>
            <a href="<?php e(BASE_URL); ?>/admin/login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
        </div>

        <div class="warning">
            <strong><i class="fas fa-exclamation-triangle"></i> Security Notice:</strong>
            This diagnostics page exposes server information. <strong>Delete diagnostics.php</strong> from your server after you're done debugging.
        </div>

        <div style="margin-top: 24px; padding: 16px; background: #1a1a2e; border-radius: 10px; font-size: 13px; color: #a0a0b8;">
            <strong style="color:#fff;">Troubleshooting Tips:</strong>
            <ul style="margin: 8px 0 0 20px; line-height: 1.8;">
                <li>If "Data Directory Writable" failed: CHMOD the <code>data/</code> folder to 755 or 777 via FTP</li>
                <li>If "JSON Data Files Created" failed: visit <a href="<?php e(BASE_URL); ?>/setup.php" style="color:#469AFF;">setup.php</a> or check data folder permissions</li>
                <li>If "Admin User Created" failed: the data folder is not writable; the users.json file could not be created</li>
                <li>If CSS is not loading: verify the <code>assets/css/</code> folder was uploaded with all 3 CSS files</li>
                <li>If pages show blank: enable DEBUG_MODE in config.php (already on by default) to see PHP errors</li>
            </ul>
        </div>
    </div>
</body>
</html>
