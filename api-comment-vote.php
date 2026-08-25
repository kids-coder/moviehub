<?php
// BDMovieHub - Comment Voting Endpoint
// Records a helpful / not-helpful vote on an approved comment.
// Votes are stored per-comment inside data/comment_votes.json and are
// de-duplicated per IP+comment so each visitor can vote only once.
require_once __DIR__ . '/config.php';

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit;
}

/* ---------- CSRF ---------- */
if (!verifyCsrf()) {
    http_response_code(403);
    echo json_encode(array('error' => 'Invalid or expired token. Please reload the page.'));
    exit;
}

/* ---------- Input ---------- */
$commentId = isset($_POST['comment_id']) ? trim((string)$_POST['comment_id']) : '';
$vote      = isset($_POST['vote']) ? trim((string)$_POST['vote']) : '';
if ($commentId === '' || !in_array($vote, array('up', 'down'), true)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Missing or invalid parameters.'));
    exit;
}

/* ---------- Validate the comment exists & is approved ---------- */
$target = null;
foreach (getData(FILE_COMMENTS) as $c) {
    if ((isset($c['id']) ? $c['id'] : '') === $commentId &&
        (isset($c['status']) ? $c['status'] : '') === 'approved') {
        $target = $c;
        break;
    }
}
if ($target === null) {
    http_response_code(404);
    echo json_encode(array('error' => 'Comment not found.'));
    exit;
}

/* ---------- Rate limit: max 30 votes / minute / IP ---------- */
define('FILE_VOTE_RATE', DATA_DIR . '/vote_rate.json');
$__ip = clientIp();
$__rateData = getData(FILE_VOTE_RATE);
$__now = time();
$__window = 60;
$__maxReq = 30;
if (!isset($__rateData[$__ip])) { $__rateData[$__ip] = array(); }
$__rateData[$__ip] = array_values(array_filter($__rateData[$__ip], function ($t) use ($__now, $__window) {
    return ($__now - $t) < $__window;
}));
if (count($__rateData[$__ip]) >= $__maxReq) {
    http_response_code(429);
    echo json_encode(array('error' => 'Too many votes. Please slow down.'));
    saveData(FILE_VOTE_RATE, $__rateData);
    exit;
}
$__rateData[$__ip][] = $__now;
if (count($__rateData) > 50) {
    $__rateData = array_slice($__rateData, -50, null, true);
}
saveData(FILE_VOTE_RATE, $__rateData);

/* ---------- Record the vote (one vote per IP per comment) ---------- */
define('FILE_COMMENT_VOTES', DATA_DIR . '/comment_votes.json');
$votes = getData(FILE_COMMENT_VOTES);
if (!isset($votes[$commentId]) || !is_array($votes[$commentId])) {
    $votes[$commentId] = array('up' => 0, 'down' => 0, 'voters' => array());
}
$bucket = $votes[$commentId];
$voterKey = md5($__ip . '|' . $commentId);
if (isset($bucket['voters'][$voterKey])) {
    // Already voted on this comment — reject silently but report current tallies.
    echo json_encode(array(
        'ok'      => false,
        'error'   => 'You already voted on this comment.',
        'up'      => (int)$bucket['up'],
        'down'    => (int)$bucket['down'],
    ));
    exit;
}
$bucket['voters'][$voterKey] = 1;
$bucket[$vote] = (int)$bucket[$vote] + 1;

// Cap stored voters to keep the JSON small.
if (count($bucket['voters']) > 500) {
    $bucket['voters'] = array_slice($bucket['voters'], -500, null, true);
}
$votes[$commentId] = $bucket;
saveData(FILE_COMMENT_VOTES, $votes);

echo json_encode(array(
    'ok'   => true,
    'up'   => (int)$bucket['up'],
    'down' => (int)$bucket['down'],
));
exit;
