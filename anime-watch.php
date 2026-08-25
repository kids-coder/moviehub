<?php
// BDMovieHub - Watch Anime Episode
// Loads anime by slug, episode by number, displays player + episode list + comments + related

require_once __DIR__ . '/config.php';

$pageSection = 'anime';
$isAnimePage = true;
$loadPlayerCss = true;
$loadPlayerJs  = true;

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$epNum = isset($_GET['ep']) ? intval($_GET['ep']) : 1;

$animeList = getData(FILE_ANIME);
$anime = getBySlug($animeList, $slug);

if (!$anime) {
    setFlash('error', 'Anime not found.');
    redirect('anime.php');
}

$pageTitle = isset($anime['title']) ? $anime['title'] : 'Anime';
$animeId  = isset($anime['id']) ? $anime['id'] : '';
$animeSlug = isset($anime['slug']) ? $anime['slug'] : $slug;

$episodes = getEpisodesByAnime($animeId);

// Find current episode
$currentEp = null;
foreach ($episodes as $ep) {
    $epN = isset($ep['episode_number']) ? intval($ep['episode_number']) : 0;
    if ($epN === $epNum) { $currentEp = $ep; break; }
}
if (!$currentEp && !empty($episodes)) { $currentEp = $episodes[0]; $epNum = isset($currentEp['episode_number']) ? intval($currentEp['episode_number']) : 1; }

// Pre-extract anime fields
$anBanner = isset($anime['banner']) ? $anime['banner'] : (isset($anime['poster']) ? $anime['poster'] : '');
$anPoster = isset($anime['poster']) ? $anime['poster'] : '';
$anTitle  = isset($anime['title']) ? $anime['title'] : 'Untitled';
$anId     = isset($anime['id']) ? $anime['id'] : '';
$anDesc   = isset($anime['description']) ? $anime['description'] : 'No description available.';
$anViews  = isset($anime['views']) ? intval($anime['views']) : 0;

// SEO meta
$ogTitle = $anTitle . ' - Episode ' . $epNum . ' - Watch Online';
$ogDescription = truncate($anDesc, 160);
$ogImage = !empty($anBanner) ? $anBanner : $anPoster;
$ogUrl = currentUrl();

// Increment views
incrementViews('anime', $anId);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token expired. Please try again.');
        redirect('anime-watch.php?slug=' . urlencode($animeSlug) . '&ep=' . $epNum . '#comments');
    }
    $author = isset($_POST['author']) ? trim($_POST['author']) : 'Anonymous';
    $text   = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    if ($text !== '') {
        $author = $author === '' ? 'Anonymous' : substr($author, 0, 60);
        $text = substr($text, 0, 1000);
        addComment(array(
            'item_type'   => 'anime',
            'item_id'     => $anId,
            'item_slug'   => $animeSlug,
            'item_title'  => $anTitle,
            'author'      => $author,
            'text'        => $text,
            'status'      => 'approved',
            'ip'          => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
        ));
        setFlash('success', 'Your comment has been posted.');
        redirect('anime-watch.php?slug=' . urlencode($animeSlug) . '&ep=' . $epNum . '#comments');
    } else {
        setFlash('error', 'Comment cannot be empty.');
    }
}

$comments = getApprovedComments('anime', $anId);
$related = getRelatedAnime($anime, 6);

include __DIR__ . '/header.php';

// JSON-LD structured data
outputJsonLd($anime, 'anime');
?>

<!-- Anime Banner -->
<div class="movie-banner" style="background-image: url('<?php echo htmlspecialchars($anBanner, ENT_QUOTES, 'UTF-8'); ?>');"></div>

