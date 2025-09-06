<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$csrf_token = generate_csrf_token();

// Handle form submissions
$message = '';
$error = '';
$toast_message = '';
$toast_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);

        // Validate inputs
        if (empty($new_username)) {
            $error = 'Username is required';
        } elseif (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Valid email is required';
        } else {
            try {
                // Check if email already exists (excluding current user)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$new_email, $user_id]);

                if ($stmt->rowCount() > 0) {
                    $error = 'Email already exists';
                } else {
                    // Update user profile
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$new_username, $new_email, $user_id]);

                    // Update session
                    $_SESSION['username'] = $new_username;
                    $_SESSION['email'] = $new_email;

                    $message = 'Profile updated successfully!';
                }
            } catch (PDOException $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($current_password)) {
            $toast_message = 'Current password is required';
            $toast_type = 'error';
        } elseif (empty($new_password)) {
            $toast_message = 'New password is required';
            $toast_type = 'error';
        } elseif (strlen($new_password) < 8) {
            $toast_message = 'New password must be at least 8 characters';
            $toast_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $toast_message = 'New passwords do not match';
            $toast_type = 'error';
        } else {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                if ($user && password_verify($current_password, $user['password_hash'])) {
                    // Update password
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$new_password_hash, $user_id]);

                    $toast_message = 'Password changed successfully!';
                    $toast_type = 'success';
                } else {
                    $toast_message = 'Current password is incorrect';
                    $toast_type = 'error';
                }
            } catch (PDOException $e) {
                $toast_message = 'Error changing password';
                $toast_type = 'error';
            }
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Settings</h1>
            <p class="text-gray-600">Manage your account settings and preferences</p>
        </div>
        <a href="dashboard.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message && (!isset($_POST['action']) || $_POST['action'] !== 'change_password')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error && (!isset($_POST['action']) || $_POST['action'] !== 'change_password')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Settings -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user text-blue-500 mr-2"></i>
                Profile Settings
            </h2>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_profile">

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-600 transition">
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Password Change -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-lock text-blue-500 mr-2"></i>
                Change Password
            </h2>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="change_password">

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-600 transition">
                    Change Password
                </button>
            </form>
        </div>

        <!-- Notification Settings -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bell text-blue-500 mr-2"></i>
                Notification Settings
            </h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-800">Email Notifications</div>
                        <div class="text-sm text-gray-600">Receive email updates about your habits</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-800">Habit Reminders</div>
                        <div class="text-sm text-gray-600">Get reminders for incomplete habits</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-800">Achievement Alerts</div>
                        <div class="text-sm text-gray-600">Notify when you earn achievements</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
            </div>

            <button class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-600 transition mt-4">
                Save Notification Settings
            </button>
        </div>

        <!-- Account Management -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-cog text-blue-500 mr-2"></i>
                Account Management
            </h2>

            <div class="space-y-4">
                <!--
<div>
    <h3 class="font-medium text-gray-800 mb-2">Export Data</h3>
    <p class="text-sm text-gray-600 mb-3">Download all your habit data in CSV format</p>
    <a href="export-data.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-200 transition inline-flex items-center">
        <i class="fas fa-download mr-2"></i> Export My Data
    </a>
</div>
-->

                <div class="pt-4 border-t">
                    <h3 class="font-medium text-red-800 mb-2">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-3">Permanently delete your account and all data</p>
                    <button onclick="confirmAccountDeletion()" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition">
                        <i class="fas fa-trash mr-2"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmAccountDeletion() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone. All your data will be permanently deleted.')) {
            // Implement account deletion here
            alert('Account deletion would be processed here. In a real application, this would redirect to a deletion confirmation page.');
        }
    }
    <?php if (!empty($toast_message)): ?>
        // Show toast notification after page load
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?php echo addslashes($toast_message); ?>', '<?php echo $toast_type; ?>');
        });
    <?php endif; ?>
</script>

<?php require_once 'footer.php'; ?>