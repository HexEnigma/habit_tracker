<?php
require_once 'config.php';
?>
<!-- Navigation -->
<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="home.php" class="flex items-center space-x-2">
                <i class="fas fa-check-circle text-blue-500 text-2xl"></i>
                <span class="text-xl font-bold text-gray-800">HabitTracker</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="home.php" class="text-gray-600 hover:text-blue-500 transition">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="text-gray-600 hover:text-blue-500 transition">Dashboard</a>
                    <a href="habits.php" class="text-gray-600 hover:text-blue-500 transition">Habits</a>
                    <a href="analytics.php" class="text-gray-600 hover:text-blue-500 transition">Analytics</a>
                    <div class="relative group">
                        <button class="flex items-center space-x-1 text-gray-600 hover:text-blue-500 transition">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <hr class="my-1">
                            <a href="auth.php?action=logout" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-blue-500 transition">Login</a>
                    <a href="login.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">Sign Up</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden p-2 text-gray-600">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="container mx-auto px-4 py-3 space-y-3">
            <a href="home.php" class="block py-2 text-gray-600 hover:text-blue-500">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="block py-2 text-gray-600 hover:text-blue-500">Dashboard</a>
                <a href="habits.php" class="block py-2 text-gray-600 hover:text-blue-500">Habits</a>
                <a href="analytics.php" class="block py-2 text-gray-600 hover:text-blue-500">Analytics</a>
                <a href="profile.php" class="block py-2 text-gray-600 hover:text-blue-500">Profile</a>
                <a href="settings.php" class="block py-2 text-gray-600 hover:text-blue-500">Settings</a>
                <hr>
                <a href="auth.php?action=logout" class="block py-2 text-red-600 hover:text-red-700">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block py-2 text-gray-600 hover:text-blue-500">Login</a>
                <a href="login.php" class="block py-2 bg-blue-500 text-white rounded-lg">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (mobileMenu && !mobileMenu.contains(e.target) &&
                mobileMenuButton && !mobileMenuButton.contains(e.target) &&
                !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
            }
        });
    });
</script>