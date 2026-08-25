<?php
// BDMovieHub - Admin Category Add
require_once __DIR__ . '/../config.php';
$adminPage = 'categories';
$pageTitle = 'Add Category';

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';

    if ($name === '') { $errors[] = 'Name is required.'; }
    if ($slug === '') { $slug = slugify($name); }
    else { $slug = slugify($slug); }

    if (empty($errors)) {
        $categories = getData(FILE_CATEGORIES);
        // Check slug uniqueness
        foreach ($categories as $c) {
            if (isset($c['slug']) && $c['slug'] === $slug) {
                $slug = $slug . '-' . substr(md5(time()), 0, 4);
                break;
            }
        }
        $categories[] = array(
            'id'   => generateId($categories, 'cat'),
            'name' => $name,
            'slug' => $slug,
        );
        if (saveData(FILE_CATEGORIES, $categories)) {
            setFlash('success', 'Category added.');
            adminRedirect('categories.php');
        } else {
            $errors[] = 'Failed to save category.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Add Category</h2>
        <a href="<?php e($adminUrl); ?>/categories.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back</a>
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

    <form method="POST" action="<?php e($adminUrl); ?>/category-add.php">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Name <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug'], ENT_QUOTES) : ''; ?>" placeholder="auto-generated">
            </div>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save Category</button>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
