<?php
// BDMovieHub - Admin Featured Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'featured';
$pageTitle = 'Featured Items';

$featured = getData(FILE_FEATURED);
$movies = getData(FILE_MOVIES);
$animeList = getData(FILE_ANIME);

$movieMap = array();
foreach ($movies as $m) { $movieMap[$m['id']] = $m; }
$animeMap = array();
foreach ($animeList as $a) { $animeMap[$a['id']] = $a; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $itemId = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';

    if ($action === 'add' && $itemId !== '' && ($type === 'movie' || $type === 'anime')) {
        // Check not already featured
        $exists = false;
        foreach ($featured as $f) {
            if (isset($f['id']) && $f['id'] === $itemId && isset($f['type']) && $f['type'] === $type) {
                $exists = true; break;
            }
        }
        if (!$exists) {
            $featured[] = array('id' => $itemId, 'type' => $type);
            saveData(FILE_FEATURED, $featured);
            setFlash('success', 'Item added to featured.');
        } else {
            setFlash('error', 'Item already featured.');
        }
        adminRedirect('featured.php');
    }
    if ($action === 'remove' && $itemId !== '') {
        $new = array();
        foreach ($featured as $f) {
            if (!(isset($f['id']) && $f['id'] === $itemId)) {
                $new[] = $f;
            }
        }
        saveData(FILE_FEATURED, $new);
        setFlash('success', 'Item removed from featured.');
        adminRedirect('featured.php');
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Add to Featured</h2>
    <form method="POST" action="<?php e($adminUrl); ?>/featured.php">
        <?php echo csrfField(); ?>
        <input type="hidden" name="action" value="add">
        <div class="form-row-3">
            <div class="form-group">
                <label>Type</label>
                <select name="type" id="feat-type">
                    <option value="movie">Movie</option>
                    <option value="anime">Anime</option>
                </select>
            </div>
            <div class="form-group">
                <label>Select Item</label>
                <select name="item_id" id="feat-item">
                    <optgroup label="Movies" id="opt-movies">
                        <?php foreach ($movies as $m): ?>
                            <option value="<?php e($m['id']); ?>" data-type="movie"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?> (<?php e(isset($m['year']) ? $m['year'] : ''); ?>)</option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Anime" id="opt-anime" style="display:none;">
                        <?php foreach ($animeList as $a): ?>
                            <option value="<?php e($a['id']); ?>" data-type="anime"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-admin btn-admin-success" style="width:100%;"><i class="fas fa-plus"></i> Add</button>
            </div>
        </div>
    </form>
    <script>
        document.getElementById('feat-type').addEventListener('change', function () {
            var t = this.value;
            document.getElementById('opt-movies').style.display = (t === 'movie') ? 'block' : 'none';
            document.getElementById('opt-anime').style.display = (t === 'anime') ? 'block' : 'none';
        });
    </script>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Currently Featured (<?php echo count($featured); ?>)</h2>
    <?php if (empty($featured)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No featured items yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Type</th><th>Title</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($featured as $f):
                    $type = isset($f['type']) ? $f['type'] : '';
                    $id = isset($f['id']) ? $f['id'] : '';
                    $item = ($type === 'anime') ? (isset($animeMap[$id]) ? $animeMap[$id] : null) : (isset($movieMap[$id]) ? $movieMap[$id] : null);
                    if (!$item) { continue; }
                ?>
                <tr>
                    <td>
                        <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $type === 'anime' ? 'rgba(155,89,182,0.15)' : 'rgba(70,154,255,0.15)'; ?>; color:<?php echo $type === 'anime' ? '#9b59b6' : '#469AFF'; ?>;">
                            <?php e(ucfirst($type)); ?>
                        </span>
                    </td>
                    <td><?php e(isset($item['title']) ? $item['title'] : 'Untitled'); ?></td>
                    <td>
                        <form method="POST" action="<?php e($adminUrl); ?>/featured.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="item_id" value="<?php e($id); ?>">
                            <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" data-confirm="Remove from featured?"><i class="fas fa-times"></i></button>
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
