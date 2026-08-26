<?php
// BDMovieHub - Admin Header Partial
// Included by all admin pages. Expects $adminPage variable to mark active sidebar link.
// $adminPage values: dashboard, movies, anime, episodes, pages, schedule, settings, users

if (!defined('BDMOVIEHUB')) { exit('Direct access denied'); }

requireLogin();

$adminPage = isset($adminPage) ? $adminPage : 'dashboard';
$pageTitle = isset($pageTitle) ? $pageTitle : 'Admin';
$user = currentUser();
$settings = getSettings();
$counts = array(
    'movies'   => count(getData(FILE_MOVIES)),
    'anime'    => count(getData(FILE_ANIME)),
    'episodes' => count(getData(FILE_EPISODES)),
    'pages'    => count(getData(FILE_PAGES)),
);

// Pending-work badge for the Messages sidebar link
$__menuPending = 0;
foreach (getData(FILE_COMMENTS) as $__mc) {
    if ((isset($__mc['status']) ? $__mc['status'] : 'pending') === 'pending') { $__menuPending++; }
}
if (file_exists(DATA_DIR . '/reports.json')) {
    $__mraw = @file_get_contents(DATA_DIR . '/reports.json');
    $__mdec = $__mraw ? json_decode($__mraw, true) : array();
    if (is_array($__mdec)) {
        foreach ($__mdec as $__mr) { if (!(isset($__mr['resolved']) && $__mr['resolved'])) { $__menuPending++; } }
    }
}

// Admin URL prefix (BASE_URL + /admin)
$adminUrl = BASE_URL . '/admin';

