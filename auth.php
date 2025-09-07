<?php
// Start session and include config FIRST
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    error_log("=== HABIT CREATION DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
}

// Check if it's an AJAX request or form submission
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'signup') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        $errors = [];

        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($errors)) {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Email already exists';
            } else {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);

                if ($stmt->rowCount() > 0) {
                    $errors['username'] = 'Username already exists';
                } else {
                    try {
                        // Hash password
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        // Start transaction
                        $pdo->beginTransaction();

                        // Insert user
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $email, $password_hash]);
                        $user_id = $pdo->lastInsertId();

                        // Initialize user points
                        $stmt = $pdo->prepare("INSERT INTO user_points (user_id, points) VALUES (?, 0)");
                        $stmt->execute([$user_id]);

                        // Commit transaction
                        $pdo->commit();

                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;

                        // Add daily login reward
                        handle_daily_login($pdo, $user_id);

                        if ($isAjax) {
                            echo json_encode(['success' => true, 'message' => 'Registration successful']);
                            exit;
                        } else {
                            header('Location: dashboard.php');
                            exit;
                        }
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        error_log("Registration error: " . $e->getMessage());
                        $errors['general'] = 'Registration failed. Please try again.';
                    }
                }
            }
        }

        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: login.php');
            exit;
        }
    }

    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate inputs
        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // Add daily login reward - THIS SHOULD NOW WORK
                handle_daily_login($pdo, $user['id']);

                // Check for streak milestones after login
                $stmt = $pdo->prepare("SELECT login_streak FROM user_settings WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $login_streak = $stmt->fetch()['login_streak'] ?? 0;

                if ($login_streak % 7 == 0 && $login_streak > 0) {
                    $_SESSION['streak_message'] = "Amazing! {$login_streak}-day login streak! 🎉";
                }

                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Login successful']);
                    exit;
                } else {
                    // Redirect to dashboard for form submissions
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $errors['email'] = 'Invalid email or password';
            }
        }

        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            // For form submissions, redirect back to login with error messages
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: login.php');
            exit;
        }
    }

    if ($action === 'logout') {
        session_destroy();

        if ($isAjax) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            header('Location: index.php');
            exit;
        }
    }

    if ($action === 'update') {
        // Verify user is logged in
        if (!isset($_SESSION['user_id'])) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'errors' => ['general' => 'Not authenticated']]);
                exit;
            } else {
                header('Location: login.php');
                exit;
            }
        }

        $habit_id = $_POST['habit_id'] ?? 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $frequency = $_POST['frequency'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?? null;
        $category = $_POST['category'];
        $is_public = $_POST['is_public'] ?? 0;
        $is_visible_to_group = $_POST['is_visible_to_group'] ?? 0;

        // Validate inputs
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Habit name is required';
        }

        if (empty($start_date) || !validate_date($start_date)) {
            $errors['start_date'] = 'Valid start date is required';
        }

        if ($end_date && !validate_date($end_date)) {
            $errors['end_date'] = 'Valid end date is required';
        }

        if ($frequency === 'custom') {
            $custom_days = $_POST['custom_days'] ?? [];
            if (empty($custom_days)) {
                $errors['custom_days'] = 'Please select at least one day for custom frequency';
            }
        }

        // Check if habit belongs to user
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM habits WHERE id = ? AND user_id = ?");
            $stmt->execute([$habit_id, $_SESSION['user_id']]);

            if ($stmt->rowCount() === 0) {
                $errors['general'] = 'Habit not found or access denied';
            }
        }

        if (empty($errors)) {
            try {
                // Handle custom days
                $custom_days_value = null;
                if ($frequency === 'custom' && !empty($custom_days)) {
                    $custom_days_value = implode(',', $custom_days);
                }

                $stmt = $pdo->prepare("
                    UPDATE habits 
                    SET 
                    name = ?, 
                    description = ?, 
                    frequency = ?, 
                    custom_days = ?, 
                    start_date = ?, 
                    end_date = ?, 
                    category = ?, 
                    is_public = ?, 
                    is_visible_to_group = ?
                    WHERE 
                    id = ? 
                    AND user_id = ?
                ");

                $stmt->execute([
                    $name,
                    $description,
                    $frequency,
                    $custom_days_value,
                    $start_date,
                    $end_date,
                    $category,
                    $is_public,
                    $is_visible_to_group,
                    $habit_id,
                    $_SESSION['user_id']
                ]);

                // Achievement check after successful update
                check_achievements($pdo, $_SESSION['user_id'], 'habit_visibility_update');

                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Habit updated successfully']);
                    exit;
                } else {
                    header('Location: habits.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Habit update error: " . $e->getMessage());
                $errors['general'] = 'Failed to update habit. Please try again.';
            }
        }

        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: add-habit.php?edit=' . $habit_id);
            exit;
        }
    }

    if ($action === 'create') {
        // Verify user is logged in
        if (!isset($_SESSION['user_id'])) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'errors' => ['general' => 'Not authenticated']]);
                exit;
            } else {
                header('Location: login.php');
                exit;
            }
        }

        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $frequency = $_POST['frequency'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?? null;
        $category = $_POST['category'] ?? 'health';
        $is_public = $_POST['is_public'] ?? 0;
        $is_visible_to_group = $_POST['is_visible_to_group'] ?? 0;

        // Validate inputs
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Habit name is required';
        }

        if (empty($start_date) || !validate_date($start_date)) {
            $errors['start_date'] = 'Valid start date is required';
        }

        if ($end_date && !validate_date($end_date)) {
            $errors['end_date'] = 'Valid end date is required';
        }

        if ($frequency === 'custom') {
            $custom_days = $_POST['custom_days'] ?? [];
            if (empty($custom_days)) {
                $errors['custom_days'] = 'Please select at least one day for custom frequency';
            }
        }

        if (empty($errors)) {
            try {
                // Handle custom days
                $custom_days_value = null;
                if ($frequency === 'custom' && !empty($custom_days)) {
                    $custom_days_value = implode(',', $custom_days);
                }

                $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, frequency, custom_days, start_date, end_date, category, is_public, is_visible_to_group) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $name, $description, $frequency, $custom_days_value, $start_date, $end_date, $category, $is_public, $is_visible_to_group]);

                if ($result) {
                    $habit_id = $pdo->lastInsertId();
                    error_log("Habit created successfully, ID: " . $habit_id);

                    // Achievement check after successful habit creation
                    check_achievements($pdo, $_SESSION['user_id'], 'habit_created', ['habit_id' => $habit_id]);

                    if ($isAjax) {
                        echo json_encode(['success' => true, 'message' => 'Habit created successfully']);
                        exit;
                    } else {
                        // Check if this is an AJAX request despite the header check
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                            echo json_encode(['success' => true, 'message' => 'Habit created successfully']);
                        } else {
                            header('Location: habits.php');
                        }
                        exit;
                    }
                } else {
                    error_log("Habit creation failed: " . print_r($stmt->errorInfo(), true));
                    $errors['general'] = 'Failed to create habit. Please try again.';
                }
            } catch (PDOException $e) {
                error_log("Habit creation error: " . $e->getMessage());
                $errors['general'] = 'Failed to create habit. Please try again.';
            }
        }

        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: add-habit.php');
            exit;
        }
    }

    if ($action === 'newsletter_subscribe') {
        $email = trim($_POST['email'] ?? '');

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
            exit;
        }

        try {
            // Insert new subscription
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscriptions (email) VALUES (?)");
            $result = $stmt->execute([$email]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'You have successfully subscribed to our newsletter!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Sorry, we couldn\'t process your subscription. Please try again.']);
            }
            exit;
        } catch (PDOException $e) {
            // Handle duplicate email error gracefully
            if ($e->getCode() == 23000) { // SQLSTATE[23000]: Integrity constraint violation
                echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter!']);
            } else {
                error_log("Newsletter subscription error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Sorry, we couldn\'t process your subscription. Please try again.']);
            }
            exit;
        }
    }
}

// If not a POST request, redirect to index
header('Location: index.php');
exit;
