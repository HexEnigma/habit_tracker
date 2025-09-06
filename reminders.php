<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$csrf_token = generate_csrf_token();

// Get user settings
$stmt = $pdo->prepare("SELECT reminder_time, enable_notifications FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch();

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Reminders</h1>
            <p class="text-gray-600">Set up notifications to help you stay on track with your habits</p>
        </div>
        <a href="dashboard.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="app-card p-6">
        <form id="reminder-form" class="space-y-6" action="settings.php" method="POST">
            <!-- Notification Settings -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-bell text-blue-500 mr-2"></i>
                    Notification Settings
                </h2>

                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-bell text-blue-500"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">Enable Notifications</div>
                            <div class="text-sm text-gray-600">Receive reminders for your habits</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enable_notifications" class="sr-only peer"
                            <?php echo ($settings['enable_notifications'] ?? false) ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
            </div>

            <!-- Reminder Time -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                    Daily Reminder Time
                </h2>

                <div class="p-4 border rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Preferred reminder time
                    </label>
                    <input type="time" name="reminder_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo $settings['reminder_time'] ?? '09:00'; ?>">
                    <p class="text-sm text-gray-500 mt-2">Choose the best time to receive your daily habit reminders</p>
                </div>
            </div>

            <!-- Habit-specific Reminders -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list-check text-blue-500 mr-2"></i>
                    Habit-specific Reminders
                </h2>

                <div class="space-y-3">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY name");
                    $stmt->execute([$user_id]);
                    $habits = $stmt->fetchAll();

                    if (empty($habits)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-list-check text-3xl text-gray-300 mb-3"></i>
                            <p>No habits yet</p>
                            <a href="add-habit.php" class="text-blue-500 hover:text-blue-600 text-sm">
                                Add your first habit to set reminders
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($habits as $habit): ?>
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-500"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($habit['name']); ?></div>
                                        <div class="text-sm text-gray-600"><?php echo ucfirst($habit['frequency']); ?></div>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="habit_reminders[]" value="<?php echo $habit['id']; ?>" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6">
                <button type="submit" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Save Reminder Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Notification Preferences -->
    <div class="app-card p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-sliders-h text-blue-500 mr-2"></i>
            Notification Preferences
        </h2>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div>
                    <div class="font-medium text-gray-800">Streak Alerts</div>
                    <div class="text-sm text-gray-600">Get notified when you reach streak milestones</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                </label>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div>
                    <div class="font-medium text-gray-800">Achievement Unlocks</div>
                    <div class="text-sm text-gray-600">Receive notifications for new achievements</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                </label>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div>
                    <div class="font-medium text-gray-800">Weekly Reports</div>
                    <div class="text-sm text-gray-600">Get weekly progress reports every Sunday</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                </label>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>