// SVG favicon properly URL-encoded
$faviconSvg = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20100%20100'%3E%3Crect%20width='100'%20height='100'%20rx='20'%20fill='%23469AFF'/%3E%3Ctext%20x='50'%20y='68'%20font-size='60'%20text-anchor='middle'%20fill='white'%20font-family='Arial'%20font-weight='bold'%3EB%3C/text%3E%3C/svg%3E";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e($pageTitle); ?> - Admin - <?php e($settings['site_name']); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php e($faviconSvg); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php e(ASSETS_URL); ?>/css/style.css">
    <style>
        /* Admin-specific styles */
        body { background: #0a0a0f; color: #fff; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px;
            background: #151520;
            border-right: 1px solid #2a2a3e;
            padding: 20px 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 0 24px 24px;
            font-size: 20px;
            font-weight: 800;
            border-bottom: 1px solid #2a2a3e;
            margin-bottom: 16px;
            color: #fff;
        }
        .sidebar-logo span { color: #469AFF; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 24px;
            color: #a0a0b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover { background: #1a1a2e; color: #fff; }
        .sidebar-link.active {
            background: #1a1a2e;
            color: #469AFF;
            border-left-color: #469AFF;
        }
        .sidebar-link i { width: 18px; text-align: center; }
        .sidebar-section {
            padding: 12px 24px 6px;
            color: #6b6b80;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .admin-main {
            flex: 1;
            margin-left: 240px;
            padding: 30px;
            min-width: 0;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .admin-header h1 { font-size: 24px; font-weight: 700; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #a0a0b8;
        }
        .user-info .avatar {
            width: 36px; height: 36px;
            background: #469AFF;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .admin-card {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s;
        }
        .stat-card:hover { border-color: #469AFF; }
        .stat-card .icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
        }
        .stat-card .icon.blue { background: rgba(70, 154, 255, 0.15); color: #469AFF; }
        .stat-card .icon.purple { background: rgba(155, 89, 182, 0.15); color: #9b59b6; }
        .stat-card .icon.green { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }
        .stat-card .icon.orange { background: rgba(255, 165, 2, 0.15); color: #ffa502; }
        .stat-card .value { font-size: 28px; font-weight: 800; line-height: 1; }
        .stat-card .label { color: #a0a0b8; font-size: 13px; margin-top: 4px; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table th, .data-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #2a2a3e;
        }
        .data-table th {
            color: #6b6b80;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .data-table tr:hover { background: rgba(255,255,255,0.02); }
        .data-table img.thumb {
            width: 40px; height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .table-actions { display: flex; gap: 6px; }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #a0a0b8;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 10px 14px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #469AFF;
        }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-group .hint { font-size: 11px; color: #6b6b80; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .checkbox-row { display: flex; align-items: center; gap: 8px; }
        .checkbox-row input { width: auto; }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-admin-primary { background: #469AFF; color: #fff; }
        .btn-admin-primary:hover { background: #2d7dd2; color: #fff; }
        .btn-admin-success { background: #2ecc71; color: #fff; }
        .btn-admin-success:hover { background: #27ae60; color: #fff; }
        .btn-admin-danger { background: #e74c3c; color: #fff; }
        .btn-admin-danger:hover { background: #c0392b; color: #fff; }
        .btn-admin-outline { background: transparent; color: #fff; border: 1px solid #2a2a3e; }
        .btn-admin-outline:hover { border-color: #469AFF; color: #469AFF; }
        .btn-admin-sm { padding: 5px 10px; font-size: 11px; }

        .genre-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
        }
        .genre-checkboxes label {
            display: flex;
            align-items: center;
            gap: 4px;
            background: #1a1a2e;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 12px;
            color: #a0a0b8;
            cursor: pointer;
        }
        .genre-checkboxes input { width: auto; }

        .mobile-sidebar-toggle {
            display: none;
            position: fixed;
            top: 14px;
            left: 14px;
            z-index: 200;
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; padding: 60px 16px 30px; }
            .mobile-sidebar-toggle { display: block; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }
        .data-table-wrap { overflow-x: auto; }
    </style>
</head>
<body>
<button class="mobile-sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="fas fa-bars"></i>
</button>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">BD<span>Movie</span>Hub</div>
        <div class="sidebar-section">Main</div>
        <a href="<?php e($adminUrl); ?>/index.php" class="sidebar-link <?php echo $adminPage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php e($adminUrl); ?>/analytics.php" class="sidebar-link <?php echo $adminPage === 'analytics' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
        <a href="<?php e($adminUrl); ?>/movies.php" class="sidebar-link <?php echo $adminPage === 'movies' ? 'active' : ''; ?>">
            <i class="fas fa-film"></i> Movies
        </a>
        <a href="<?php e($adminUrl); ?>/anime.php" class="sidebar-link <?php echo $adminPage === 'anime' ? 'active' : ''; ?>">
            <i class="fas fa-tv"></i> Anime
        </a>
        <a href="<?php e($adminUrl); ?>/episodes.php" class="sidebar-link <?php echo $adminPage === 'episodes' ? 'active' : ''; ?>">
            <i class="fas fa-list-ol"></i> Episodes
        </a>
        <a href="<?php e($adminUrl); ?>/featured.php" class="sidebar-link <?php echo $adminPage === 'featured' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> Featured
        </a>
        <a href="<?php e($adminUrl); ?>/slides.php" class="sidebar-link <?php echo $adminPage === 'slides' ? 'active' : ''; ?>">
            <i class="fas fa-image"></i> Hero Slides
        </a>
        <div class="sidebar-section">Content</div>
        <a href="<?php e($adminUrl); ?>/pages.php" class="sidebar-link <?php echo $adminPage === 'pages' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i> Pages
        </a>
        <a href="<?php e($adminUrl); ?>/categories.php" class="sidebar-link <?php echo $adminPage === 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="<?php e($adminUrl); ?>/genres.php" class="sidebar-link <?php echo $adminPage === 'genres' ? 'active' : ''; ?>">
            <i class="fas fa-theater-masks"></i> Genres
        </a>
        <a href="<?php e($adminUrl); ?>/schedule.php" class="sidebar-link <?php echo $adminPage === 'schedule' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Schedule
        </a>
        <a href="<?php e($adminUrl); ?>/comments.php" class="sidebar-link <?php echo $adminPage === 'comments' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> Messages
            <?php if ($__menuPending > 0): ?><span style="margin-left:auto; background:#e74c3c; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:10px;"><?php echo $__menuPending > 99 ? '99+' : $__menuPending; ?></span><?php endif; ?>
        </a>
        <a href="<?php e($adminUrl); ?>/requests.php" class="sidebar-link <?php echo $adminPage === 'requests' ? 'active' : ''; ?>">
            <i class="fas fa-inbox"></i> Title Requests
        </a>
        <div class="sidebar-section">System</div>
        <?php $__isAdminUser = $user && (isset($user['role']) ? $user['role'] : '') === 'admin'; ?>
        <?php if ($__isAdminUser): ?>
        <a href="<?php e($adminUrl); ?>/import.php" class="sidebar-link <?php echo $adminPage === 'import' ? 'active' : ''; ?>">
            <i class="fas fa-file-import"></i> Import
        </a>
        <a href="<?php e($adminUrl); ?>/export.php" class="sidebar-link <?php echo $adminPage === 'export' ? 'active' : ''; ?>">
            <i class="fas fa-file-export"></i> Export
        </a>
        <a href="<?php e($adminUrl); ?>/backup.php" class="sidebar-link <?php echo $adminPage === 'backup' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Backup
        </a>
        <a href="<?php e($adminUrl); ?>/settings.php" class="sidebar-link <?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="<?php e($adminUrl); ?>/users.php" class="sidebar-link <?php echo $adminPage === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <?php endif; ?>
        <a href="<?php e($adminUrl); ?>/logout.php" class="sidebar-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <div style="padding: 20px 24px; margin-top: 20px; border-top: 1px solid #2a2a3e;">
            <a href="<?php e(BASE_URL); ?>/index.php" class="sidebar-link" style="border-left: none;">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1><?php e($pageTitle); ?></h1>
            <div class="user-info">
                <div class="avatar"><?php e(strtoupper(substr($user ? $user['username'] : 'A', 0, 1))); ?></div>
                <div>
                    <div style="color:#fff; font-weight:600;"><?php e($user ? $user['username'] : 'Admin'); ?></div>
                    <div style="font-size:11px;"><?php e($user ? ucfirst($user['role']) : ''); ?></div>
                </div>
            </div>
        </div>

        <?php $flash = getFlash(); ?>
        <?php if ($flash): ?>
            <div class="admin-card" style="background:<?php echo $flash['type'] === 'success' ? 'rgba(46,204,113,0.1)' : ($flash['type'] === 'error' ? 'rgba(231,76,60,0.1)' : 'rgba(70,154,255,0.1)'); ?>; border-color:<?php echo $flash['type'] === 'success' ? '#2ecc71' : ($flash['type'] === 'error' ? '#e74c3c' : '#469AFF'); ?>; padding:14px 18px; margin-bottom:20px;">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"
                   style="color:<?php echo $flash['type'] === 'success' ? '#2ecc71' : ($flash['type'] === 'error' ? '#e74c3c' : '#469AFF'); ?>; margin-right:8px;"></i>
                <?php e($flash['msg']); ?>
            </div>
        <?php endif; ?>
