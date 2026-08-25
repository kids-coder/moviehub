<?php
// BDMovieHub - Admin Category Delete
require_once __DIR__ . '/../config.php';
$adminPage = 'categories';
$pageTitle = 'Delete Category';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    adminRedirect('categories.php');
}
requireCsrf();

$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
if ($id === '') {
    setFlash('error', 'Category ID required.');
    adminRedirect('categories.php');
}

$categories = getData(FILE_CATEGORIES);
$new = array();
$found = false;
foreach ($categories as $c) {
    if (isset($c['id']) && $c['id'] === $id) {
        $found = true;
    } else {
        $new[] = $c;
    }
}

if (!$found) {
    setFlash('error', 'Category not found.');
    adminRedirect('categories.php');
}

if (saveData(FILE_CATEGORIES, $new)) {
    setFlash('success', 'Category deleted.');
} else {
    setFlash('error', 'Failed to delete category.');
}
adminRedirect('categories.php');
