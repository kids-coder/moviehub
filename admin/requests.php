<?php
// BDMovieHub - Admin Title Requests Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'requests';
$pageTitle = 'Title Requests';

$requestsFile = DATA_DIR . '/requests.json';
$requests = getData($requestsFile);

// Handle status update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['rid'])) {
    requireCsrf();
    $rid = $_POST['rid'];
    foreach ($requests as $i => $r) {
        if ((isset($r['id']) ? $r['id'] : '') === $rid) {
            if ($_POST['action'] === 'delete') {
                unset($requests[$i]);
                setFlash('success', 'Request deleted.');
            } elseif (in_array($_POST['action'], array('pending', 'approved', 'rejected'), true)) {
                $requests[$i]['status'] = $_POST['action'];
                setFlash('success', 'Request marked as ' . $_POST['action'] . '.');
            }
            break;
        }
    }
    saveData($requestsFile, array_values($requests));
    adminRedirect('requests.php');
}

// Sort newest first
usort($requests, function ($a, $b) {
    return strcmp(isset($b['date']) ? $b['date'] : '', isset($a['date']) ? $a['date'] : '');
});

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:18px; margin-bottom:16px;">User Title Requests (<?php echo count($requests); ?>)</h2>
    <?php if (empty($requests)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No title requests yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Title</th><th>Type</th><th>Language</th><th>Details</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?php e(isset($r['title']) ? $r['title'] : ''); ?></td>
                        <td><?php e(ucfirst(isset($r['type']) ? $r['type'] : '')); ?></td>
                        <td><?php e(isset($r['language']) ? $r['language'] : '-'); ?></td>
                        <td style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php e(isset($r['details']) ? $r['details'] : ''); ?>"><?php e(isset($r['details']) ? $r['details'] : '-'); ?></td>
                        <td><?php e(isset($r['date']) ? $r['date'] : ''); ?></td>
                        <td>
                            <?php
                                $__st = isset($r['status']) ? $r['status'] : 'pending';
                                $__bg = $__st === 'approved' ? 'rgba(46,204,113,0.15)' : ($__st === 'rejected' ? 'rgba(231,76,60,0.15)' : 'rgba(255,193,7,0.15)');
                                $__fg = $__st === 'approved' ? '#2ecc71' : ($__st === 'rejected' ? '#e74c3c' : '#f1c40f');
                            ?>
                            <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $__bg; ?>; color:<?php echo $__fg; ?>;"><?php e(ucfirst($__st)); ?></span>
                        </td>
                        <td>
                            <div class="table-actions" style="display:flex; gap:6px;">
                                <form method="POST" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="rid" value="<?php e(isset($r['id']) ? $r['id'] : ''); ?>">
                                    <input type="hidden" name="action" value="approved">
                                    <button type="submit" class="btn-admin btn-admin-success btn-admin-sm" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="rid" value="<?php e(isset($r['id']) ? $r['id'] : ''); ?>">
                                    <input type="hidden" name="action" value="rejected">
                                    <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm" title="Reject"><i class="fas fa-ban"></i></button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this request?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="rid" value="<?php e(isset($r['id']) ? $r['id'] : ''); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete"><i class="fas fa-trash"></i></button>
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
