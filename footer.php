</div>

<!-- Footer -->
<footer class="bg-white border-t mt-16">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-check-circle text-blue-500 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">HabitTracker</span>
                </div>
                <p class="text-gray-600 mb-4">
                    Build better habits, one day at a time. Track your progress and achieve your goals.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-facebook text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="home.php" class="text-gray-600 hover:text-blue-500 transition">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="text-gray-600 hover:text-blue-500 transition">Dashboard</a></li>
                        <li><a href="habits.php" class="text-gray-600 hover:text-blue-500 transition">My Habits</a></li>
                        <li><a href="analytics.php" class="text-gray-600 hover:text-blue-500 transition">Analytics</a></li>
                    <?php else: ?>
                        <li><a href="javascript:void(0)" onclick="showLoginToast()" class="text-gray-600 hover:text-blue-500 transition">Dashboard</a></li>
                        <li><a href="javascript:void(0)" onclick="showLoginToast()" class="text-gray-600 hover:text-blue-500 transition">My Habits</a></li>
                        <li><a href="javascript:void(0)" onclick="showLoginToast()" class="text-gray-600 hover:text-blue-500 transition">Analytics</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Support</h3>
                <ul class="space-y-2">
                    <li><a href="faq.php" class="text-gray-600 hover:text-blue-500 transition">FAQ</a></li>
                    <li><a href="contact.php" class="text-gray-600 hover:text-blue-500 transition">Contact Us</a></li>
                    <li><a href="privacy.php" class="text-gray-600 hover:text-blue-500 transition">Privacy Policy</a></li>
                    <li><a href="terms.php" class="text-gray-600 hover:text-blue-500 transition">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Stay Updated</h3>
                <p class="text-gray-600 mb-4">Get tips and updates on habit building</p>
                <form class="space-y-2">
                    <input type="email" placeholder="Your email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-500 text-white w-full py-2 rounded-lg font-semibold hover:bg-blue-600 transition">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="border-t mt-8 pt-6 text-center">
            <p class="text-gray-600">&copy; 2025 HabitTracker. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="js/script.js"></script>

<script>
    function showLoginToast() {
        // Show toast notification
        if (typeof showToast !== 'undefined') {
            showToast('Please login first to access this feature', 'info');
        } else {
            // Fallback if showToast is not available
            alert('Please login first to access this feature');
        }

        // Redirect to login page after a short delay
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
    }
</script>
</body>

</html>