<?php
// BDMovieHub - Single Movie Page
// Loads movie by slug, displays banner, info, player, related, comments, share

require_once __DIR__ . '/config.php';

$pageSection = 'movies';
$loadPlayerCss = true;
$loadPlayerJs  = true;

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$movies = getData(FILE_MOVIES);
$movie = getBySlug($movies, $slug);

if (!$movie) {
    setFlash('error', 'Movie not found.');
    redirect('index.php');
}

$pageTitle = isset($movie['title']) ? $movie['title'] : 'Movie';
$related = getRelatedMovies($movie, 6);

// Pre-extract fields with safe defaults
$mvId       = isset($movie['id']) ? $movie['id'] : '';
$mvTitle    = isset($movie['title']) ? $movie['title'] : 'Untitled';
$mvPoster   = isset($movie['poster']) ? $movie['poster'] : '';
$mvBanner   = isset($movie['banner']) ? $movie['banner'] : $mvPoster;
$mvYear     = isset($movie['year']) ? $movie['year'] : 'N/A';
$mvDuration = isset($movie['duration']) ? $movie['duration'] : 'N/A';
$mvQuality  = isset($movie['quality']) ? $movie['quality'] : '';
$mvRating   = isset($movie['rating']) ? $movie['rating'] : '';
$mvGenre    = isset($movie['genre']) && is_array($movie['genre']) ? $movie['genre'] : array();
$mvDesc     = isset($movie['description']) ? $movie['description'] : 'No description available.';
$mvStream   = isset($movie['stream_url']) ? $movie['stream_url'] : '';
$mvTrailer  = isset($movie['trailer']) ? $movie['trailer'] : '';
$mvDownload = isset($movie['download_url']) ? $movie['download_url'] : '';
$mvViews    = isset($movie['views']) ? intval($movie['views']) : 0;

// Optional backup sources (data-alt-sources) and native subtitle tracks.
// Admins can add "alt_sources" (array) and "subtitle_tracks" (label/lang/src) fields.
$altSources = array();
foreach ((isset($movie['alt_sources']) && is_array($movie['alt_sources']) ? $movie['alt_sources'] : array()) as $__as) {
    if (is_string($__as) && trim($__as) !== '' && $__as !== $mvStream) { $altSources[] = trim($__as); }
}
$subtitleTracks = array();
foreach ((isset($movie['subtitle_tracks']) && is_array($movie['subtitle_tracks']) ? $movie['subtitle_tracks'] : array()) as $__st) {
    if (isset($__st['src'], $__st['lang']) && is_string($__st['src']) && trim($__st['src']) !== '') {
        $subtitleTracks[] = array('src' => trim($__st['src']), 'lang' => substr(trim($__st['lang']), 0, 10), 'label' => isset($__st['label']) ? $__st['label'] : strtoupper(substr(trim($__st['lang']), 0, 10)));
    }
}

// SEO meta
$ogTitle = $mvTitle . ' (' . $mvYear . ') - Watch Online';
$ogDescription = truncate($mvDesc, 160);
$ogImage = !empty($mvBanner) ? $mvBanner : $mvPoster;
$ogUrl = currentUrl();

// Increment views (best-effort, ignored on errors)
incrementViews('movie', $mvId);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token expired. Please try again.');
        redirect('movie.php?slug=' . urlencode($slug) . '#comments');
    }
    $author = isset($_POST['author']) ? trim($_POST['author']) : 'Anonymous';
    $text   = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    if ($text !== '') {
        $author = $author === '' ? 'Anonymous' : substr($author, 0, 60);
        $text = substr($text, 0, 1000);
        addComment(array(
            'item_type'   => 'movie',
            'item_id'     => $mvId,
            'item_slug'   => $slug,
            'item_title'  => $mvTitle,
            'author'      => $author,
            'text'        => $text,
            'status'      => 'approved',
            'ip'          => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
        ));
        setFlash('success', 'Your comment has been posted.');
        redirect('movie.php?slug=' . urlencode($slug) . '#comments');
    } else {
        setFlash('error', 'Comment cannot be empty.');
    }
}

$comments = getApprovedComments('movie', $mvId);

include __DIR__ . '/header.php';

// Output JSON-LD structured data
outputJsonLd($movie, 'movie');
?>

<!-- Movie Banner -->
<div class="movie-banner" style="background-image: url('<?php echo htmlspecialchars($mvBanner, ENT_QUOTES, 'UTF-8'); ?>');"></div>