<!-- Anime Detail -->
<section class="movie-detail">
    <div class="container">
        <div class="movie-detail-grid">
            <div class="movie-poster">
                <img src="<?php echo htmlspecialchars($anPoster, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e($anTitle); ?>">
                <button class="btn btn-outline fav-btn" style="width:100%; margin-top:10px;"
                        data-type="anime" data-id="<?php e($anId); ?>" data-title="<?php e($anTitle); ?>">
                    <i class="far fa-heart"></i> Favorite
                </button>
            </div>
            <div class="movie-info">
                <h1><?php e($anTitle); ?></h1>
                <div class="movie-meta-row">
                    <span><i class="fas fa-list"></i> <?php e(isset($anime['episode_count']) ? $anime['episode_count'] : count($episodes)); ?> Episodes</span>
                    <?php if (!empty($anime['status'])): ?>
                        <span class="card-badge status <?php echo $anime['status'] === 'completed' ? 'completed' : ''; ?>"><?php e(ucfirst($anime['status'])); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($anime['rating'])): ?>
                        <span class="movie-rating"><i class="fas fa-star"></i> <?php e($anime['rating']); ?>/10</span>
                    <?php endif; ?>
                    <?php if (!empty($anime['studio'])): ?>
                        <span><i class="fas fa-building"></i> <?php e($anime['studio']); ?></span>
                    <?php endif; ?>
                    <?php if ($anViews > 0): ?>
                        <span><i class="fas fa-eye"></i> <?php echo number_format($anViews); ?> views</span>
                    <?php endif; ?>
                </div>

                <?php if (isset($anime['genre']) && is_array($anime['genre']) && !empty($anime['genre'])): ?>
                <div class="movie-genres">
                    <?php foreach ($anime['genre'] as $g): ?>
                        <a href="<?php e(BASE_URL); ?>/genres.php?genre=<?php echo urlencode($g); ?>" class="genre-tag"><?php e($g); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <p class="movie-description"><?php e($anDesc); ?></p>

                <div class="movie-actions">
                    <?php if ($currentEp && !empty($currentEp['stream_url'])): ?>
                        <a href="#player" class="btn btn-anime"><i class="fas fa-play"></i> Watch Episode <?php e($epNum); ?></a>
                    <?php endif; ?>
                    <button class="btn btn-outline share-btn" data-share-url="<?php e($ogUrl); ?>" data-share-title="<?php e($anTitle); ?>">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </div>

                <!-- Share options -->
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

<?php
$epStreamUrl = ($currentEp && isset($currentEp['stream_url'])) ? $currentEp['stream_url'] : '';
$epNum = $currentEp && isset($currentEp['episode_number']) ? $currentEp['episode_number'] : $epNum;
$epTitle = $currentEp && isset($currentEp['title']) ? $currentEp['title'] : '';
?>
<!-- Episode Player -->
<?php if ($currentEp && !empty($epStreamUrl)): ?>
<section class="section">
    <div class="container">
        <div class="player-title-bar">
            <h2>Episode <?php e($epNum); ?>: <?php e($epTitle); ?></h2>
        </div>
        <div class="player-wrapper">
            <div class="player-container">
                <video id="video-player" data-src="<?php echo htmlspecialchars($epStreamUrl, ENT_QUOTES, 'UTF-8'); ?>" controls preload="metadata" playsinline></video>
                <div class="player-loading">
                    <div class="player-spinner"></div>
                </div>
                <div class="player-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Playback Error</h3>
                    <p>Unable to load this episode stream. Please try again later.</p>
                </div>
            </div>
        </div>

        <!-- Episode navigation -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
            <?php
                $prevEp = null; $nextEp = null;
                foreach ($episodes as $i => $ep) {
                    $epN2 = isset($ep['episode_number']) ? intval($ep['episode_number']) : 0;
                    if ($epN2 === $epNum) {
                        if ($i > 0) { $prevEp = $episodes[$i - 1]; }
                        if ($i < count($episodes) - 1) { $nextEp = $episodes[$i + 1]; }
                        break;
                    }
                }
            ?>
            <?php if ($prevEp): ?>
                <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode($animeSlug); ?>&ep=<?php echo intval(isset($prevEp['episode_number']) ? $prevEp['episode_number'] : 0); ?>" class="btn btn-outline">
                    <i class="fas fa-chevron-left"></i> Episode <?php e(isset($prevEp['episode_number']) ? $prevEp['episode_number'] : '?'); ?>
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <?php if ($nextEp): ?>
                <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode($animeSlug); ?>&ep=<?php echo intval(isset($nextEp['episode_number']) ? $nextEp['episode_number'] : 0); ?>" class="btn btn-anime">
                    Episode <?php e(isset($nextEp['episode_number']) ? $nextEp['episode_number'] : '?'); ?> <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
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
                <input type="hidden" name="type" value="anime">
                <input type="hidden" name="id" value="<?php e($anId); ?>">
                <input type="hidden" name="slug" value="<?php e($animeSlug); ?>">
                <!-- Honeypot: real users never fill this; bots auto-fill it -->
                <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                    <label>Website (leave empty)<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <select name="reason" style="width:100%; padding:8px 12px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                        <option value="broken">Video not playing</option>
                        <option value="wrong">Wrong episode</option>
                        <option value="quality">Poor quality</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <textarea name="detail" rows="3" placeholder="Additional details (optional)" style="width:100%; padding:8px 12px; background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:6px;"></textarea>
                </div>
                <button type="submit" class="btn btn-anime btn-sm"><i class="fas fa-paper-plane"></i> Submit Report</button>
            </form>
            <div id="report-result" style="margin-top:10px; font-size:13px;"></div>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="empty-state">
            <i class="fas fa-exclamation-circle"></i>
            <h3>No Stream Available</h3>
            <p>This episode does not have a stream URL yet.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Episode List -->
