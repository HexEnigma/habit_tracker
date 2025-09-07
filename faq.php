<?php
require_once 'config.php';
$page_title = "FAQ - HabitTracker";
require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h1>
        <p class="text-gray-600">Find answers to common questions about HabitTracker</p>
    </div>

    <div class="app-card p-8">
        <div class="space-y-6">
            <!-- Getting Started -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Getting Started</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">How do I create my first habit?</h3>
                        <p class="text-gray-600">Click on "Add New Habit" from your dashboard, fill in the habit details, set your frequency, and click "Create Habit". You can start tracking immediately!</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Is HabitTracker free to use?</h3>
                        <p class="text-gray-600">Yes! HabitTracker is completely free to use with all features available to all users.</p>
                    </div>
                </div>
            </div>

            <!-- Habit Tracking -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Habit Tracking</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">How do streaks work?</h3>
                        <p class="text-gray-600">Your streak increases by 1 for each consecutive day you complete a habit. Missing a day resets the streak counter for that habit.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Can I edit or delete habits?</h3>
                        <p class="text-gray-600">Yes, you can edit habits by clicking the edit icon or delete them using the trash icon on your habits page.</p>
                    </div>
                </div>
            </div>

            <!-- Groups & Social -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Groups & Social Features</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">How do I join a group?</h3>
                        <p class="text-gray-600">Go to the Groups page, browse public groups, and click "Join Group" on any group you'd like to be part of.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Can I create my own group?</h3>
                        <p class="text-gray-600">Yes! Click "Create Group" on the Groups page, set your group name and privacy settings, and start inviting members.</p>
                    </div>
                </div>
            </div>

            <!-- Technical -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Technical Support</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">My progress didn't save. What should I do?</h3>
                        <p class="text-gray-600">First, refresh the page. If the issue persists, clear your browser cache or try using a different browser. If problems continue, contact our support team.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Is my data secure?</h3>
                        <p class="text-gray-600">Yes, we take data security seriously. All your data is encrypted and stored securely. We never share your personal information with third parties.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-question-circle text-blue-500 text-2xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-blue-800">Still have questions?</h3>
                    <p class="text-blue-600">Contact our support team for personalized assistance.</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="contact.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition inline-block">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>