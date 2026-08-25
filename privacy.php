<?php
// BDMovieHub - Privacy Policy
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Privacy Policy';
$settings = getSettings();

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 820px;">
        <h1 class="section-title" style="margin-bottom: 20px;">Privacy Policy</h1>
        <div class="page-content">
            <p><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>

            <h2>1. Information We Collect</h2>
            <p>
                <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?> collects minimal information necessary to provide our services. We may collect the following types of information:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li><strong>Information you provide:</strong> When you contact us, leave a comment, or interact with our content, we may collect your name, email address, and any other information you voluntarily provide.</li>
                <li><strong>Log data:</strong> Like most websites, our servers automatically record information about how you access and use the site, including IP address, browser type, pages visited, and timestamps.</li>
                <li><strong>Local storage:</strong> We use browser local storage to remember your theme preference and favorites list. This data stays on your device and is never transmitted to us.</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>
                We use the information we collect to:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Operate, maintain, and improve our website and services.</li>
                <li>Respond to your comments, questions, and customer service requests.</li>
                <li>Monitor and analyze trends, usage, and activities in connection with our service.</li>
                <li>Detect, investigate, and prevent fraudulent transactions and other illegal activities.</li>
                <li>Personalize your experience by remembering your preferences and settings.</li>
            </ul>

            <h2>3. Cookies and Tracking</h2>
            <p>
                We do not use cookies to track users across third-party websites. However, we may use browser local storage to store your theme preference (dark/light mode) and your favorites list. This data is stored only on your device and can be cleared at any time from your browser settings.
            </p>

            <h2>4. Third-Party Services</h2>
            <p>
                Our website may use third-party services such as analytics providers, video hosting services (CDN), and font/CDN providers (e.g., jsdelivr, Google Fonts, Font Awesome). These third parties may collect usage data in accordance with their own privacy policies.
            </p>

            <h2>5. Data Security</h2>
            <p>
                We take reasonable measures to protect the information we collect from unauthorized access, alteration, or destruction. However, no method of transmission over the internet or method of electronic storage is 100% secure.
            </p>

            <h2>6. Children's Privacy</h2>
            <p>
                Our service is not directed to children under the age of 13. We do not knowingly collect personal information from children under 13. If you believe that a child has provided us with personal information, please contact us so we can delete it.
            </p>

            <h2>7. Your Rights</h2>
            <p>
                Depending on your location, you may have certain rights regarding your personal information, including:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>The right to access the personal information we have about you.</li>
                <li>The right to request that we correct or delete your personal information.</li>
                <li>The right to opt-out of certain communications from us.</li>
                <li>The right to withdraw consent at any time.</li>
            </ul>

            <h2>8. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date. You are advised to review this Privacy Policy periodically for any changes.
            </p>

            <h2>9. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy, please <a href="<?php e(BASE_URL); ?>/contact.php">contact us</a>.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
