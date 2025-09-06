<?php
// Temporary debug - habit creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    error_log("=== HABIT CREATION DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
}

require_once 'config.php';
//session_start();

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
        } elseif (strlen($username) > 50) {
            $errors['username'] = 'Username must be less than 50 characters';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } elseif (strlen($email) > 100) {
            $errors['email'] = 'Email must be less than 100 characters';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // Check if email already exists
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Email already exists';
            }
        }

        // If no errors, create user
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash]);

                $user_id = $pdo->lastInsertId();
                // Debug: Check if user was created
                error_log("New user ID: " . $user_id);

                // Initialize user settings
                $stmt = $pdo->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
                $stmt->execute([$user_id]);

                // Initialize user points
                $stmt = $pdo->prepare("INSERT INTO user_points (user_id, points) VALUES (?, 0)");
                $stmt->execute([$user_id]);

                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Account created successfully']);
                    exit;
                } else {
                    // Redirect to dashboard for form submissions
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Signup error: " . $e->getMessage());
                $errors['general'] = 'Failed to create account. Please try again.';
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
                    SET name = ?, description = ?, frequency = ?, custom_days = ?, start_date = ?, end_date = ?, category = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $name,
                    $description,
                    $frequency,
                    $custom_days_value,
                    $start_date,
                    $end_date,
                    $category,
                    $habit_id,
                    $_SESSION['user_id']
                ]);

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

                $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, frequency, custom_days, start_date, end_date, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $name, $description, $frequency, $custom_days_value, $start_date, $end_date, $category]);

                if ($result) {
                    $habit_id = $pdo->lastInsertId();
                    error_log("Habit created successfully, ID: " . $habit_id);

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
}

// If not a POST request, redirect to index
header('Location: index.php');
exit;
