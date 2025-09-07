<?php
require_once 'config.php';
$page_title = "Privacy Policy - HabitTracker";
require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Privacy Policy</h1>
        <p class="text-gray-600">Last updated: <?php echo date('F j, Y'); ?></p>
    </div>

    <div class="app-card p-8">
        <div class="prose max-w-none">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Information We Collect</h2>
            <p class="text-gray-600 mb-6">
                We collect information you provide directly to us when you create an account,
                track habits, join groups, or communicate with us. This includes:
            </p>
            <ul class="list-disc list-inside text-gray-600 mb-6">
                <li>Account information (name, email, password)</li>
                <li>Habit data and progress information</li>
                <li>Group memberships and interactions</li>
                <li>Communication preferences</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. How We Use Your Information</h2>
            <p class="text-gray-600 mb-6">
                We use the information we collect to:
            </p>
            <ul class="list-disc list-inside text-gray-600 mb-6">
                <li>Provide, maintain, and improve our services</li>
                <li>Personalize your experience and provide habit insights</li>
                <li>Send you technical notices and support messages</li>
                <li>Respond to your comments and questions</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. Data Security</h2>
            <p class="text-gray-600 mb-6">
                We implement appropriate security measures to protect your personal information
                against unauthorized access, alteration, disclosure, or destruction. All data
                is encrypted in transit and at rest.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Your Rights</h2>
            <p class="text-gray-600 mb-6">
                You have the right to:
            </p>
            <ul class="list-disc list-inside text-gray-600 mb-6">
                <li>Access and download your personal data</li>
                <li>Correct inaccurate personal information</li>
                <li>Request deletion of your personal data</li>
                <li>Export your habit data at any time</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Contact Us</h2>
            <p class="text-gray-600">
                If you have any questions about this Privacy Policy, please contact us at
                <a href="mailto:hamimshahriar17@gmail.com" class="text-blue-500 hover:text-blue-600">hamimshahriar17@gmail.com</a>.
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>