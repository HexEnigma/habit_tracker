<?php
session_start();
// Get error messages from session
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
// Clear the session messages
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
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <style>
        .error {
            color: #ff3860;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            font-weight: 500;
        }

        .input-box {
            position: relative;
            margin-bottom: 20px;
        }

        /* Smooth transitions */
        .form-box {
            transition: all 0.6s ease-in-out;
        }

        /* Ensure proper spacing */
        .login-btn {
            margin-top: 25px;
        }

        .regi-link {
            margin-top: 20px;
        }
    </style>
</head>

<body class="login-body">
    <div class="login-container">
        <div class="curved-shape"></div>
        <div class="curved-shape2"></div>

        <!-- Login Form -->
        <div class="form-box Login">
            <h2 class="animation" style="--D:0; --S:21">Login</h2>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="input-box animation" style="--D:1; --S:22">
                    <input type="email" id="login-email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                    <label for="login-email">Email</label>
                    <box-icon name='envelope' type='solid' color="gray"></box-icon>
                    <?php if (isset($formErrors['email'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--D:2; --S:23">
                    <input type="password" id="login-password" name="password" required>
                    <label for="login-password">Password</label>
                    <box-icon name='lock-alt' type='solid' color="gray"></box-icon>
                    <?php if (isset($formErrors['password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['password']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--D:3; --S:24">
                    <button class="login-btn" type="submit">Login</button>
                </div>

                <div class="regi-link animation" style="--D:4; --S:25">
                    <p>Don't have an account? <br> <a href="#" class="SignUpLink">Sign Up</a></p>
                </div>
            </form>
        </div>

        <!-- Login Info Content -->
        <div class="info-content Login">
            <h2 class="animation" style="--D:0; --S:20">WELCOME BACK!</h2>
            <p class="animation" style="--D:1; --S:21">We are happy to have you with us again. If you need anything, we are here to help.</p>
        </div>

        <!-- Register Form -->
        <div class="form-box Register">
            <h2 class="animation" style="--li:17; --S:0">Register</h2>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="signup">
                <div class="input-box animation" style="--li:18; --S:1">
                    <input type="text" id="signup-username" name="username" value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" required>
                    <label for="signup-username">Username</label>
                    <box-icon type='solid' name='user' color="gray"></box-icon>
                    <?php if (isset($formErrors['username'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['username']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--li:19; --S:2">
                    <input type="email" id="signup-email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                    <label for="signup-email">Email</label>
                    <box-icon name='envelope' type='solid' color="gray"></box-icon>
                    <?php if (isset($formErrors['email'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--li:19; --S:3">
                    <input type="password" id="signup-password" name="password" required>
                    <label for="signup-password">Password</label>
                    <box-icon name='lock-alt' type='solid' color="gray"></box-icon>
                    <?php if (isset($formErrors['password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['password']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--li:20; --S:4">
                    <input type="password" id="signup-confirm-password" name="confirm_password" required>
                    <label for="signup-confirm-password">Confirm Password</label>
                    <box-icon name='lock-alt' type='solid' color="gray"></box-icon>
                    <?php if (isset($formErrors['confirm_password'])): ?>
                        <span class="error"><?php echo htmlspecialchars($formErrors['confirm_password']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-box animation" style="--li:21; --S:5">
                    <button class="login-btn" type="submit">Register</button>
                </div>

                <div class="regi-link animation" style="--li:22; --S:6">
                    <p>Already have an account? <br> <a href="#" class="SignInLink">Sign In</a></p>
                </div>
            </form>
        </div>

        <!-- Register Info Content -->
        <div class="info-content Register">
            <h2 class="animation" style="--li:17; --S:0">WELCOME!</h2>
            <p class="animation" style="--li:18; --S:1">We're delighted to have you here. If you need any assistance, feel free to reach out.</p>
        </div>
    </div>

    <script>
        // JavaScript for login/register form switching
        const container = document.querySelector('.login-container');
        const LoginLink = document.querySelector('.SignInLink');
        const RegisterLink = document.querySelector('.SignUpLink');

        RegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add('active');
        });

        LoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove('active');
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[type="password"]');
                const confirmPassword = this.querySelector('input[id*="confirm"]');

                if (confirmPassword && password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                }
            });
        });

        // Auto-switch to register form if there are registration errors
        <?php if (isset($formErrors) && count($formErrors) > 0 && isset($formData['action']) && $formData['action'] === 'signup'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                container.classList.add('active');
            });
        <?php endif; ?>
    </script>
</body>

</html>