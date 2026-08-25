<?php
// BDMovieHub - Disclaimer
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Disclaimer';
$settings = getSettings();

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 820px;">
        <h1 class="section-title" style="margin-bottom: 20px;">Disclaimer</h1>
        <div class="page-content">
            <p><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>

            <h2>General Information</h2>
            <p>
                The information provided by <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?> on this website is for general informational purposes only. All information on the Site is provided in good faith; however, we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the Site.
            </p>

            <h2>External Links Disclaimer</h2>
            <p>
                The Site may contain links to other websites or content belonging to or originating from third parties. Such external links are not investigated, monitored, or checked for accuracy, adequacy, validity, reliability, availability, or completeness by us.
            </p>

            <h2>Streaming Content Disclaimer</h2>
            <p>
                All video content linked to from this website is hosted on third-party servers and platforms. We do not host any video content on our own servers. We are not responsible for the accuracy, compliance, copyright, or any other aspect of the content hosted on these third-party platforms.
            </p>
            <p>
                If you believe any content linked from this site violates your copyright, please refer to our <a href="<?php e(BASE_URL); ?>/dmca.php">DMCA Policy</a> for instructions on submitting a takedown request.
            </p>

            <h2>Professional Disclaimer</h2>
            <p>
                The Site cannot and does not contain professional advice. The information provided is not a substitute for professional advice. Before making any decision based on information from this Site, you should seek the appropriate professional advice.
            </p>

            <h2>Use at Your Own Risk</h2>
            <p>
                Your use of the Site and your reliance on any information on the Site is solely at your own risk. We will not be liable for any loss or damage of any kind incurred as a result of using the Site or relying on any information provided on the Site.
            </p>

            <h2>Consent</h2>
            <p>
                By using our website, you hereby consent to this disclaimer and agree to its terms. If you do not agree with any part of this disclaimer, please discontinue use of the Site.
            </p>

            <h2>Contact Us</h2>
            <p>
                Should you have any questions about this disclaimer, please <a href="<?php e(BASE_URL); ?>/contact.php">contact us</a>.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
