<?php
// BDMovieHub - Terms of Service
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Terms of Service';
$settings = getSettings();

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container" style="max-width: 820px;">
        <h1 class="section-title" style="margin-bottom: 20px;">Terms of Service</h1>
        <div class="page-content">
            <p><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>

            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing or using <?php e(isset($settings['site_name']) ? $settings['site_name'] : SITE_NAME); ?> (the "Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, please do not use the Service.
            </p>

            <h2>2. Use of the Service</h2>
            <p>
                You may use the Service only for lawful purposes and in accordance with these Terms. You agree not to use the Service:
            </p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>In any way that violates any applicable federal, state, local, or international law or regulation.</li>
                <li>To transmit any material that is defamatory, obscene, indecent, abusive, offensive, harassing, or otherwise objectionable.</li>
                <li>To impersonate or attempt to impersonate the company, employees, or other users.</li>
                <li>To engage in any conduct that restricts or inhibits anyone's use or enjoyment of the Service.</li>
                <li>To use any robot, spider, or other automatic device to access the Service for any purpose without our express written permission.</li>
                <li>To introduce any viruses, trojan horses, worms, or other malicious code.</li>
            </ul>

            <h2>3. Intellectual Property</h2>
            <p>
                The Service and its original content, features, and functionality are owned by us and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws. All video and image content hosted on third-party services belongs to their respective owners.
            </p>

            <h2>4. User Content</h2>
            <p>
                If the Service allows you to post, comment, or otherwise submit content, you retain ownership of that content. By submitting content, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, and distribute that content in connection with the Service.
            </p>
            <p>
                You represent and warrant that you own or have the necessary rights to all content you submit, and that the content does not violate the rights of any third party.
            </p>

            <h2>5. Third-Party Content and Links</h2>
            <p>
                The Service may contain links to third-party websites or services that are not owned or controlled by us. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services. We strongly advise you to read the terms and policies of any third-party services that you access.
            </p>

            <h2>6. Disclaimer of Warranties</h2>
            <p>
                The Service is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, or non-infringement. We do not warrant that the Service will be uninterrupted, secure, or error-free.
            </p>

            <h2>7. Limitation of Liability</h2>
            <p>
                In no event shall we be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of the Service.
            </p>

            <h2>8. Indemnification</h2>
            <p>
                You agree to defend, indemnify, and hold harmless the company and its affiliates from any claims, damages, losses, liabilities, costs, and expenses, including attorney's fees, arising from your use of the Service or your violation of these Terms.
            </p>

            <h2>9. Termination</h2>
            <p>
                We reserve the right to suspend or terminate your access to the Service at any time, without notice, for any reason, including violation of these Terms.
            </p>

            <h2>10. Governing Law</h2>
            <p>
                These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which the Service is operated, without regard to its conflict of law provisions.
            </p>

            <h2>11. Changes to These Terms</h2>
            <p>
                We reserve the right to modify these Terms at any time. We will notify users of any material changes by posting the new Terms on this page and updating the "Last updated" date. Your continued use of the Service after any changes constitutes acceptance of the new Terms.
            </p>

            <h2>12. Contact Us</h2>
            <p>
                If you have any questions about these Terms, please <a href="<?php e(BASE_URL); ?>/contact.php">contact us</a>.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
