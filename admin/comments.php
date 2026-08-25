<?php
// BDMovieHub - Admin Comments / Messages Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'comments';
$pageTitle = 'Messages & Comments';

// Load comments
$comments = getData(FILE_COMMENTS);

// Load contact messages
$contactsFile = DATA_DIR . '/contacts.json';
$contacts = array();
if (file_exists($contactsFile)) {
    $raw = @file_get_contents($contactsFile);
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) { $contacts = $decoded; }
    }
}

// Mark contact as read (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['read']) && $_POST['read'] !== '') {
    requireCsrf();
    $cid = $_POST['read'];
    foreach ($contacts as $i => $c) {
        if (isset($c['id']) && $c['id'] === $cid) {
            $contacts[$i]['read'] = true;
            break;
        }
    }
    @file_put_contents($contactsFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    setFlash('success', 'Marked as read.');
    adminRedirect('comments.php');
}

include __DIR__ . '/header.php';
?>

<!-- Contact Messages -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Contact Messages (<?php echo count($contacts); ?>)</h2>
    </div>

    <?php if (empty($contacts)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No contact messages yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>From</th><th>Subject</th><th>Message</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php
                // Show newest first
                $reversed = array_reverse($contacts);
                foreach ($reversed as $c):
                ?>
                <tr>
                    <td style="white-space:nowrap;"><?php e(isset($c['date']) ? $c['date'] : '-'); ?></td>
                    <td>
                        <strong><?php e(isset($c['name']) ? $c['name'] : '-'); ?></strong><br>
                        <a href="mailto:<?php e(isset($c['email']) ? $c['email'] : ''); ?>" style="color:#469AFF; font-size:11px;"><?php e(isset($c['email']) ? $c['email'] : ''); ?></a>
                    </td>
                    <td><?php e(isset($c['subject']) ? $c['subject'] : '(no subject)'); ?></td>
                    <td style="max-width:300px;"><?php e(mb_substr(isset($c['message']) ? $c['message'] : '', 0, 120, 'UTF-8')); ?><?php echo strlen(isset($c['message']) ? $c['message'] : '') > 120 ? '...' : ''; ?></td>
                    <td>
                        <?php if (isset($c['read']) && $c['read']): ?>
                            <span style="color:#2ecc71;">Read</span>
                        <?php else: ?>
                            <span style="color:#ffa502;">Unread</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <?php if (!isset($c['read']) || !$c['read']): ?>
                                <form method="POST" action="<?php e($adminUrl); ?>/comments.php" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="read" value="<?php echo htmlspecialchars($c['id'], ENT_QUOTES); ?>">
                                    <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm" title="Mark read"><i class="fas fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <a href="mailto:<?php e(isset($c['email']) ? $c['email'] : ''); ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Reply"><i class="fas fa-reply"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/comment-delete.php" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($c['id'], ENT_QUOTES); ?>">
                                <input type="hidden" name="type" value="contact">
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

<!-- User Comments (on movies/anime) -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">User Comments (<?php echo count($comments); ?>)</h2>
    </div>

    <?php if (empty($comments)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No user comments yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Author</th><th>On</th><th>Comment</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php
                $reversed2 = array_reverse($comments);
                foreach ($reversed2 as $c):
                ?>
                <tr>
                    <td style="white-space:nowrap;"><?php e(isset($c['date']) ? $c['date'] : '-'); ?></td>
                    <td>
                        <strong><?php e(isset($c['author']) ? $c['author'] : 'Anonymous'); ?></strong>
                    </td>
                    <td>
                        <?php
                            $type = isset($c['item_type']) ? $c['item_type'] : 'movie';
                            $slug = isset($c['item_slug']) ? $c['item_slug'] : '';
                            $url = ($type === 'anime') ? BASE_URL . '/anime-watch.php?slug=' . urlencode($slug) : BASE_URL . '/movie.php?slug=' . urlencode($slug);
                        ?>
                        <a href="<?php e($url); ?>" target="_blank" style="color:#469AFF;">
                            <?php e(isset($c['item_title']) ? $c['item_title'] : '(deleted)'); ?>
                        </a>
                    </td>
                    <td style="max-width:300px;"><?php e(mb_substr(isset($c['text']) ? $c['text'] : '', 0, 120, 'UTF-8')); ?></td>
                    <td>
                        <?php $st = isset($c['status']) ? $c['status'] : 'pending'; ?>
                        <span style="color:<?php echo $st === 'approved' ? '#2ecc71' : ($st === 'spam' ? '#e74c3c' : '#ffa502'); ?>;">
                            <?php e(ucfirst($st)); ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <?php if ($st !== 'approved'): ?>
                                <form method="POST" action="<?php e($adminUrl); ?>/comment-approve.php" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($c['id']) ? $c['id'] : '', ENT_QUOTES); ?>">
                                    <button type="submit" class="btn-admin btn-admin-success btn-admin-sm" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?php e($adminUrl); ?>/comment-delete.php" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($c['id']) ? $c['id'] : '', ENT_QUOTES); ?>">
                                <input type="hidden" name="type" value="comment">
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

<!-- Reports -->
<?php
$reportsFile = DATA_DIR . '/reports.json';
$reports = array();
if (file_exists($reportsFile)) {
    $raw = @file_get_contents($reportsFile);
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) { $reports = $decoded; }
    }
}
?>
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Broken Video Reports (<?php echo count($reports); ?>)</h2>
    </div>

    <?php if (empty($reports)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No reports yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Type</th><th>Item ID/Slug</th><th>Reason</th><th>Detail</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $reversed3 = array_reverse($reports); ?>
                <?php foreach ($reversed3 as $r): ?>
                <tr>
                    <td style="white-space:nowrap;"><?php e(isset($r['date']) ? $r['date'] : '-'); ?></td>
                    <td><?php e(ucfirst(isset($r['type']) ? $r['type'] : '-')); ?></td>
                    <td>
                        <?php e(isset($r['item_id']) ? $r['item_id'] : ''); ?>
                        <?php if (!empty($r['item_slug'])): ?>
                            <br><code style="font-size:11px; color:#a0a0b8;"><?php e($r['item_slug']); ?></code>
                        <?php endif; ?>
                    </td>
                    <td><?php e(isset($r['reason']) ? $r['reason'] : '-'); ?></td>
                    <td style="max-width:200px;"><?php e(mb_substr(isset($r['detail']) ? $r['detail'] : '', 0, 100, 'UTF-8')); ?></td>
                    <td>
                        <?php if (isset($r['resolved']) && $r['resolved']): ?>
                            <span style="color:#2ecc71;">Resolved</span>
                        <?php else: ?>
                            <span style="color:#ffa502;">Open</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
