<?php
// BDMovieHub - Admin Schedule Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'schedule';
$pageTitle = 'Schedule';

$animeList = getData(FILE_ANIME);
$animeMap = array();
foreach ($animeList as $a) { $animeMap[$a['id']] = $a['title']; }

$schedule = getData(FILE_SCHEDULE);
$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

// Sort by day order then time
$dayOrder = array_flip($days);
usort($schedule, function($a, $b) use ($dayOrder) {
    $da = isset($a['day']) && isset($dayOrder[$a['day']]) ? $dayOrder[$a['day']] : 99;
    $db = isset($b['day']) && isset($dayOrder[$b['day']]) ? $dayOrder[$b['day']] : 99;
    if ($da !== $db) { return $da - $db; }
    $ta = isset($a['time']) ? $a['time'] : '';
    $tb = isset($b['time']) ? $b['time'] : '';
    return strcmp($ta, $tb);
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'add') {
        $anime_id = isset($_POST['anime_id']) ? $_POST['anime_id'] : '';
        $day      = isset($_POST['day']) ? $_POST['day'] : 'Monday';
        $time     = isset($_POST['time']) ? $_POST['time'] : '18:00';
        $tz       = isset($_POST['timezone']) ? $_POST['timezone'] : 'Asia/Dhaka';
        if ($anime_id) {
            $newItem = array(
                'id'        => generateId($schedule, 'sch'),
                'anime_id'  => $anime_id,
                'day'       => $day,
                'time'      => $time,
                'timezone'  => $tz,
            );
            $schedule[] = $newItem;
            saveData(FILE_SCHEDULE, $schedule);
            setFlash('success', 'Schedule entry added.');
        }
        adminRedirect('schedule.php');
    } elseif ($action === 'delete') {
        $del_id = isset($_POST['id']) ? $_POST['id'] : '';
        $newList = array();
        foreach ($schedule as $s) {
            if ($s['id'] === $del_id) { continue; }
            $newList[] = $s;
        }
        saveData(FILE_SCHEDULE, $newList);
        setFlash('success', 'Schedule entry deleted.');
        adminRedirect('schedule.php');
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Add Schedule Entry</h2>
    <form method="POST" action="<?php e($adminUrl); ?>/schedule.php">
        <?php echo csrfField(); ?>
        <input type="hidden" name="action" value="add">
        <div class="form-row-3">
            <div class="form-group">
                <label>Anime</label>
                <select name="anime_id" required>
                    <option value="">-- Select Anime --</option>
                    <?php foreach ($animeList as $a): ?>
                        <option value="<?php e($a['id']); ?>"><?php e($a['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Day</label>
                <select name="day">
                    <?php foreach ($days as $d): ?>
                        <option value="<?php e($d); ?>"><?php e($d); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="time" value="18:00">
            </div>
        </div>
        <div class="form-group">
            <label>Timezone</label>
            <input type="text" name="timezone" value="Asia/Dhaka" placeholder="Asia/Dhaka">
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add to Schedule</button>
    </form>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Schedule List (<?php echo count($schedule); ?>)</h2>
    <?php if (empty($schedule)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:40px;">No schedule entries yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Anime</th>
                    <th>Timezone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedule as $s): ?>
                <tr>
                    <td><?php e(isset($s['day']) ? $s['day'] : '-'); ?></td>
                    <td><?php e(isset($s['time']) ? $s['time'] : '-'); ?></td>
                    <td><?php e(isset($animeMap[$s['anime_id']]) ? $animeMap[$s['anime_id']] : '(deleted)'); ?></td>
                    <td><?php e(isset($s['timezone']) ? $s['timezone'] : '-'); ?></td>
                    <td>
                        <form method="POST" action="<?php e($adminUrl); ?>/schedule.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php e($s['id']); ?>">
                            <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" data-confirm="Delete this entry?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
