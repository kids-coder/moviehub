<?php
// BDMovieHub - Admin Genres Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'categories';
$pageTitle = 'Manage Genres';

$errors = array();
$genres = getData(FILE_GENRES);

// Handle Add Genre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $newGenre = isset($_POST['genre']) ? trim($_POST['genre']) : '';
        if ($newGenre === '') {
            $errors[] = 'Genre name is required.';
        } elseif (in_array($newGenre, $genres)) {
            $errors[] = 'Genre already exists.';
        } else {
            $genres[] = $newGenre;
            sort($genres);
            if (saveData(FILE_GENRES, $genres)) {
                setFlash('success', 'Genre added successfully.');
                adminRedirect('genres.php');
            } else {
                $errors[] = 'Failed to save genre.';
            }
        }
    } elseif ($action === 'delete') {
        $delGenre = isset($_POST['genre']) ? $_POST['genre'] : '';
        $genres = array_filter($genres, function($g) use ($delGenre) {
            return $g !== $delGenre;
        });
        $genres = array_values($genres);
        if (saveData(FILE_GENRES, $genres)) {
            setFlash('success', 'Genre deleted successfully.');
            adminRedirect('genres.php');
        } else {
            $errors[] = 'Failed to delete genre.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Manage Genres</h2>
        <a href="<?php e($adminUrl); ?>/categories.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to Categories</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Add New Genre Form -->
    <div style="background:#0a0a0f; border:1px solid #2a2a3e; border-radius:12px; padding:20px; margin-bottom:24px;">
        <h3 style="font-size:16px; margin:0 0 16px; color:#fff;">Add New Genre</h3>
        <form method="POST" action="<?php e($adminUrl); ?>/genres.php" style="display:flex; gap:10px; align-items:flex-end;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add">
            <div class="form-group" style="margin:0; flex:1;">
                <label>Genre Name <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="genre" required placeholder="e.g., Western, Musical, War..." style="margin:0;">
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="margin-bottom:0;"><i class="fas fa-plus"></i> Add Genre</button>
        </form>
    </div>

    <!-- Existing Genres List -->
    <h3 style="font-size:16px; margin-bottom:16px;">Existing Genres (<?php echo count($genres); ?>)</h3>
    
    <?php if (empty($genres)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No genres yet. Add one above.</p>
    <?php else: ?>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <?php foreach ($genres as $g): ?>
                <div style="background:#1a1a2e; border:1px solid #2a2a3e; border-radius:8px; padding:10px 16px; display:flex; align-items:center; gap:10px;">
                    <span style="color:#fff; font-size:14px;"><?php e($g); ?></span>
                    <form method="POST" action="<?php e($adminUrl); ?>/genres.php" style="display:inline;" onsubmit="return confirm('Delete genre \'<?php e($g); ?>\'?');">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="genre" value="<?php e($g); ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete Genre" style="padding:4px 8px;"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top:24px; padding:16px; background:rgba(70,154,255,0.05); border:1px solid rgba(70,154,255,0.2); border-radius:8px;">
        <h4 style="font-size:13px; color:#469AFF; margin:0 0 8px;"><i class="fas fa-info-circle"></i> How Genres Work</h4>
        <ul style="margin:0; padding-left:20px; color:#a0a0b8; font-size:12px; line-height:1.6;">
            <li>Genres added here will appear as checkboxes when adding/editing Movies and Anime.</li>
            <li>Genres are also automatically collected from existing movies/anime.</li>
            <li>Deleting a genre here won't remove it from existing movies - it just won't show in the selection list for new entries.</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
