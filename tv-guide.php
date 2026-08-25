<?php
// BDMovieHub - TV Guide: weekly schedule + airing-today calendar
// Inspired by mlsbd / movielinkbd series schedule pages.

require_once __DIR__ . '/config.php';

$pageSection = 'schedule';
$isAnimePage = true;
$pageTitle   = 'TV Guide & Schedule';

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$scheduleByDay = array();
foreach ($days as $day) {
    $scheduleByDay[$day] = getScheduleByDay($day);
}

// Which day is "today"? (server timezone; falls back gracefully)
$todayIndex = (int)date('N') - 1; // 0=Monday .. 6=Sunday
if ($todayIndex < 0 || $todayIndex > 6) { $todayIndex = 0; }
$todayName = $days[$todayIndex];

// Active day tab (?day=Friday). Default = today.
$activeDay = isset($_GET['day']) ? ucfirst(strtolower(trim($_GET['day']))) : $todayName;
if (!in_array($activeDay, $days, true)) { $activeDay = $todayName; }

// Next 7 days strip for the mini calendar
$calendarDays = array();
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+{$i} day");
    $calendarDays[] = array(
        'name'    => $days[(int)date('N', $ts) - 1],
        'date'    => date('j M', $ts),
        'isToday' => $i === 0,
        'count'   => count($scheduleByDay[$days[(int)date('N', $ts) - 1]]),
    );
}

$activeItems = $scheduleByDay[$activeDay];

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title anime-accent" style="margin-bottom: 12px;">TV Guide &amp; Schedule</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            See what airs today and plan your week. All times are shown in the schedule's local timezone.
        </p>

        <!-- Airing Today strip -->
        <div class="tvguide-strip">
            <?php foreach ($calendarDays as $cd): ?>
                <a href="<?php e(BASE_URL); ?>/tv-guide.php?day=<?php echo urlencode($cd['name']); ?>"
                   class="tvguide-day<?php echo $cd['name'] === $activeDay ? ' active' : ''; ?>">
                    <span class="tvguide-day-name"><?php e($cd['isToday'] ? 'Today' : substr($cd['name'], 0, 3)); ?></span>
                    <span class="tvguide-day-date"><?php e($cd['date']); ?></span>
                    <span class="tvguide-day-count"><?php echo (int)$cd['count']; ?> ep</span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Selected day heading -->
        <div class="section-header" style="margin-top:28px;">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt" style="color:var(--anime-color); margin-right:6px;"></i>
                <?php e($activeDay === $todayName ? 'Airing Today' : 'Airing ' . $activeDay); ?>
            </h2>
            <a href="<?php e(BASE_URL); ?>/anime-schedule.php" class="section-link">Full Week <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if (empty($activeItems)): ?>
            <div class="empty-state">
                <i class="fas fa-mug-hot"></i>
                <p>Nothing scheduled for <?php e($activeDay); ?> yet — check another day.</p>
            </div>
        <?php else: ?>
            <div class="tvguide-list">
                <?php foreach ($activeItems as $s): ?>
                    <?php $a = isset($s['anime']) ? $s['anime'] : null; if (!$a) { continue; } ?>
                    <div class="tvguide-row">
                        <div class="tvguide-time"><?php e(isset($s['time']) ? $s['time'] : '--:--'); ?></div>
                        <?php if (!empty($a['poster'])): ?>
                            <a class="tvguide-poster" href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>">
                                <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>" loading="lazy">
                            </a>
                        <?php endif; ?>
                        <div class="tvguide-info">
                            <a class="tvguide-title" href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>">
                                <?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>
                            </a>
                            <div class="tvguide-meta">
                                <?php if (!empty($a['studio'])): ?><span><i class="fas fa-building"></i> <?php e($a['studio']); ?></span><?php endif; ?>
                                <?php if (!empty($a['genre'])): ?>
                                    <span><i class="fas fa-tags"></i>
                                        <?php echo htmlspecialchars(is_array($a['genre']) ? implode(', ', $a['genre']) : (string)$a['genre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($s['episode_number'])): ?><span><i class="fas fa-film"></i> EP <?php e($s['episode_number']); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <a class="btn btn-sm tvguide-watch" href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>">Watch</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Full week overview -->
        <div class="section-header" style="margin-top:36px;">
            <h2 class="section-title">Full Week at a Glance</h2>
        </div>
        <div class="schedule-grid">
            <?php foreach ($days as $day): ?>
                <div class="schedule-day<?php echo $day === $todayName ? ' is-today' : ''; ?>">
                    <h4><?php e(substr($day, 0, 3)); ?> <span style="opacity:0.5; font-weight:400;"><?php e(substr($day, 3)); ?></span></h4>
                    <?php if (empty($scheduleByDay[$day])): ?>
                        <div class="schedule-empty">No anime scheduled</div>
                    <?php else: ?>
                        <?php foreach ($scheduleByDay[$day] as $s): ?>
                            <?php $a = isset($s['anime']) ? $s['anime'] : null; if (!$a) { continue; } ?>
                            <div class="schedule-item">
                                <div class="time"><?php e(isset($s['time']) ? $s['time'] : '--:--'); ?></div>
                                <div class="title">
                                    <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" style="color:var(--text); text-decoration:none;">
                                        <?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
