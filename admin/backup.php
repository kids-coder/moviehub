<?php
// BDMovieHub - Admin Backup / Restore
require_once __DIR__ . '/../config.php';
requireAdmin(); // Admin-only: full data access
$adminPage = 'backup';
$pageTitle = 'Backup & Restore';

$message = '';
$error = '';

// Only allow mutations via POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    // Create backup (timestamped snapshot folder)
    if (isset($_POST['create'])) {
        $backupDir = DATA_DIR . '/backups';
        if (!is_dir($backupDir)) { @mkdir($backupDir, 0755, true); }
        $stamp = date('Y-m-d-His');
        $snapshotDir = $backupDir . '/snap-' . $stamp;
        if (!@mkdir($snapshotDir, 0755, true)) {
            $error = 'Cannot create backup directory.';
        } else {
            $files = array(FILE_SETTINGS, FILE_MOVIES, FILE_ANIME, FILE_EPISODES, FILE_PAGES, FILE_SCHEDULE, FILE_USERS, FILE_CATEGORIES, FILE_FEATURED, FILE_COMMENTS, FILE_SLIDES, FILE_GENRES);
            $copied = 0;
            foreach ($files as $f) {
                if (file_exists($f)) {
                    $dest = $snapshotDir . '/' . basename($f);
                    if (@copy($f, $dest)) { $copied++; }
                }
            }
            $message = "Backup created: snap-{$stamp} ({$copied} files)";
            setFlash('success', $message);
        }
    }

    // Restore from a snapshot
    if (isset($_POST['restore']) && $_POST['restore'] !== '') {
        $snap = $_POST['restore'];
        // Validate snapshot name (no path traversal)
        if (!preg_match('/^snap-[0-9\-]+$/', $snap)) {
            $error = 'Invalid snapshot name.';
        } else {
            $snapDir = DATA_DIR . '/backups/' . $snap;
            if (!is_dir($snapDir)) {
                $error = 'Snapshot not found.';
            } else {
                $files = array(FILE_SETTINGS, FILE_MOVIES, FILE_ANIME, FILE_EPISODES, FILE_PAGES, FILE_SCHEDULE, FILE_USERS, FILE_CATEGORIES, FILE_FEATURED, FILE_COMMENTS, FILE_SLIDES, FILE_GENRES);
                $restored = 0;
                foreach ($files as $f) {
                    $src = $snapDir . '/' . basename($f);
                    if (file_exists($src)) {
                        if (@copy($src, $f)) { $restored++; }
                    }
                }
                $message = "Restored {$restored} files from {$snap}";
                setFlash('success', $message);
            }
        }
    }

    // Delete a snapshot
    if (isset($_POST['delete']) && $_POST['delete'] !== '') {
        $snap = $_POST['delete'];
        if (!preg_match('/^snap-[0-9\-]+$/', $snap)) {
            $error = 'Invalid snapshot name.';
        } else {
            $snapDir = DATA_DIR . '/backups/' . $snap;
            if (is_dir($snapDir)) {
                // Delete all files in snapshot
                $files = glob($snapDir . '/*');
                if (is_array($files)) {
                    foreach ($files as $f) { @unlink($f); }
                }
                @rmdir($snapDir);
                setFlash('success', 'Snapshot deleted.');
            }
        }
    }
}

// List existing snapshots
$backupDir = DATA_DIR . '/backups';
$snapshots = array();
if (is_dir($backupDir)) {
    $items = scandir($backupDir);
    foreach ($items as $it) {
        if ($it === '.' || $it === '..') { continue; }
        if (is_dir($backupDir . '/' . $it) && strpos($it, 'snap-') === 0) {
            $snapshots[] = $it;
        }
    }
    rsort($snapshots);
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Backup & Restore</h2>
    <p style="color:#a0a0b8; margin-bottom:20px;">
        Create timestamped snapshots of all your JSON data files. Useful before making bulk changes.
    </p>

    <?php if ($error): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;"><?php e($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php e($adminUrl); ?>/backup.php" style="display:inline;">
        <?php echo csrfField(); ?>
        <input type="hidden" name="create" value="1">
        <button type="submit" class="btn-admin btn-admin-success">
            <i class="fas fa-camera"></i> Create New Snapshot
        </button>
    </form>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Existing Snapshots (<?php echo count($snapshots); ?>)</h2>

    <?php if (empty($snapshots)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No snapshots yet. Click "Create New Snapshot" above to make one.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Snapshot</th><th>Files</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($snapshots as $snap): ?>
                <?php
                    $snapDir = DATA_DIR . '/backups/' . $snap;
                    $count = 0;
                    $files = glob($snapDir . '/*.json');
                    if (is_array($files)) { $count = count($files); }
                    // Parse date from name: snap-YYYY-MM-DD-HHMMSS
                    $dateStr = substr($snap, 5);
                ?>
                <tr>
                    <td><code><?php e($snap); ?></code></td>
                    <td><?php echo $count; ?> files</td>
                    <td style="white-space:nowrap;"><?php e($dateStr); ?></td>
                    <td>
                        <div class="table-actions">
                            <form method="POST" action="<?php e($adminUrl); ?>/backup.php" style="display:inline;" onsubmit="return confirm('Restore from this snapshot? Current data will be overwritten.');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="restore" value="<?php e($snap); ?>">
                                <button type="submit" class="btn-admin btn-admin-success btn-admin-sm"><i class="fas fa-undo"></i></button>
                            </form>
                            <form method="POST" action="<?php e($adminUrl); ?>/backup.php" style="display:inline;" onsubmit="return confirm('Delete this snapshot?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="delete" value="<?php e($snap); ?>">
                                <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
