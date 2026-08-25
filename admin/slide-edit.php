<?php
// BDMovieHub - Admin Slide Edit
require_once __DIR__ . '/../config.php';
$adminPage = 'slides';
$pageTitle = 'Edit Slide';

$slides = getData(FILE_SLIDES);
$errors = array();

// Get slide ID from URL
$slideId = isset($_GET['id']) ? trim($_GET['id']) : '';
$slide = null;

// Find the slide
foreach ($slides as $s) {
    if ((isset($s['id']) ? $s['id'] : '') === $slideId) {
        $slide = $s;
        break;
    }
}

if (!$slide) {
    setFlash('error', 'Slide not found.');
    adminRedirect('slides.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $url   = isset($_POST['url']) ? trim($_POST['url']) : '#';
    $order = isset($_POST['order']) ? intval($_POST['order']) : 1;

    if ($title === '') { $errors[] = 'Title is required.'; }
    if ($image === '') { $errors[] = 'Image URL is required.'; }

    if (empty($errors)) {
        // Update the slide
        foreach ($slides as &$s) {
            if ((isset($s['id']) ? $s['id'] : '') === $slideId) {
                $s['title'] = $title;
                $s['image'] = $image;
                $s['url']   = $url;
                $s['order'] = $order;
                break;
            }
        }
        unset($s);
        
        if (saveData(FILE_SLIDES, $slides)) {
            setFlash('success', 'Slide updated successfully.');
            adminRedirect('slides.php');
        } else {
            $errors[] = 'Failed to save slide.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Edit Slide</h2>
        <a href="<?php e($adminUrl); ?>/slides.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to Slides</a>
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

    <form method="POST" action="<?php e($adminUrl); ?>/slide-edit.php?id=<?php e($slideId); ?>">
        <?php echo csrfField(); ?>
        <div class="form-group">
            <label>Title <span style="color:#e74c3c;">*</span></label>
            <input type="text" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES) : htmlspecialchars(isset($slide['title']) ? $slide['title'] : '', ENT_QUOTES); ?>">
        </div>
        <div class="form-group">
            <label>Image URL <span style="color:#e74c3c;">*</span></label>
            <input type="text" name="image" required value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image'], ENT_QUOTES) : htmlspecialchars(isset($slide['image']) ? $slide['image'] : '', ENT_QUOTES); ?>" placeholder="https://example.com/banner.jpg">
            <div class="hint">Recommended size: 1600x600 px</div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Link URL</label>
                <input type="text" name="url" value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url'], ENT_QUOTES) : htmlspecialchars(isset($slide['url']) ? $slide['url'] : '#', ENT_QUOTES); ?>" placeholder="index.php or https://...">
            </div>
            <div class="form-group">
                <label>Order</label>
                <input type="number" name="order" value="<?php echo isset($_POST['order']) ? intval($_POST['order']) : (isset($slide['order']) ? intval($slide['order']) : 1); ?>" min="1">
                <div class="hint">Lower numbers appear first.</div>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Update Slide</button>
            <a href="<?php e($adminUrl); ?>/slides.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
