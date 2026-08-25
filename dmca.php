<?php
// BDMovieHub - DMCA Policy
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'DMCA Policy';
$settings = getSettings();

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 820px;">
        <h1 class="section-title" style="margin-bottom: 20px;">DMCA Policy</h1>
        <div class="page-content">
            <p><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>

            <h2>1. Notice of Copyright Infringement</h2>
            <p>
                <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?> respects the intellectual property rights of others and expects users of our website to do the same. We comply with the Digital Millennium Copyright Act (DMCA) and other applicable copyright laws.
            </p>
            <p>
                If you believe that any content available on this website infringes your copyright, you may submit a DMCA takedown request by providing the following information in writing:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>A physical or electronic signature of the copyright owner or a person authorized to act on their behalf.</li>
                <li>Identification of the copyrighted work claimed to have been infringed.</li>
                <li>Identification of the material that is claimed to be infringing and information reasonably sufficient to permit us to locate the material.</li>
                <li>Your contact information, including your full name, mailing address, telephone number, and email address.</li>
                <li>A statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law.</li>
                <li>A statement that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
            </ul>

            <h2>2. Submitting a Takedown Request</h2>
            <p>
                DMCA takedown requests should be sent to our designated copyright agent at the contact information provided on our <a href="<?php e(BASE_URL); ?>/contact.php">Contact page</a>. Please allow up to 72 hours for us to review and respond to your request.
            </p>

            <h2>3. Counter-Notification</h2>
            <p>
                If you believe that your content was removed from this website in error, you may submit a counter-notification. The counter-notification must include:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Your physical or electronic signature.</li>
                <li>Identification of the material that has been removed and the location at which it appeared before removal.</li>
                <li>A statement under penalty of perjury that you have a good faith belief that the material was removed or disabled as a result of mistake or misidentification.</li>
                <li>Your name, address, telephone number, and email address.</li>
                <li>A statement that you consent to the jurisdiction of the Federal District Court for the judicial district in which your address is located (or if your address is outside of the United States, for any judicial district in which we may be found).</li>
            </ul>

            <h2>4. Repeat Infringers</h2>
            <p>
                We reserve the right to terminate the accounts of users who are determined to be repeat infringers of copyright.
            </p>

            <h2>5. Third-Party Links</h2>
            <p>
                This website may contain links to third-party websites and content. We do not control these external sites and are not responsible for their content. The inclusion of any link does not imply endorsement by us.
            </p>

            <h2>6. Changes to This Policy</h2>
            <p>
                We reserve the right to modify this DMCA Policy at any time. Changes will be effective immediately upon posting to this page. We encourage you to review this page periodically for updates.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
