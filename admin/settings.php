<?php
// BDMovieHub - Admin Settings (enhanced with social, SEO, custom code)
require_once __DIR__ . '/../config.php';
$adminPage = 'settings';
$pageTitle = 'Settings';

$settings = getSettings();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    // Site identity
    $site_name     = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $site_url      = isset($_POST['site_url']) ? trim($_POST['site_url']) : '';
    $description   = isset($_POST['description']) ? trim($_POST['description']) : '';
    $primary_color = isset($_POST['primary_color']) ? trim($_POST['primary_color']) : '#469AFF';
    $accent_color  = isset($_POST['accent_color']) ? trim($_POST['accent_color']) : '#FF6B6B';
    $anime_color   = isset($_POST['anime_color']) ? trim($_POST['anime_color']) : '#9b59b6';
    $logo_url      = isset($_POST['logo_url']) ? trim($_POST['logo_url']) : '';
    $footer_text   = isset($_POST['footer_text']) ? trim($_POST['footer_text']) : '';
    $contact_email = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : '';
    $contact_phone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : '';

    // Social
    $social_facebook  = isset($_POST['social_facebook']) ? trim($_POST['social_facebook']) : '';
    $social_twitter   = isset($_POST['social_twitter']) ? trim($_POST['social_twitter']) : '';
    $social_instagram = isset($_POST['social_instagram']) ? trim($_POST['social_instagram']) : '';
    $social_youtube   = isset($_POST['social_youtube']) ? trim($_POST['social_youtube']) : '';
    $social_telegram  = isset($_POST['social_telegram']) ? trim($_POST['social_telegram']) : '';
    $social_discord   = isset($_POST['social_discord']) ? trim($_POST['social_discord']) : '';

    // SEO
    $seo_keywords = isset($_POST['seo_keywords']) ? trim($_POST['seo_keywords']) : '';
    $seo_og_image = isset($_POST['seo_og_image']) ? trim($_POST['seo_og_image']) : '';

    // Custom code
    $analytics_code = isset($_POST['analytics_code']) ? trim($_POST['analytics_code']) : '';
    $custom_css     = isset($_POST['custom_css']) ? trim($_POST['custom_css']) : '';
    $custom_js      = isset($_POST['custom_js']) ? trim($_POST['custom_js']) : '';

    // Comments
    $auto_approve_comments = isset($_POST['auto_approve_comments']) ? true : false;

    if ($site_name === '') { $errors[] = 'Site name is required.'; }

    if (empty($errors)) {
        $newSettings = array(
            'site_name'              => $site_name,
            'site_url'               => $site_url,
            'description'            => $description,
            'primary_color'          => $primary_color,
            'accent_color'           => $accent_color,
            'anime_color'            => $anime_color,
            'logo_url'               => $logo_url,
            'footer_text'            => $footer_text,
            'contact_email'          => $contact_email,
            'contact_phone'          => $contact_phone,
            'social_facebook'        => $social_facebook,
            'social_twitter'         => $social_twitter,
            'social_instagram'       => $social_instagram,
            'social_youtube'         => $social_youtube,
            'social_telegram'        => $social_telegram,
            'social_discord'         => $social_discord,
            'seo_keywords'           => $seo_keywords,
            'seo_og_image'           => $seo_og_image,
            'analytics_code'         => $analytics_code,
            'custom_css'             => $custom_css,
            'custom_js'              => $custom_js,
            'auto_approve_comments'  => $auto_approve_comments,
        );
        if (saveData(FILE_SETTINGS, $newSettings)) {
            setFlash('success', 'Settings saved.');
            adminRedirect('settings.php');
        } else {
            $errors[] = 'Failed to save settings.';
        }
    }
    $settings = array_merge($settings, $_POST);
}

include __DIR__ . '/header.php';
?>

<?php if (!empty($errors)): ?>
    <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
        <ul style="margin:0; padding-left:20px;">
            <?php foreach ($errors as $err): ?>
                <li><?php e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?php e($adminUrl); ?>/settings.php">
