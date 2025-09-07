<?php
require_once 'config.php';
$page_title = "Terms of Service - HabitTracker";
require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Terms of Service</h1>
        <p class="text-gray-600">Last updated: <?php echo date('F j, Y'); ?></p>
    </div>

    <div class="app-card p-8">
        <div class="prose max-w-none">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Acceptance of Terms</h2>
            <p class="text-gray-600 mb-6">
                By accessing or using HabitTracker, you agree to be bound by these Terms of Service
                and our Privacy Policy. If you do not agree to these terms, please do not use our service.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. User Accounts</h2>
            <p class="text-gray-600 mb-6">
                You are responsible for maintaining the confidentiality of your account credentials
                and for all activities that occur under your account. You must be at least 13 years
                old to use our service.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. User Content</h2>
            <p class="text-gray-600 mb-6">
                You retain all rights to the content you create and share on HabitTracker. By posting
                content, you grant us a license to use, display, and distribute that content for the
                purpose of providing our services.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Prohibited Conduct</h2>
            <p class="text-gray-600 mb-6">
                You agree not to:
            </p>
            <ul class="list-disc list-inside text-gray-600 mb-6">
                <li>Use the service for any illegal purpose</li>
                <li>Harass, abuse, or harm other users</li>
                <li>Impersonate any person or entity</li>
                <li>Interfere with or disrupt the service</li>
                <li>Attempt to gain unauthorized access to other accounts</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Termination</h2>
            <p class="text-gray-600 mb-6">
                We may suspend or terminate your account if you violate these Terms of Service.
                You may delete your account at any time through your account settings.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Limitation of Liability</h2>
            <p class="text-gray-600 mb-6">
                HabitTracker is provided "as is" without any warranties. We are not liable for
                any damages resulting from your use of the service.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Changes to Terms</h2>
            <p class="text-gray-600">
                We may modify these terms at any time. We will notify you of significant changes
                by email or through the service. Continued use after changes constitutes acceptance
                of the modified terms.
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>