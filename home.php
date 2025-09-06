<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabitTracker - Build Better Habits</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-6">
                    Build Better Habits,<br>
                    <span class="text-blue-500">One Day at a Time</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Track your habits, build streaks, and achieve your goals with our intuitive habit tracking platform.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if (is_logged_in()): ?>
                        <a href="dashboard.php" class="bg-blue-500 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-blue-600 transition">
                            Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="bg-blue-500 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-blue-600 transition">
                            Get Started Free
                        </a>
                        <a href="#features" class="border border-blue-500 text-blue-500 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                            Learn More
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Why Choose HabitTracker?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Powerful features to help you build lasting habits</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center p-6 rounded-lg hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-fire text-blue-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Streak Tracking</h3>
                    <p class="text-gray-600">Build and maintain streaks to stay motivated and consistent with your habits.</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center p-6 rounded-lg hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-green-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Visual Analytics</h3>
                    <p class="text-gray-600">See your progress with beautiful charts and understand your habit patterns.</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center p-6 rounded-lg hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-purple-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Achievements</h3>
                    <p class="text-gray-600">Earn badges and achievements as you reach milestones in your habit journey.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

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
</body>

</html>