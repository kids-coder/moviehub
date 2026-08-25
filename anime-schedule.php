<?php
// BDMovieHub - Weekly Anime Schedule

require_once __DIR__ . '/config.php';

$pageSection = 'schedule';
$isAnimePage = true;
$pageTitle = 'Anime Schedule';

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$scheduleByDay = array();
foreach ($days as $day) {
    $scheduleByDay[$day] = getScheduleByDay($day);
}

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title anime-accent" style="margin-bottom: 12px;">Weekly Anime Schedule</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Find out when your favorite anime air each week. All times shown in the schedule's local timezone.
        </p>

        <div class="schedule-grid">
            <?php foreach ($days as $day): ?>
                <div class="schedule-day">
                    <h4><?php e(substr($day, 0, 3)); ?> <span style="opacity:0.5; font-weight:400;"><?php e(substr($day, 3)); ?></span></h4>
                    <?php if (empty($scheduleByDay[$day])): ?>
                        <div class="schedule-empty">No anime scheduled</div>
                    <?php else: ?>
                        <?php foreach ($scheduleByDay[$day] as $s): ?>
                            <?php $a = isset($s['anime']) ? $s['anime'] : null; ?>
                            <?php if ($a): ?>
                            <div class="schedule-item">
                                <div class="time"><?php e(isset($s['time']) ? $s['time'] : '--:--'); ?></div>
                                <div class="title">
                                    <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" style="color:var(--text); text-decoration:none;">
                                        <?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
