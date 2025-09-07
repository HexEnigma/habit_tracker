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
                    <a href="https://www.linkedin.com/in/hamim-shahriar" target="_blank" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://github.com/HexEnigma" target="_blank" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://www.facebook.com/HamimShahriar4" target="_blank" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-facebook text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fab fa-twitter text-lg"></i>
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
                <form method="POST" class="space-y-2" onsubmit="handleNewsletterSubmit(event)">
                    <input type="hidden" name="action" value="newsletter_subscribe">
                    <input type="email" name="email" placeholder="Your email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-500 text-white w-full py-2 rounded-lg font-semibold hover:bg-blue-600 transition">
                        Subscribe
                    </button>
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

    function handleNewsletterSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const emailInput = form.querySelector('input[name="email"]');
        const submitButton = form.querySelector('button[type="submit"]');

        // Store original button text and disable button
        const originalButtonText = submitButton.textContent;
        submitButton.textContent = 'Subscribing...';
        submitButton.disabled = true;

        // Submit the form via AJAX
        fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show thank you message
                    showToast('Thank you for subscribing to our newsletter!', 'success');

                    // Clear the email input
                    emailInput.value = '';

                    // Show social media suggestion after a delay
                    setTimeout(() => {
                        showToast('You\'re welcome to contact us via our social media channels!', 'info');
                    }, 2000);

                    // Redirect to contact page after showing both toasts
                    setTimeout(() => {
                        window.location.href = 'contact.php';
                    }, 4000);
                } else {
                    showToast(data.error, 'error');
                    // Re-enable button on error
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show messages even if there's an error (since email is stored)
                showToast('Thank you for subscribing to our newsletter!', 'success');
                setTimeout(() => {
                    showToast('You\'re welcome to contact us via our social media channels!', 'info');
                }, 2000);

                // Redirect to contact page after showing both toasts
                setTimeout(() => {
                    window.location.href = 'contact.php';
                }, 4000);
            });
    }
</script>
</body>

</html>