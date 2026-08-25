<?php
// BDMovieHub - Admin Login Page
require_once __DIR__ . '/../config.php';

if (isLoggedIn()) { adminRedirect('index.php'); }

// Admin URL prefix (since this page doesn't include the admin header)
$adminUrl = BASE_URL . '/admin';

// Check rate limiting
list($isLocked, $retryAfter) = checkLoginLockout();

$error = '';
$lockoutMsg = '';

if ($isLocked) {
    $mins = ceil($retryAfter / 60);
    $secs = $retryAfter % 60;
    $lockoutMsg = 'Too many failed login attempts. Please try again in ' . $mins . ' min ' . $secs . ' sec.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrf()) {
        $error = 'Security token expired. Please refresh the page and try again.';
    } else {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } elseif (login($username, $password)) {
            clearFailedLogins();
            adminRedirect('index.php');
        } else {
            recordFailedLogin();
            $error = 'Invalid credentials. Try again.';
            // Re-check lockout after recording the failed attempt
            list($isLocked, $retryAfter) = checkLoginLockout();
            if ($isLocked) {
                $mins = ceil($retryAfter / 60);
                $lockoutMsg = 'Too many failed login attempts. Please try again in ' . $mins . ' minutes.';
            } else {
                // Compute remaining attempts
                $attemptsFile = getData(FILE_LOGIN_ATTEMPTS);
                $ip = clientIp();
                $recentAttempts = isset($attemptsFile[$ip]) ? $attemptsFile[$ip] : array();
                // Count only attempts in the lockout window
                $now = time();
                $recentCount = 0;
                foreach ($recentAttempts as $t) {
                    if ($now - $t < LOGIN_LOCKOUT_SECONDS) { $recentCount++; }
                }
                $attemptsLeft = LOGIN_MAX_ATTEMPTS - $recentCount;
                if ($attemptsLeft > 0 && $attemptsLeft < LOGIN_MAX_ATTEMPTS) {
                    $error = 'Invalid credentials. ' . $attemptsLeft . ' attempt(s) remaining before lockout.';
                }
            }
        }
    }
}

// Generate CSRF token for the form
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BDMovieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid #2a2a3e;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .login-logo {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .login-logo span { color: #469AFF; }
        .login-subtitle {
            text-align: center;
            color: #a0a0b8;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #a0a0b8;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .input-group { position: relative; }
        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b6b80;
        }
        .form-group input {
            width: 100%;
            background: #0a0a0f;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 12px 14px 12px 40px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group input:focus { outline: none; border-color: #469AFF; }
        .btn-login {
            width: 100%;
            background: #469AFF;
            color: #fff;
            border: none;
            padding: 13px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-login:hover { background: #2d7dd2; }
        .error-msg {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid #e74c3c;
            color: #e74c3c;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .hint-box {
            background: rgba(70, 154, 255, 0.1);
            border: 1px solid #469AFF;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #a0a0b8;
            margin-top: 20px;
            text-align: center;
        }
        .hint-box strong { color: #469AFF; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #a0a0b8;
            font-size: 13px;
            text-decoration: none;
        }
        .back-link:hover { color: #469AFF; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">BD<span>Movie</span>Hub</div>
        <div class="login-subtitle">Admin Panel Login</div>

        <?php if ($lockoutMsg): ?>
            <div class="error-msg" style="background:rgba(255, 165, 0, 0.1); border-color:#ffa502; color:#ffa502;">
                <i class="fas fa-lock"></i> <?php e($lockoutMsg); ?>
            </div>
        <?php elseif ($error): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php e($error); ?></div>
        <?php endif; ?>

        <?php if (!$isLocked): ?>
        <form method="POST" action="<?php e($adminUrl); ?>/login.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label>Username</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" required autofocus value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        <?php endif; ?>

        <a href="<?php e(BASE_URL); ?>/index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Site</a>
    </div>
</body>
</html>