<?php if (!empty($episodes)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title anime-accent">All Episodes (<?php echo count($episodes); ?>)</h2>
        </div>
        <div class="episode-list">
            <?php foreach ($episodes as $ep): ?>
                <?php $epN3 = isset($ep['episode_number']) ? intval($ep['episode_number']) : 0; ?>
                <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode($animeSlug); ?>&ep=<?php echo $epN3; ?>"
                   class="episode-item <?php echo $epN3 === $epNum ? 'active' : ''; ?>">
                    <span class="ep-num">EP <?php e($epN3); ?></span>
                    <span class="ep-title"><?php e(isset($ep['title']) ? $ep['title'] : 'Episode ' . $epN3); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Comments Section -->
<section class="section" id="comments">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title anime-accent">Comments (<?php echo count($comments); ?>)</h2>
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
                <button type="submit" class="btn btn-anime"><i class="fas fa-comment"></i> Post Comment</button>
                <p style="font-size:12px; color:var(--muted); margin-top:8px;">Your comment will be posted instantly. Please keep it respectful.</p>
            </form>

            <div style="margin-top: 24px;">
                <?php if (empty($comments)): ?>
                    <p style="color:var(--muted); text-align:center; padding:24px;">No comments yet. Be the first to share your thoughts!</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div style="padding:14px 0; border-bottom:1px solid var(--border);">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:var(--anime-color); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;">
                                    <?php e(strtoupper(substr(isset($c['author']) ? $c['author'] : 'A', 0, 1))); ?>
                                </div>
                                <div>
                                    <strong style="font-size:14px;"><?php e(isset($c['author']) ? $c['author'] : 'Anonymous'); ?></strong>
                                    <div style="font-size:11px; color:var(--muted);"><?php e(isset($c['date']) ? $c['date'] : ''); ?></div>
                                </div>
                            </div>
                            <p style="margin-left:46px; color:var(--text); font-size:14px; line-height:1.6;"><?php e(isset($c['text']) ? $c['text'] : ''); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Anime -->
<?php if (!empty($related)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title anime-accent">Related Anime</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($related as $a): ?>
                <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>" loading="lazy">
                        <?php if (!empty($a['rating'])): ?>
                            <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($a['rating']); ?></span>
                        <?php endif; ?>
                        <div class="card-overlay">
                            <button class="card-play-btn"><i class="fas fa-play"></i></button>
                        </div>
                    </div>
                    <div class="card-info">
                        <div class="card-title"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></div>
                        <div class="card-meta">
                            <span><i class="fas fa-list"></i> <?php e(isset($a['episode_count']) ? $a['episode_count'] : 0); ?> EPs</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
