<?php
session_start();
// Set correct timezone for Bangladesh
date_default_timezone_set('Asia/Dhaka');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'habit_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("ERROR: Could not connect to database. Please try again later.");
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['email']);
}

// Redirect if not logged in
function require_login()
{
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}



// CSRF protection functions
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate date format
function validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Function to check and mark missed habits (runs once per day per user)
function check_missed_habits($pdo, $user_id)
{
    $today = date('Y-m-d');

    // Check if we've already run today
    $stmt = $pdo->prepare("SELECT last_missed_check FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();

    if ($settings && $settings['last_missed_check'] == $today) {
        return; // Already checked today
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Get all active habits
    $stmt = $pdo->prepare("
        SELECT id, frequency, custom_days, start_date, end_date 
        FROM habits 
        WHERE user_id = ? AND (end_date IS NULL OR end_date >= ?) AND start_date <= ?
    ");
    $stmt->execute([$user_id, $yesterday, $yesterday]);
    $habits = $stmt->fetchAll();

    foreach ($habits as $habit) {
        // Check if habit should have been done yesterday
        $should_have_done = false;
        $day_of_week = date('N', strtotime($yesterday)); // 1=Monday, 7=Sunday

        if ($habit['frequency'] === 'daily') {
            $should_have_done = true;
        } elseif ($habit['frequency'] === 'weekly') {
            // Weekly habits are done on specific days
            if (!empty($habit['custom_days'])) {
                $custom_days = explode(',', $habit['custom_days']);
                $should_have_done = in_array($day_of_week, $custom_days);
            }
        } elseif ($habit['frequency'] === 'custom' && !empty($habit['custom_days'])) {
            $custom_days = explode(',', $habit['custom_days']);
            $should_have_done = in_array($day_of_week, $custom_days);
        }

        // If habit should have been done yesterday but wasn't, mark as missed
        if ($should_have_done) {
            // Check if already has an entry for yesterday
            $stmt = $pdo->prepare("SELECT id FROM habit_progress WHERE habit_id = ? AND progress_date = ?");
            $stmt->execute([$habit['id'], $yesterday]);

            if (!$stmt->fetch()) {
                // No entry for yesterday, mark as missed
                $stmt = $pdo->prepare("INSERT INTO habit_progress (habit_id, progress_date, completed) VALUES (?, ?, 0)");
                $stmt->execute([$habit['id'], $yesterday]);
            }
        }
    }

    // Update last check date
    if ($settings) {
        $stmt = $pdo->prepare("UPDATE user_settings SET last_missed_check = ? WHERE user_id = ?");
        $stmt->execute([$today, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, last_missed_check) VALUES (?, ?)");
        $stmt->execute([$user_id, $today]);
    }
}
