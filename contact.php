<?php
// BDMovieHub - Contact page
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Contact Us';

$settings = getSettings();
$sent = false;
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token expired. Please try again.');
        redirect('contact.php');
    }
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($name === '') { $errors[] = 'Name is required.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
    if ($message === '') { $errors[] = 'Message cannot be empty.'; }

    if (empty($errors)) {
        // Save message to a contacts log file (no database)
        $logFile = DATA_DIR . '/contacts.json';
        $existing = array();
        if (file_exists($logFile)) {
            $raw = @file_get_contents($logFile);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) { $existing = $decoded; }
            }
        }
        $existing[] = array(
            'id'      => 'c' . (count($existing) + 1) . '-' . substr(md5(time()), 0, 6),
            'name'    => $name,
            'email'   => $email,
            'subject' => $subject,
            'message' => $message,
            'ip'      => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'date'    => date('Y-m-d H:i:s'),
            'read'    => false,
        );
        @file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sent = true;
    }
}

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 720px;">
        <h1 class="section-title" style="margin-bottom: 20px;">Contact Us</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Have a question, request, or feedback? Send us a message using the form below.
            We typically respond within 48 hours.
        </p>

        <?php if ($sent): ?>
            <div class="admin-card" style="background: rgba(46,204,113,0.1); border-color: #2ecc71; color: #2ecc71; padding: 18px; margin-bottom: 24px;">
                <i class="fas fa-check-circle"></i> Thank you! Your message has been received. We will get back to you soon.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
                <ul style="margin:0; padding-left:20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST" action="<?php e(BASE_URL); ?>/contact.php">
                <?php echo csrfField(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Your Name <span style="color:#e74c3c;">*</span></label>
                        <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Email <span style="color:#e74c3c;">*</span></label>
                        <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES) : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Message <span style="color:#e74c3c;">*</span></label>
                    <textarea name="message" required rows="6"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
            </form>
        </div>

        <?php
        $contactEmail = isset($settings['contact_email']) ? $settings['contact_email'] : '';
        $contactPhone = isset($settings['contact_phone']) ? $settings['contact_phone'] : '';
        if ($contactEmail || $contactPhone):
        ?>
        <div class="admin-card" style="margin-top: 20px;">
            <h3 style="margin-bottom:12px; font-size:16px;">Other Ways to Reach Us</h3>
            <?php if ($contactEmail): ?>
                <p style="margin-bottom:6px;"><i class="fas fa-envelope" style="color:var(--primary);"></i> <a href="mailto:<?php e($contactEmail); ?>"><?php e($contactEmail); ?></a></p>
            <?php endif; ?>
            <?php if ($contactPhone): ?>
                <p style="margin-bottom:6px;"><i class="fas fa-phone" style="color:var(--primary);"></i> <?php e($contactPhone); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
