<?php
// BDMovieHub - test.php
// A MINIMAL PHP page to verify that PHP is working on this host.
// Visit: https://yoursite.com/test.php
// If this page loads, PHP is working. If it doesn't load, the problem is with the host, not the code.
// Delete this file once your site is working.

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');

$phpVersion = phpversion();
$time = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Test - BDMovieHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #0a0a0f;
            color: #fff;
            padding: 40px;
            line-height: 1.7;
            max-width: 900px;
            margin: 0 auto;
        }
        h1 { color: #469AFF; margin-top: 0; }
        h2 { color: #9b59b6; margin-top: 32px; border-bottom: 1px solid #2a2a3e; padding-bottom: 8px; }
        .box {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .ok { color: #2ecc71; }
        .bad { color: #e74c3c; }
        .warn { color: #ffa502; }
        code, pre {
            background: #0a0a0f;
            padding: 12px 16px;
            border-radius: 6px;
            display: block;
            overflow-x: auto;
            color: #ffa502;
            font-family: 'Courier New', Consolas, monospace;
        }
        a { color: #469AFF; }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #2a2a3e;
        }
        .step:last-child { border-bottom: none; }
        .step-num {
            background: #469AFF;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
            font-size: 14px;
        }
        .step-content { flex: 1; }
        .step-content strong { color: #fff; }
        .step-content p { color: #a0a0b8; margin: 4px 0 0 0; font-size: 14px; }
    </style>
</head>
<body>
    <h1>&#9989; PHP is working!</h1>

    <div class="box">
        <p><strong>PHP Version:</strong> <span class="ok"><?php echo htmlspecialchars($phpVersion); ?></span></p>
        <p><strong>Server Time:</strong> <?php echo htmlspecialchars($time); ?></p>
        <p><strong>Server Software:</strong> <?php echo htmlspecialchars(isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown'); ?></p>
        <p><strong>Document Root:</strong> <?php echo htmlspecialchars(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'Unknown'); ?></p>
        <p><strong>Script Path:</strong> <?php echo htmlspecialchars(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'Unknown'); ?></p>
    </div>

    <h2>If your home page doesn't load but this page does...</h2>
    <p>PHP itself is fine. The problem is in one of the BDMovieHub PHP files. Follow these steps:</p>

    <div class="box">
        <div class="step">
            <span class="step-num">1</span>
            <div class="step-content">
                <strong>Visit <a href="diagnostics.php">diagnostics.php</a></strong>
                <p>This runs a full check of file permissions, data directory, sessions, etc.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">2</span>
            <div class="step-content">
                <strong>Check file permissions</strong>
                <p>Make sure all files are uploaded with permission 644 (or 755 for folders). The <code>data/</code> folder must be writable (CHMOD 755 or 777).</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">3</span>
            <div class="step-content">
                <strong>Make sure all files are uploaded</strong>
                <p>The site requires: <code>config.php</code>, <code>functions.php</code>, <code>bootstrap.php</code>, <code>header.php</code>, <code>footer.php</code>, <code>index.php</code>, and the <code>assets/</code> folder.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">4</span>
            <div class="step-content">
                <strong>Check error log</strong>
                <p>Errors are logged to <code>data/php-error.log</code>. Download and open this file to see what's failing.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">5</span>
            <div class="step-content">
                <strong>Try re-uploading</strong>
                <p>Sometimes files get corrupted during FTP upload. Re-upload the ZIP and extract again, replacing all files.</p>
            </div>
        </div>
    </div>

    <h2>Quick test links</h2>
    <div class="box">
        <p>&rarr; <a href="index.php">Try the homepage</a></p>
        <p>&rarr; <a href="diagnostics.php">Run diagnostics</a></p>
        <p>&rarr; <a href="admin/login.php">Admin login</a></p>
    </div>

    <p style="margin-top:40px; color:#6b6b80; font-size:12px;">
        Once your site is working, <strong>delete this file</strong> (test.php) for security.
    </p>
</body>
</html>
