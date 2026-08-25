<?php
// BDMovieHub - Setup Script
// Run this ONCE after uploading to install sample data.
// Access via: https://yoursite.com/setup.php
// After successful setup, DELETE this file for security.

require_once __DIR__ . '/config.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$done = false;
$message = '';
$logs = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'install_sample') {
        // Sample Movies
        $sampleMovies = array(
            array(
                'id' => 'm1', 'title' => 'Inception', 'slug' => 'inception',
                'poster' => 'https://via.placeholder.com/300x450/469AFF/ffffff?text=Inception',
                'banner' => 'https://via.placeholder.com/1600x600/469AFF/ffffff?text=Inception',
                'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a CEO.',
                'year' => '2010', 'genre' => array('Action', 'Sci-Fi', 'Thriller'),
                'rating' => '8.8', 'duration' => '2h 28m', 'quality' => 'HD',
                'status' => 'published', 'trailer' => '', 'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'download_url' => '', 'featured' => true, 'created_at' => '2024-01-01',
            ),
            array(
                'id' => 'm2', 'title' => 'The Dark Knight', 'slug' => 'the-dark-knight',
                'poster' => 'https://via.placeholder.com/300x450/1a1a2e/ffffff?text=Dark+Knight',
                'banner' => 'https://via.placeholder.com/1600x600/1a1a2e/ffffff?text=Dark+Knight',
                'description' => 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.',
                'year' => '2008', 'genre' => array('Action', 'Crime', 'Drama'),
                'rating' => '9.0', 'duration' => '2h 32m', 'quality' => 'FHD',
                'status' => 'published', 'trailer' => '', 'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'download_url' => '', 'featured' => true, 'created_at' => '2024-01-02',
            ),
            array(
                'id' => 'm3', 'title' => 'Interstellar', 'slug' => 'interstellar',
                'poster' => 'https://via.placeholder.com/300x450/469AFF/ffffff?text=Interstellar',
                'banner' => 'https://via.placeholder.com/1600x600/469AFF/ffffff?text=Interstellar',
                'description' => 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.',
                'year' => '2014', 'genre' => array('Adventure', 'Drama', 'Sci-Fi'),
                'rating' => '8.6', 'duration' => '2h 49m', 'quality' => 'HD',
                'status' => 'published', 'trailer' => '', 'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'download_url' => '', 'featured' => false, 'created_at' => '2024-01-03',
            ),
            array(
                'id' => 'm4', 'title' => 'Spider-Man: No Way Home', 'slug' => 'spider-man-no-way-home',
                'poster' => 'https://via.placeholder.com/300x450/FF6B6B/ffffff?text=Spider-Man',
                'banner' => 'https://via.placeholder.com/1600x600/FF6B6B/ffffff?text=Spider-Man',
                'description' => 'With Spider-Man\'s identity now revealed, Peter asks Doctor Strange for help. When a spell goes wrong, the multiverse is broken open.',
                'year' => '2021', 'genre' => array('Action', 'Adventure', 'Sci-Fi'),
                'rating' => '8.2', 'duration' => '2h 28m', 'quality' => 'HD',
                'status' => 'published', 'trailer' => '', 'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'download_url' => '', 'featured' => true, 'created_at' => '2024-01-04',
            ),
            array(
                'id' => 'm5', 'title' => 'Avatar: The Way of Water', 'slug' => 'avatar-the-way-of-water',
                'poster' => 'https://via.placeholder.com/300x450/469AFF/ffffff?text=Avatar+2',
                'banner' => 'https://via.placeholder.com/1600x600/469AFF/ffffff?text=Avatar+2',
                'description' => 'Jake Sully lives with his newfound family formed on the extrasolar moon Pandora. Once a familiar threat returns to finish what was previously started.',
                'year' => '2022', 'genre' => array('Action', 'Adventure', 'Fantasy'),
                'rating' => '7.6', 'duration' => '3h 12m', 'quality' => '4K',
                'status' => 'published', 'trailer' => '', 'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'download_url' => '', 'featured' => false, 'created_at' => '2024-01-05',
            ),
        );

        // Sample Anime
        $sampleAnime = array(
            array(
                'id' => 'a1', 'title' => 'Naruto', 'slug' => 'naruto',
                'poster' => 'https://via.placeholder.com/300x450/9b59b6/ffffff?text=Naruto',
                'banner' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=Naruto',
                'description' => 'Naruto Uzumaki, a young ninja, seeks recognition from his peers while dreaming of becoming the Hokage, the leader of his village.',
                'genre' => array('Action', 'Adventure', 'Fantasy'),
                'rating' => '8.4', 'status' => 'completed', 'episode_count' => 5,
                'aired' => '2002-10', 'studio' => 'Pierrot',
                'status_pub' => 'published', 'featured' => true, 'created_at' => '2024-01-01',
            ),
            array(
                'id' => 'a2', 'title' => 'One Piece', 'slug' => 'one-piece',
                'poster' => 'https://via.placeholder.com/300x450/9b59b6/ffffff?text=One+Piece',
                'banner' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=One+Piece',
                'description' => 'Monkey D. Luffy sets off on an adventure with his pirate crew in hopes of finding the greatest treasure ever, known as the "One Piece."',
                'genre' => array('Action', 'Adventure', 'Comedy'),
                'rating' => '8.9', 'status' => 'ongoing', 'episode_count' => 5,
                'aired' => '1999-10', 'studio' => 'Toei Animation',
                'status_pub' => 'published', 'featured' => true, 'created_at' => '2024-01-02',
            ),
            array(
                'id' => 'a3', 'title' => 'Demon Slayer', 'slug' => 'demon-slayer',
                'poster' => 'https://via.placeholder.com/300x450/9b59b6/ffffff?text=Demon+Slayer',
                'banner' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=Demon+Slayer',
                'description' => 'A family is attacked by demons and only two members survive - Tanjiro and his sister Nezuko, who is turning into a demon slowly.',
                'genre' => array('Action', 'Fantasy', 'Horror'),
                'rating' => '8.7', 'status' => 'ongoing', 'episode_count' => 5,
                'aired' => '2019-04', 'studio' => 'ufotable',
                'status_pub' => 'published', 'featured' => false, 'created_at' => '2024-01-03',
            ),
            array(
                'id' => 'a4', 'title' => 'Attack on Titan', 'slug' => 'attack-on-titan',
                'poster' => 'https://via.placeholder.com/300x450/9b59b6/ffffff?text=AoT',
                'banner' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=Attack+on+Titan',
                'description' => 'After his hometown is destroyed and his mother is killed, Eren vows to cleanse the earth of the giant humanoid Titans.',
                'genre' => array('Action', 'Drama', 'Fantasy'),
                'rating' => '9.0', 'status' => 'completed', 'episode_count' => 5,
                'aired' => '2013-04', 'studio' => 'Wit Studio',
                'status_pub' => 'published', 'featured' => true, 'created_at' => '2024-01-04',
            ),
            array(
                'id' => 'a5', 'title' => 'Jujutsu Kaisen', 'slug' => 'jujutsu-kaisen',
                'poster' => 'https://via.placeholder.com/300x450/9b59b6/ffffff?text=JJK',
                'banner' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=Jujutsu+Kaisen',
                'description' => 'A boy swallows a cursed talisman - the finger of a demon - and becomes cursed himself. He enters a shaman school to be able to locate the demon\'s other body parts.',
                'genre' => array('Action', 'Fantasy', 'Horror'),
                'rating' => '8.6', 'status' => 'ongoing', 'episode_count' => 5,
                'aired' => '2020-10', 'studio' => 'MAPPA',
                'status_pub' => 'published', 'featured' => false, 'created_at' => '2024-01-05',
            ),
        );

        // Sample Episodes (5 per anime)
        $sampleEpisodes = array();
        $epId = 1;
        foreach ($sampleAnime as $a) {
            for ($i = 1; $i <= 5; $i++) {
                $sampleEpisodes[] = array(
                    'id'             => 'ep' . $epId,
                    'anime_id'       => $a['id'],
                    'episode_number' => $i,
                    'title'          => 'Episode ' . $i,
                    'stream_url'     => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                    'thumbnail'      => 'https://via.placeholder.com/300x170/9b59b6/ffffff?text=EP+' . $i,
                    'created_at'     => '2024-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                );
                $epId++;
            }
        }

        // Sample Pages
        $samplePages = array(
            array(
                'id' => 'pg1', 'title' => 'About Us', 'slug' => 'about',
                'content' => '<h1>About BDMovieHub</h1><p>Welcome to BDMovieHub, your premier destination for streaming movies and anime for free. We offer a vast collection of content across multiple genres, from Hollywood blockbusters to the latest anime series.</p><p>Our mission is to provide high-quality entertainment to viewers worldwide, with a focus on user experience and accessibility. Whether you are looking for action-packed movies or heartwarming anime, we have something for everyone.</p><h2>What We Offer</h2><ul><li>HD quality movie streaming</li><li>Latest anime episodes with subtitles</li><li>User-friendly interface</li><li>Regular content updates</li></ul>',
                'status' => 'published', 'created_at' => '2024-01-01',
            ),
            array(
                'id' => 'pg2', 'title' => 'Contact Us', 'slug' => 'contact',
                'content' => '<h1>Contact Us</h1><p>Have questions or feedback? We would love to hear from you. Reach out to us through the following channels:</p><ul><li>Email: contact@bdmoviehub.com</li><li>Discord: discord.gg/bdmoviehub</li><li>Telegram: @bdmoviehub</li></ul><p>We typically respond within 24-48 hours. For urgent matters, please use the email option.</p>',
                'status' => 'published', 'created_at' => '2024-01-01',
            ),
            array(
                'id' => 'pg3', 'title' => 'Privacy Policy', 'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>This privacy policy describes how BDMovieHub collects, uses, and protects your information when you visit our website.</p><h2>Information We Collect</h2><p>We do not require registration to browse or watch content. We may collect anonymous usage statistics to improve our service.</p><h2>Cookies</h2><p>We use localStorage to remember your theme preferences and favorites. No tracking cookies are used.</p><h2>Third-Party Content</h2><p>Video content is hosted by third-party providers. We are not responsible for the content or availability of these streams.</p>',
                'status' => 'published', 'created_at' => '2024-01-01',
            ),
        );

        // Sample Slides
        $sampleSlides = array(
            array(
                'id' => 'sl1', 'title' => 'Watch Inception in HD',
                'image' => 'https://via.placeholder.com/1600x600/469AFF/ffffff?text=Inception+Now+Streaming',
                'url' => 'movie.php?slug=inception', 'order' => 1,
            ),
            array(
                'id' => 'sl2', 'title' => 'Latest Anime: Demon Slayer',
                'image' => 'https://via.placeholder.com/1600x600/9b59b6/ffffff?text=Demon+Slayer+New+Episodes',
                'url' => 'anime-watch.php?slug=demon-slayer', 'order' => 2,
            ),
            array(
                'id' => 'sl3', 'title' => 'Spider-Man: No Way Home',
                'image' => 'https://via.placeholder.com/1600x600/FF6B6B/ffffff?text=Spider-Man+No+Way+Home',
                'url' => 'movie.php?slug=spider-man-no-way-home', 'order' => 3,
            ),
        );

        // Sample Schedule
        $sampleSchedule = array(
            array('id' => 'sch1', 'anime_id' => 'a2', 'day' => 'Saturday',  'time' => '18:00', 'timezone' => 'Asia/Dhaka'),
            array('id' => 'sch2', 'anime_id' => 'a3', 'day' => 'Sunday',    'time' => '20:00', 'timezone' => 'Asia/Dhaka'),
            array('id' => 'sch3', 'anime_id' => 'a5', 'day' => 'Thursday',  'time' => '23:00', 'timezone' => 'Asia/Dhaka'),
            array('id' => 'sch4', 'anime_id' => 'a1', 'day' => 'Wednesday', 'time' => '19:30', 'timezone' => 'Asia/Dhaka'),
        );

        // Sample Featured (only featured movies/anime)
        $sampleFeatured = array();
        foreach ($sampleMovies as $m) { if (!empty($m['featured'])) { $sampleFeatured[] = array('id' => $m['id'], 'type' => 'movie'); } }
        foreach ($sampleAnime as $a) { if (!empty($a['featured'])) { $sampleFeatured[] = array('id' => $a['id'], 'type' => 'anime'); } }

        // Save all
        saveData(FILE_MOVIES, $sampleMovies);
        $logs[] = 'Created ' . count($sampleMovies) . ' sample movies';
        saveData(FILE_ANIME, $sampleAnime);
        $logs[] = 'Created ' . count($sampleAnime) . ' sample anime';
        saveData(FILE_EPISODES, $sampleEpisodes);
        $logs[] = 'Created ' . count($sampleEpisodes) . ' sample episodes';
        saveData(FILE_PAGES, $samplePages);
        $logs[] = 'Created ' . count($samplePages) . ' sample pages';
        saveData(FILE_SLIDES, $sampleSlides);
        $logs[] = 'Created ' . count($sampleSlides) . ' sample slides';
        saveData(FILE_SCHEDULE, $sampleSchedule);
        $logs[] = 'Created ' . count($sampleSchedule) . ' schedule entries';
        saveData(FILE_FEATURED, $sampleFeatured);
        $logs[] = 'Created ' . count($sampleFeatured) . ' featured items';

        $done = true;
        $message = 'Sample data installed successfully! You can now browse the site.';
    } elseif ($action === 'clear_all') {
        saveData(FILE_MOVIES, array());
        saveData(FILE_ANIME, array());
        saveData(FILE_EPISODES, array());
        saveData(FILE_SLIDES, array());
        saveData(FILE_SCHEDULE, array());
        saveData(FILE_FEATURED, array());
        // Keep pages
        $logs[] = 'Cleared all movies, anime, episodes, slides, schedule, featured';
        $done = true;
        $message = 'All content data cleared. Pages and users preserved.';
    } elseif ($action === 'change_password') {
        $newUser = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';
        $newPass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPass = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if ($newUser === '' || strlen($newUser) < 3) {
            $message = 'Username must be at least 3 characters.';
        } elseif ($newPass === '' || strlen($newPass) < 6) {
            $message = 'Password must be at least 6 characters.';
        } elseif ($newPass !== $confirmPass) {
            $message = 'Passwords do not match.';
        } else {
            $users = getData(FILE_USERS);
            $updated = false;
            foreach ($users as $i => $u) {
                if (isset($u['role']) && $u['role'] === 'admin') {
                    $users[$i]['username'] = $newUser;
                    $users[$i]['password'] = password_hash($newPass, PASSWORD_DEFAULT);
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                saveData(FILE_USERS, $users);
                $logs[] = 'Admin credentials updated. Username: ' . $newUser;
                $logs[] = 'Password hashed with bcrypt.';
                $done = true;
                $message = 'Admin credentials updated successfully. You can now log in with your new username and password.';
            } else {
                $message = 'No admin user found. Run "Install Sample Data" first.';
            }
        }
    }
}

// Get current counts for display
$counts = array(
    'movies'   => count(getData(FILE_MOVIES)),
    'anime'    => count(getData(FILE_ANIME)),
    'episodes' => count(getData(FILE_EPISODES)),
    'pages'    => count(getData(FILE_PAGES)),
    'schedule' => count(getData(FILE_SCHEDULE)),
    'slides'   => count(getData(FILE_SLIDES)),
    'users'    => count(getData(FILE_USERS)),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - BDMovieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .setup-container {
            max-width: 720px;
            margin: 0 auto;
            background: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid #2a2a3e;
            border-radius: 16px;
            padding: 40px;
        }
        .logo { text-align: center; font-size: 32px; font-weight: 800; margin-bottom: 8px; }
        .logo span { color: #469AFF; }
        .subtitle { text-align: center; color: #a0a0b8; font-size: 14px; margin-bottom: 30px; }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }
        .status-item {
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .status-item .count { font-size: 24px; font-weight: 800; color: #469AFF; }
        .status-item .label { font-size: 11px; color: #a0a0b8; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        .action-card {
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .action-card h3 { font-size: 16px; margin-bottom: 6px; }
        .action-card p { font-size: 13px; color: #a0a0b8; margin-bottom: 14px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary { background: #469AFF; color: #fff; }
        .btn-primary:hover { background: #2d7dd2; }
        .btn-danger { background: #e74c3c; color: #fff; }
        .btn-danger:hover { background: #c0392b; }
        .btn-outline { background: transparent; color: #fff; border: 1px solid #2a2a3e; }
        .btn-outline:hover { border-color: #469AFF; }
        .message-box {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid #2ecc71;
            color: #2ecc71;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .log-list {
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 14px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #2ecc71;
        }
        .log-list div { margin-bottom: 4px; }
        .warning-box {
            background: rgba(255, 165, 2, 0.1);
            border: 1px solid #ffa502;
            color: #ffa502;
            padding: 14px 18px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
        }
        .links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        @media (max-width: 600px) {
            .status-grid { grid-template-columns: repeat(2, 1fr); }
            .setup-container { padding: 24px 16px; }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="logo">BD<span>Movie</span>Hub</div>
        <div class="subtitle">Setup &amp; Sample Data Installer</div>

        <div class="status-grid">
            <div class="status-item"><div class="count"><?php echo $counts['movies']; ?></div><div class="label">Movies</div></div>
            <div class="status-item"><div class="count"><?php echo $counts['anime']; ?></div><div class="label">Anime</div></div>
            <div class="status-item"><div class="count"><?php echo $counts['episodes']; ?></div><div class="label">Episodes</div></div>
            <div class="status-item"><div class="count"><?php echo $counts['pages']; ?></div><div class="label">Pages</div></div>
        </div>

        <?php if ($done): ?>
            <div class="message-box"><i class="fas fa-check-circle"></i> <?php e($message); ?></div>
            <?php if (!empty($logs)): ?>
                <div class="log-list">
                    <?php foreach ($logs as $log): ?>
                        <div><i class="fas fa-check"></i> <?php e($log); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="links">
                <a href="<?php e(BASE_URL); ?>/index.php" class="btn btn-primary"><i class="fas fa-home"></i> Visit Homepage</a>
                <a href="<?php e(BASE_URL); ?>/admin/login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
            </div>
        <?php else: ?>
            <div class="action-card">
                <h3><i class="fas fa-magic" style="color:#469AFF;"></i> Install Sample Data</h3>
                <p>This will populate your site with 5 sample movies, 5 anime (each with 5 episodes), 3 pages, 3 slides, and 4 schedule entries. Perfect for testing the site before adding real content.</p>
                <form method="POST" action="<?php e(BASE_URL); ?>/setup.php">
                    <input type="hidden" name="action" value="install_sample">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Install Sample Data</button>
                </form>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-trash" style="color:#e74c3c;"></i> Clear All Content</h3>
                <p>Removes all movies, anime, episodes, slides, and schedule entries. Pages and admin users are preserved. Use this if you want a fresh start.</p>
                <form method="POST" action="<?php e(BASE_URL); ?>/setup.php" onsubmit="return confirm('Are you sure? This will delete ALL movies, anime, and episodes.')">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-eraser"></i> Clear All Data</button>
                </form>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-key" style="color:#469AFF;"></i> Change Admin Credentials</h3>
                <p>Change the default admin username and password. <strong>Highly recommended</strong> after first install. The password is hashed with bcrypt before storage.</p>
                <form method="POST" action="<?php e(BASE_URL); ?>/setup.php">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; color:#a0a0b8; margin-bottom:6px;">New Username</label>
                        <input type="text" name="new_username" required minlength="3" maxlength="60" placeholder="admin" style="width:100%; padding:10px 14px; background:#0a0a0f; color:#fff; border:1px solid #2a2a3e; border-radius:8px; font-family:inherit; font-size:14px;">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; color:#a0a0b8; margin-bottom:6px;">New Password (min 6 chars)</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="********" style="width:100%; padding:10px 14px; background:#0a0a0f; color:#fff; border:1px solid #2a2a3e; border-radius:8px; font-family:inherit; font-size:14px;">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; color:#a0a0b8; margin-bottom:6px;">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="********" style="width:100%; padding:10px 14px; background:#0a0a0f; color:#fff; border:1px solid #2a2a3e; border-radius:8px; font-family:inherit; font-size:14px;">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Admin Credentials</button>
                </form>
            </div>

            <div class="warning-box">
                <strong><i class="fas fa-exclamation-triangle"></i> Security Notice:</strong>
                After setup is complete, please <strong>delete setup.php</strong> from your server to prevent unauthorized access.
            </div>

            <div class="links">
                <a href="<?php e(BASE_URL); ?>/index.php" class="btn btn-outline"><i class="fas fa-home"></i> Visit Homepage</a>
                <a href="<?php e(BASE_URL); ?>/admin/login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
