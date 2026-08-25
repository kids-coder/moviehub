<?php
// BDMovieHub - Custom Page (slug-based)

require_once __DIR__ . '/config.php';

$pageSection = 'page';
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$pages = getData(FILE_PAGES);
$page = getBySlug($pages, $slug);

if (!$page || (isset($page['status']) && $page['status'] !== 'published')) {
    setFlash('error', 'Page not found.');
    redirect('index.php');
}

$pageTitle = isset($page['title']) ? $page['title'] : 'Page';
$pageContent = isset($page['content']) ? $page['content'] : '';

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 900px;">
        <h1 class="section-title" style="margin-bottom: 24px;"><?php e($pageTitle); ?></h1>
        <div class="page-content">
            <?php
                // Allow basic HTML content from admin (admin is trusted)
                echo $pageContent;
            ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
