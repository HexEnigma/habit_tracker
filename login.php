<?php
session_start();
// Get error messages and form data from session
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
// Clear session messages after use
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HabitTracker</title>
    <link rel="stylesheet" href="css/login-style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-box Login">
            <h2>Login to HabitTracker</h2>
            <p>Welcome back! Continue your journey to better habits</p>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">

                <div class="input-box">
                    <input type="email" id="login-email" name="email"
                        value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                        placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                    <?php if (isset($formErrors['email'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="password" id="login-password" name="password"
                        placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                    <?php if (isset($formErrors['password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['password']); ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Login</button>

                <div class="regi-link">
                    Don't have an account? <a href="#" class="register-link">Sign Up</a>
                </div>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-box Register">
            <h2>Create Account</h2>
            <p>Join us and start building better habits today</p>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="signup">

                <div class="input-box">
                    <input type="text" id="signup-username" name="username"
                        value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                        placeholder="Username" required>
                    <i class='bx bxs-user'></i>
                    <?php if (isset($formErrors['username'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['username']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="email" id="signup-email" name="email"
                        value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                        placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                    <?php if (isset($formErrors['email'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="password" id="signup-password" name="password"
                        placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                    <?php if (isset($formErrors['password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['password']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="password" id="signup-confirm-password" name="confirm_password"
                        placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-alt'></i>
                    <?php if (isset($formErrors['confirm_password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['confirm_password']); ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Register</button>

                <div class="regi-link">
                    Already have an account? <a href="#" class="login-link">Sign In</a>
                </div>
            </form>
        </div>

        <!-- Login Info -->
        <div class="info-content Login">
            <i class='bx bxs-check-circle habit-icon'></i>
            <h2>Welcome Back!</h2>
            <p>We're excited to have you continue your habit tracking journey. Consistency is the key to success!</p>
        </div>

        <!-- Register Info -->
        <div class="info-content Register">
            <i class='bx bxs-calendar habit-icon'></i>
            <h2>Start Your Journey!</h2>
            <p>Join thousands of users who are building better habits every day. Track your progress and achieve your goals!</p>
        </div>
    </div>

    <script>
        const container = document.querySelector('.container');
        const registerLink = document.querySelector('.register-link');
        const loginLink = document.querySelector('.login-link');

        registerLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add('active');
        });

        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove('active');
        });

        // Password match validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[name="password"]');
                const confirmPassword = this.querySelector('input[name="confirm_password"]');
                if (confirmPassword && password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                }
            });
        });

        // Auto-switch to register form if registration errors exist
        <?php if (!empty($formErrors) && isset($formData['action']) && $formData['action'] === 'signup'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                container.classList.add('active');
            });
        <?php endif; ?>
    </script>
</body>

</html>