<?php echo csrfField(); ?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-globe" style="color:#469AFF;"></i> Site Identity</h2>

    <div class="form-row">
        <div class="form-group">
            <label>Site Name <span style="color:#e74c3c;">*</span></label>
            <input type="text" name="site_name" required value="<?php e(isset($settings['site_name']) ? $settings['site_name'] : ''); ?>">
        </div>
        <div class="form-group">
            <label>Site URL</label>
            <input type="text" name="site_url" value="<?php e(isset($settings['site_url']) ? $settings['site_url'] : ''); ?>" placeholder="https://example.com">
        </div>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description"><?php e(isset($settings['description']) ? $settings['description'] : ''); ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Logo URL (optional)</label>
            <input type="text" name="logo_url" value="<?php e(isset($settings['logo_url']) ? $settings['logo_url'] : ''); ?>" placeholder="https://example.com/logo.png">
            <div class="hint">If empty, text logo will be used. Recommended height: 36px.</div>
        </div>
        <div class="form-group">
            <label>Contact Email</label>
            <input type="email" name="contact_email" value="<?php e(isset($settings['contact_email']) ? $settings['contact_email'] : ''); ?>" placeholder="contact@example.com">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Contact Phone (optional)</label>
            <input type="text" name="contact_phone" value="<?php e(isset($settings['contact_phone']) ? $settings['contact_phone'] : ''); ?>">
        </div>
        <div class="form-group">
            <label>Footer Text (optional)</label>
            <input type="text" name="footer_text" value="<?php e(isset($settings['footer_text']) ? $settings['footer_text'] : ''); ?>">
        </div>
    </div>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-palette" style="color:#9b59b6;"></i> Theme Colors</h2>
    <div class="form-row-3">
        <div class="form-group">
            <label>Primary Color (Movies)</label>
            <input type="color" name="primary_color" value="<?php e(isset($settings['primary_color']) ? $settings['primary_color'] : '#469AFF'); ?>" style="height:46px; padding:4px;">
            <div class="hint">Default: #469AFF (blue)</div>
        </div>
        <div class="form-group">
            <label>Accent Color</label>
            <input type="color" name="accent_color" value="<?php e(isset($settings['accent_color']) ? $settings['accent_color'] : '#FF6B6B'); ?>" style="height:46px; padding:4px;">
            <div class="hint">Default: #FF6B6B (red)</div>
        </div>
        <div class="form-group">
            <label>Anime Color</label>
            <input type="color" name="anime_color" value="<?php e(isset($settings['anime_color']) ? $settings['anime_color'] : '#9b59b6'); ?>" style="height:46px; padding:4px;">
            <div class="hint">Default: #9b59b6 (purple)</div>
        </div>
    </div>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-share-alt" style="color:#2ecc71;"></i> Social Media Links</h2>
    <p style="color:#a0a0b8; margin-bottom:16px;">Leave empty to hide a social icon from the footer.</p>
    <div class="form-row-3">
        <div class="form-group">
            <label><i class="fab fa-facebook-f" style="color:#3b5998;"></i> Facebook</label>
            <input type="text" name="social_facebook" value="<?php e(isset($settings['social_facebook']) ? $settings['social_facebook'] : ''); ?>" placeholder="https://facebook.com/...">
        </div>
        <div class="form-group">
            <label><i class="fab fa-twitter" style="color:#1da1f2;"></i> Twitter</label>
            <input type="text" name="social_twitter" value="<?php e(isset($settings['social_twitter']) ? $settings['social_twitter'] : ''); ?>" placeholder="https://twitter.com/...">
        </div>
        <div class="form-group">
            <label><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram</label>
            <input type="text" name="social_instagram" value="<?php e(isset($settings['social_instagram']) ? $settings['social_instagram'] : ''); ?>" placeholder="https://instagram.com/...">
        </div>
    </div>
    <div class="form-row-3">
        <div class="form-group">
            <label><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube</label>
            <input type="text" name="social_youtube" value="<?php e(isset($settings['social_youtube']) ? $settings['social_youtube'] : ''); ?>" placeholder="https://youtube.com/...">
        </div>
        <div class="form-group">
            <label><i class="fab fa-telegram" style="color:#0088cc;"></i> Telegram</label>
            <input type="text" name="social_telegram" value="<?php e(isset($settings['social_telegram']) ? $settings['social_telegram'] : ''); ?>" placeholder="https://t.me/...">
        </div>
        <div class="form-group">
            <label><i class="fab fa-discord" style="color:#5869da;"></i> Discord</label>
            <input type="text" name="social_discord" value="<?php e(isset($settings['social_discord']) ? $settings['social_discord'] : ''); ?>" placeholder="https://discord.gg/...">
        </div>
    </div>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-search" style="color:#ffa502;"></i> SEO Settings</h2>
    <div class="form-group">
        <label>SEO Keywords</label>
        <input type="text" name="seo_keywords" value="<?php e(isset($settings['seo_keywords']) ? $settings['seo_keywords'] : ''); ?>" placeholder="movies, anime, streaming, free">
        <div class="hint">Comma-separated keywords for search engines.</div>
    </div>
    <div class="form-group">
        <label>Default Open Graph Image</label>
        <input type="text" name="seo_og_image" value="<?php e(isset($settings['seo_og_image']) ? $settings['seo_og_image'] : ''); ?>" placeholder="https://example.com/og-image.jpg">
        <div class="hint">Default image shown when sharing links on social media. Recommended: 1200x630 px.</div>
    </div>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-comments" style="color:#9b59b6;"></i> Comments</h2>
    <div class="checkbox-row">
        <input type="checkbox" name="auto_approve_comments" id="auto_approve" value="1" <?php echo !empty($settings['auto_approve_comments']) ? 'checked' : ''; ?>>
        <label for="auto_approve" style="margin:0;">Auto-approve comments (skip moderation)</label>
    </div>
    <div class="hint" style="margin-top:6px;">If unchecked, comments will need to be approved from the Messages page.</div>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;"><i class="fas fa-code" style="color:#469AFF;"></i> Custom Code</h2>
    <div class="form-group">
        <label>Analytics Code (Google Analytics, Plausible, etc.)</label>
        <textarea name="analytics_code" rows="5" style="font-family: monospace; font-size:12px;" placeholder='<!-- Google Analytics -->&#10;<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX"></script>&#10;<script>&#10;window.dataLayer = window.dataLayer || [];&#10;function gtag(){dataLayer.push(arguments);}&#10;gtag("js", new Date());&#10;gtag("config", "G-XXXXXXX");&#10;</script>'><?php e(isset($settings['analytics_code']) ? $settings['analytics_code'] : ''); ?></textarea>
        <div class="hint">Pasted as-is into the &lt;head&gt; section of every page.</div>
    </div>
    <div class="form-group">
        <label>Custom CSS</label>
        <textarea name="custom_css" rows="6" style="font-family: monospace; font-size:12px;" placeholder="/* Example: hide a specific element */&#10;.some-class { display: none; }"><?php e(isset($settings['custom_css']) ? $settings['custom_css'] : ''); ?></textarea>
        <div class="hint">Pasted inside a &lt;style&gt; tag on every page.</div>
    </div>
    <div class="form-group">
        <label>Custom JavaScript</label>
        <textarea name="custom_js" rows="6" style="font-family: monospace; font-size:12px;" placeholder="// Example: console.log('Hello from custom JS');"><?php e(isset($settings['custom_js']) ? $settings['custom_js'] : ''); ?></textarea>
        <div class="hint">Pasted at the bottom of every page (after other scripts).</div>
    </div>
</div>

<div style="margin-top:20px;">
    <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save All Settings</button>
</div>

</form>

<?php include __DIR__ . '/footer.php'; ?>