<!-- Movie Detail -->
<section class="movie-detail">
    <div class="container">
        <div class="movie-detail-grid">
            <div class="movie-poster">
                <img loading="lazy" src="<?php echo htmlspecialchars($mvPoster, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e($mvTitle); ?>">
                <button class="btn btn-outline fav-btn" style="width:100%; margin-top:10px;"
                        data-type="movie" data-id="<?php e($mvId); ?>" data-title="<?php e($mvTitle); ?>">
                    <i class="far fa-heart"></i> Favorite
                </button>
            </div>
            <div class="movie-info">
                <h1><?php e($mvTitle); ?></h1>
                <div class="movie-meta-row">
                    <span><i class="far fa-calendar"></i> <?php e($mvYear); ?></span>
                    <span><i class="far fa-clock"></i> <?php e($mvDuration); ?></span>
                    <?php if (!empty($mvQuality)): ?>
                        <span class="card-badge"><?php e($mvQuality); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($mvRating)): ?>
                        <span class="movie-rating"><i class="fas fa-star"></i> <?php e($mvRating); ?>/10</span>
                    <?php endif; ?>
                    <?php if ($mvViews > 0): ?>
                        <span><i class="fas fa-eye"></i> <?php echo number_format($mvViews); ?> views</span>
                    <?php endif; ?>
                </div>
                <!-- Rate this movie (5-star widget, stored per visitor) -->
                <?php $__mSettings = getSettings(); if (!isset($__mSettings['enable_ratings']) || !empty($__mSettings['enable_ratings'])): ?>
                <div class="user-rating" data-key="movie-<?php e($mvId); ?>" data-value="<?php echo !empty($mvRating) ? round(((float)$mvRating / 10) * 5, 1) : '0'; ?>" style="margin-top:12px;"></div>
                <?php endif; ?>

                <?php if (!empty($mvGenre)): ?>
                <div class="movie-genres">
                    <?php foreach ($mvGenre as $g): ?>
                        <a href="<?php e(BASE_URL); ?>/genres.php?genre=<?php echo urlencode($g); ?>" class="genre-tag"><?php e($g); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <p class="movie-description"><?php e($mvDesc); ?></p>

                <?php
                    $__meta = array(
                        'alternate_title' => array('Alternate Title', 'fas fa-language'),
                        'country'         => array('Country', 'fas fa-globe'),
                        'language'        => array('Language', 'fas fa-comment-dots'),
                        'cast'            => array('Cast', 'fas fa-users'),
                        'director'        => array('Director', 'fas fa-video'),
                        'subtitles'       => array('Subtitles', 'fas fa-closed-captioning'),
                        'legal_providers' => array('Watch On', 'fas fa-external-link-alt'),
                    );
                ?>
                <div class="movie-extra-meta" style="margin:14px 0; font-size:13px; color:var(--muted); display:grid; gap:4px;">
                    <?php foreach ($__meta as $key => $labelInfo): ?>
                        <?php if (!empty($movie[$key])): ?>
                            <div><i class="<?php e($labelInfo[1]); ?>" style="width:16px;"></i> <strong><?php e($labelInfo[0]); ?>:</strong> <?php e($movie[$key]); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="movie-actions">
                    <?php if (!empty($mvStream)): ?>
                        <a href="#player" class="btn btn-primary"><i class="fas fa-play"></i> Watch Now</a>
                    <?php endif; ?>
                    <?php if (!empty($mvTrailer)): ?>
                        <a href="<?php echo htmlspecialchars($mvTrailer, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-outline"><i class="fas fa-film"></i> Trailer</a>
                    <?php endif; ?>
                    <?php if (!empty($mvDownload)): ?>
                        <a href="<?php echo htmlspecialchars($mvDownload, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-accent"><i class="fas fa-download"></i> Download</a>
                    <?php endif; ?>
                    <button class="btn btn-outline share-btn" data-share-url="<?php e($ogUrl); ?>" data-share-title="<?php e($mvTitle); ?>">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </div>

                <!-- Share Options (hidden by default, toggled by JS) -->
                <div class="share-options" id="share-options" style="display:none; margin-top:14px; gap:8px;">
                    <a href="#" class="share-link" data-share="facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                    <a href="#" class="share-link" data-share="twitter"><i class="fab fa-twitter"></i> Twitter</a>
                    <a href="#" class="share-link" data-share="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="#" class="share-link" data-share="telegram"><i class="fab fa-telegram"></i> Telegram</a>
                    <a href="#" class="share-link" data-share="copy"><i class="fas fa-link"></i> Copy Link</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Player -->
