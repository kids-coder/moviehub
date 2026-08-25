<?php
// BDMovieHub - debug.php
// Shows server info, PHP config, and any errors that may be blocking the site.
// Visit: https://yoursite.com/debug.php
// Delete this file once your site is working.

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');

// Try to load the site config - this will report any errors
$configError = '';
$configLoaded = false;
try {
    require_once __DIR__ . '/config.php';
    $configLoaded = true;
} catch (Throwable $e) {
    $configError = $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine();
}

// Gather server info
$phpVersion = phpversion();
$loadedExts = get_loaded_extensions();
$requiredExts = array('json', 'mbstring', 'session', 'ctype', 'pcre');
$missingExts = array();
foreach ($requiredExts as $e) {
    if (!extension_loaded($e)) { $missingExts[] = $e; }
}

// File existence checks
$requiredFiles = array(
    'config.php', 'functions.php', 'bootstrap.php',
    'header.php', 'footer.php', 'index.php',
    'assets/css/style.css', 'assets/css/anime.css', 'assets/css/player.css',
    'assets/js/ui.js', 'assets/js/features.js', 'assets/js/player.js',
    '.htaccess', 'data/.htaccess',
);

// Data directory
$dataDir = __DIR__ . '/data';
$dataDirExists = is_dir($dataDir);
$dataDirWritable = $dataDirExists && is_writable($dataDir);

// JSON files
$jsonFiles = array();
if ($dataDirExists) {
    foreach (glob($dataDir . '/*.json') as $jf) {
        $jsonFiles[] = basename($jf) . ' (' . filesize($jf) . ' bytes)';
    }
}

