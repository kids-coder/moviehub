<?php
// BDMovieHub - Title request page
require_once __DIR__ . '/config.php';

$pageSection = 'request';
$pageTitle = 'Request a Title';
$sent = false;
$errors = array();

$requestsFile = DATA_DIR . '/requests.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Security token expired. Please refresh the page and try again.';
    } else {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : 'movie';
        $language = isset($_POST['language']) ? trim($_POST['language']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $details = isset($_POST['details']) ? trim($_POST['details']) : '';
        if ($title === '') { $errors[] = 'A title is required.'; }
        if (!in_array($type, array('movie', 'series', 'anime'), true)) { $type = 'movie'; }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
        if (mb_strlen($title, 'UTF-8') > 160) { $title = mb_substr($title, 0, 160, 'UTF-8'); }
        if (mb_strlen($details, 'UTF-8') > 1000) { $details = mb_substr($details, 0, 1000, 'UTF-8'); }
        if (empty($errors)) {
            $requests = getData($requestsFile);

            // Duplicate detection: merge with an existing pending request
            $__norm = strtolower(preg_replace('/\s+/', ' ', $title));
            $__merged = false;
            foreach ($requests as $i => $r) {
                if ((isset($r['status']) ? $r['status'] : '') === 'pending'
                    && strtolower(preg_replace('/\s+/', ' ', isset($r['title']) ? $r['title'] : '')) === $__norm) {
                    $requests[$i]['votes'] = intval(isset($r['votes']) ? $r['votes'] : 0) + 1;
                    $__merged = true;
                    break;
                }
            }

            if (!$__merged) {
                $requests[] = array(
                    'id' => 'rq' . (count($requests) + 1) . '-' . substr(md5(uniqid('', true)), 0, 6),
                    'title' => $title,
                    'type' => $type,
                    'language' => $language,
                    'email' => $email,
                    'details' => $details,
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'votes' => 1,
                );
            }
            if (saveData($requestsFile, $requests)) { $sent = true; }
            else { $errors[] = 'The request could not be saved. Please try again later.'; }
        }
    }
}

// Recent public requests (pending/approved only, no emails)
$__recentRequests = getData($requestsFile);
usort($__recentRequests, function ($a, $b) { return strcmp(isset($b['date']) ? $b['date'] : '', isset($a['date']) ? $a['date'] : ''); });
$__recentRequests = array_slice(array_filter($__recentRequests, function ($r) {
    return in_array((isset($r['status']) ? $r['status'] : ''), array('pending', 'approved'), true);
}), 0, 8);

include __DIR__ . '/header.php';
?>
<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 720px;">
        <h1 class="section-title" style="margin-bottom:12px;">Request a Title</h1>
        <p style="color:var(--muted); margin-bottom:24px;">Tell us what you would like to see added to the catalog.</p>
        <?php if ($sent): ?><div class="admin-card" style="color:#2ecc71;">Thanks. Your request has been received.</div><?php endif; ?>
        <?php if (!empty($errors)): ?><div class="admin-card" style="color:#e74c3c;"><?php foreach ($errors as $error): ?><p><?php e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
        <div class="admin-card">
            <form method="POST" action="<?php e(BASE_URL); ?>/request.php">
                <?php echo csrfField(); ?>
                <div class="form-group"><label for="request-title">Title</label><input id="request-title" name="title" required maxlength="160"></div>
                <div class="form-row">
                    <div class="form-group"><label for="request-type">Type</label><select id="request-type" name="type"><option value="movie">Movie</option><option value="series">Series</option><option value="anime">Anime</option></select></div>
                    <div class="form-group"><label for="request-language">Language</label><input id="request-language" name="language" placeholder="Bangla, Hindi, English"></div>
                </div>
                <div class="form-group"><label for="request-email">Email (optional)</label><input id="request-email" type="email" name="email"></div>
                <div class="form-group"><label for="request-details">Additional details</label><textarea id="request-details" name="details" rows="4" maxlength="1000"></textarea></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
            </form>
        </div>

        <?php if (!empty($__recentRequests)): ?>
        <div style="margin-top:32px;">
            <h2 class="section-title" style="font-size:20px; margin-bottom:14px;">Recent Requests</h2>
            <div style="display:grid; gap:10px;">
                <?php foreach ($__recentRequests as $r): ?>
                    <div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px;">
                        <div>
                            <strong><?php e(isset($r['title']) ? $r['title'] : ''); ?></strong>
                            <span style="color:var(--muted); font-size:13px;"> — <?php e(ucfirst(isset($r['type']) ? $r['type'] : '')); ?><?php if (!empty($r['language'])): ?> · <?php e($r['language']); ?><?php endif; ?></span>
                        </div>
                        <span class="genre-pill active" title="Community votes"><i class="fas fa-thumbs-up"></i> <?php echo intval(isset($r['votes']) ? $r['votes'] : 1); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