<?php if (!empty($mvStream)): ?>
<section class="section" id="player">
    <div class="container">
        <div class="player-title-bar">
            <h2>Watch: <?php e($mvTitle); ?></h2>
            <?php if (!empty($mvQuality)): ?>
                <span class="quality-badge"><?php e($mvQuality); ?></span>
            <?php endif; ?>
        </div>
        <div class="player-wrapper">
            <div class="player-container">
                <video id="video-player" data-src="<?php echo htmlspecialchars($mvStream, ENT_QUOTES, 'UTF-8'); ?>"<?php if (!empty($altSources)): ?> data-alt-sources="<?php echo htmlspecialchars(implode('|', $altSources), ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?> controls preload="metadata" playsinline>
                    <?php foreach ($subtitleTracks as $st): ?>
                    <track kind="subtitles" label="<?php e($st['label']); ?>" srclang="<?php e($st['lang']); ?>" src="<?php echo htmlspecialchars($st['src'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endforeach; ?>
                </video>
                <div class="player-loading">
                    <div class="player-spinner"></div>
                </div>
                <div class="player-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Playback Error</h3>
                    <p>Unable to load this video stream. Please try again later.</p>
                </div>
            </div>
        </div>

        <!-- Report broken video -->
        <div style="display:flex; justify-content:flex-end; margin-top:14px;">
            <button class="btn btn-outline btn-sm" id="report-broken-btn">
                <i class="fas fa-flag"></i> Report Broken Video
            </button>
        </div>
        <div id="report-form" style="display:none; margin-top:14px; padding:16px; background:var(--card); border-radius:8px; max-width:500px;">
            <h4 style="margin-bottom:10px; font-size:14px;">Report a Problem</h4>
            <form id="report-form-el">
                <?php echo csrfField(); ?>
                <input type="hidden" name="type" value="movie">
                <input type="hidden" name="id" value="<?php e($mvId); ?>">
                <input type="hidden" name="slug" value="<?php e($slug); ?>">
                <!-- Honeypot: real users never fill this; bots auto-fill it -->
                <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                    <label>Website (leave empty)<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <select name="reason" style="width:100%; padding:8px 12px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                        <option value="broken">Video not loading</option>
                        <option value="wrong">Wrong movie</option>
                        <option value="subtitles">Missing subtitles</option>
                        <option value="audio">Audio problem</option>
                        <option value="quality">Poor quality</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <textarea name="detail" rows="3" placeholder="Additional details (optional)" style="width:100%; padding:8px 12px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:6px;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Submit Report</button>
            </form>
            <div id="report-result" style="margin-top:10px; font-size:13px;"></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Comments Section -->
<section class="section" id="comments">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Comments (<?php echo count($comments); ?>)</h2>
        </div>
        <div style="background: var(--card); border-radius: 12px; padding: 24px; max-width: 800px;">
            <form method="POST" action="#comments">
                <?php echo csrfField(); ?>
                <input type="hidden" name="comment_submit" value="1">
                <div class="form-group" style="margin-bottom: 12px;">
                    <input type="text" name="author" placeholder="Your name (optional)" maxlength="60" style="width:100%; padding:10px 14px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:8px;">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <textarea name="comment" required rows="4" placeholder="Write a comment..." maxlength="1000" style="width:100%; padding:10px 14px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:8px;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-comment"></i> Post Comment</button>
                <p style="font-size:12px; color:var(--muted); margin-top:8px;">Your comment will be posted instantly. Please keep it respectful.</p>
            </form>

            <div style="margin-top: 24px;">
                <?php if (empty($comments)): ?>
                    <p style="color:var(--muted); text-align:center; padding:24px;">No comments yet. Be the first to share your thoughts!</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <?php
                            $__cid = isset($c['id']) ? $c['id'] : '';
                            $__votes = getData(FILE_COMMENT_VOTES);
                            $__cv = (isset($__votes[$__cid]) && is_array($__votes[$__cid])) ? $__votes[$__cid] : array('up' => 0, 'down' => 0);
                        ?>
                        <div style="padding:14px 0; border-bottom:1px solid var(--border);">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;">
                                    <?php e(strtoupper(substr(isset($c['author']) ? $c['author'] : 'A', 0, 1))); ?>
                                </div>
                                <div>
                                    <strong style="font-size:14px;"><?php e(isset($c['author']) ? $c['author'] : 'Anonymous'); ?></strong>
                                    <div style="font-size:11px; color:var(--muted);"><?php e(isset($c['date']) ? $c['date'] : ''); ?></div>
                                </div>
                            </div>
                            <p style="margin-left:46px; color:var(--text); font-size:14px; line-height:1.6;"><?php e(isset($c['text']) ? $c['text'] : ''); ?></p>
                            <?php $__vSettings = getSettings(); if ($__cid !== '' && (!isset($__vSettings['enable_comment_votes']) || !empty($__vSettings['enable_comment_votes']))): ?>
                            <div class="comment-votes" data-comment-id="<?php e($__cid); ?>" style="margin-left:46px; margin-top:8px; display:flex; align-items:center; gap:12px;">
                                <button type="button" class="comment-vote-btn" data-vote="up" aria-label="Helpful">
                                    <i class="far fa-thumbs-up"></i> <span class="vote-count"><?php echo (int)$__cv['up']; ?></span>
                                </button>
                                <button type="button" class="comment-vote-btn" data-vote="down" aria-label="Not helpful">
                                    <i class="far fa-thumbs-down"></i> <span class="vote-count"><?php echo (int)$__cv['down']; ?></span>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Movies -->
<?php if (!empty($related)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Related Movies</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($related as $m): ?>
                <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img loading="lazy" src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?>
                            <span class="card-badge"><?php e($m['quality']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['rating'])): ?>
                            <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                        <?php endif; ?>
                        <div class="card-overlay">
                            <button class="card-play-btn"><i class="fas fa-play"></i></button>
                        </div>
                    </div>
                    <div class="card-info">
                        <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                        <div class="card-meta">
                            <span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