// Error log
$errorLog = $dataDir . '/php-error.log';
$errorLogContent = '';
if (file_exists($errorLog)) {
    $errorLogContent = file_get_contents($errorLog);
    if (strlen($errorLogContent) > 5000) {
        $errorLogContent = "...(truncated)...\n" . substr($errorLogContent, -5000);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - BDMovieHub</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #0a0a0f;
            color: #fff;
            padding: 30px;
            line-height: 1.6;
            margin: 0;
        }
        h1 { color: #469AFF; margin-top: 0; }
        h2 { color: #9b59b6; margin-top: 32px; border-bottom: 1px solid #2a2a3e; padding-bottom: 8px; }
        .box {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .ok { color: #2ecc71; font-weight: 600; }
        .bad { color: #e74c3c; font-weight: 600; }
        .warn { color: #ffa502; font-weight: 600; }
        pre, code {
            background: #0a0a0f;
            padding: 12px 16px;
            border-radius: 6px;
            display: block;
            overflow-x: auto;
            color: #ffa502;
            font-family: 'Courier New', Consolas, monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #2a2a3e; font-size: 13px; vertical-align: top; }
        th { color: #a0a0b8; }
        a { color: #469AFF; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>BDMovieHub Debug Page</h1>

    <h2>1. PHP Configuration</h2>
    <div class="box">
        <table>
            <tr><th>PHP Version</th><td><span class="<?php echo version_compare($phpVersion, '7.0.0', '>=') ? 'ok' : 'bad'; ?>"><?php echo htmlspecialchars($phpVersion); ?></span> (requires 7.0+)</td></tr>
            <tr><th>Server Software</th><td><?php echo htmlspecialchars(isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown'); ?></td></tr>
            <tr><th>Document Root</th><td><?php echo htmlspecialchars(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'Unknown'); ?></td></tr>
            <tr><th>Script Name</th><td><?php echo htmlspecialchars(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'Unknown'); ?></td></tr>
            <tr><th>Request URI</th><td><?php echo htmlspecialchars(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown'); ?></td></tr>
            <tr><th>display_errors</th><td><?php echo htmlspecialchars(ini_get('display_errors')); ?></td></tr>
            <tr><th>error_reporting</th><td><?php echo htmlspecialchars(error_reporting()); ?></td></tr>
            <tr><th>memory_limit</th><td><?php echo htmlspecialchars(ini_get('memory_limit')); ?></td></tr>
            <tr><th>max_execution_time</th><td><?php echo htmlspecialchars(ini_get('max_execution_time')); ?> sec</td></tr>
            <tr><th>upload_max_filesize</th><td><?php echo htmlspecialchars(ini_get('upload_max_filesize')); ?></td></tr>
        </table>
    </div>

    <h2>2. Required PHP Extensions</h2>
    <div class="box">
        <?php if (empty($missingExts)): ?>
            <p class="ok">&#10003; All required extensions loaded.</p>
        <?php else: ?>
            <p class="bad">&#10007; MISSING: <?php echo htmlspecialchars(implode(', ', $missingExts)); ?></p>
        <?php endif; ?>
        <p style="color:#a0a0b8; font-size:13px; margin-top:8px;">Loaded: <?php echo htmlspecialchars(implode(', ', $loadedExts)); ?></p>
    </div>

    <h2>3. Site Config Load Test</h2>
    <div class="box">
        <?php if ($configLoaded): ?>
            <p class="ok">&#10003; config.php loaded successfully!</p>
            <p>BASE_URL = "<code><?php echo htmlspecialchars(defined('BASE_URL') ? BASE_URL : '(undefined)'); ?></code>"</p>
            <p>ASSETS_URL = "<code><?php echo htmlspecialchars(defined('ASSETS_URL') ? ASSETS_URL : '(undefined)'); ?></code>"</p>
            <p>DATA_DIR = "<code><?php echo htmlspecialchars(defined('DATA_DIR') ? DATA_DIR : '(undefined)'); ?></code>"</p>
            <p>DEBUG_MODE = <?php echo htmlspecialchars(defined('DEBUG_MODE') && DEBUG_MODE ? 'true' : 'false'); ?></p>
        <?php else: ?>
            <p class="bad">&#10007; Failed to load config.php!</p>
            <pre><?php echo htmlspecialchars($configError); ?></pre>
        <?php endif; ?>
    </div>

    <h2>4. Required Files Exist</h2>
    <div class="box">
        <table>
            <thead><tr><th>File</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($requiredFiles as $rf): ?>
                <?php $exists = file_exists(__DIR__ . '/' . $rf); ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($rf); ?></code></td>
                    <td><span class="<?php echo $exists ? 'ok' : 'bad'; ?>"><?php echo $exists ? '&#10003; exists' : '&#10007; MISSING'; ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2>5. Data Directory</h2>
    <div class="box">
        <table>
            <tr><th>Path</th><td><code><?php echo htmlspecialchars($dataDir); ?></code></td></tr>
            <tr><th>Exists</th><td><span class="<?php echo $dataDirExists ? 'ok' : 'bad'; ?>"><?php echo $dataDirExists ? '&#10003; Yes' : '&#10007; NO'; ?></span></td></tr>
            <tr><th>Writable</th><td><span class="<?php echo $dataDirWritable ? 'ok' : 'bad'; ?>"><?php echo $dataDirWritable ? '&#10003; Yes' : '&#10007; NO (CHMOD 755 or 777)'; ?></span></td></tr>
        </table>
        <?php if (!empty($jsonFiles)): ?>
            <p style="margin-top:14px;"><strong>JSON files found:</strong></p>
            <ul style="color:#a0a0b8; font-size:13px;">
                <?php foreach ($jsonFiles as $jf): ?>
                    <li><code><?php echo htmlspecialchars($jf); ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="margin-top:14px;" class="warn">No JSON files yet. Visit the homepage once to auto-create them.</p>
        <?php endif; ?>
    </div>

    <?php if ($errorLogContent): ?>
    <h2>6. Recent PHP Errors (from data/php-error.log)</h2>
    <div class="box">
        <pre><?php echo htmlspecialchars($errorLogContent); ?></pre>
    </div>
    <?php endif; ?>

    <h2>7. What to do next</h2>
    <div class="box">
        <p>If config.php failed to load, the error message above shows exactly what's wrong.</p>
        <p>If a required file is missing, re-upload it from the ZIP.</p>
        <p>If the data directory isn't writable, CHMOD it to 755 or 777 via your FTP client.</p>
        <p>If everything looks OK here but the homepage still doesn't load, the issue is likely:</p>
        <ul style="color:#a0a0b8; font-size:14px; padding-left:20px;">
            <li>A file got corrupted during FTP upload (re-upload as binary, not ASCII)</li>
            <li>Your hosting has a CPU/memory limit that's being hit</li>
            <li>Your hosting's anti-bot system is blocking the home page (try a different browser / clear cookies)</li>
        </ul>
    </div>

    <p style="margin-top:30px;">
        &rarr; <a href="index.php">Try homepage</a> |
        &rarr; <a href="test.php">PHP test page</a> |
        &rarr; <a href="diagnostics.php">Diagnostics</a> |
        &rarr; <a href="admin/login.php">Admin login</a>
    </p>

    <p style="margin-top:30px; color:#6b6b80; font-size:12px;">
        Once your site is working, <strong>delete this file</strong> (debug.php) for security.
    </p>
</body>
</html>
