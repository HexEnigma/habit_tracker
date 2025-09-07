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

// Function to handle daily login rewards and streaks
function handle_daily_login($pdo, $user_id)
{
    $today = date('Y-m-d');

    // Check if user already logged in today
    $stmt = $pdo->prepare("SELECT last_login_date, login_streak FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();

    $last_login = $settings['last_login_date'] ?? null;
    $current_streak = $settings['login_streak'] ?? 0;

    if (!$last_login || $last_login != $today) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Check if consecutive login
        if ($last_login == $yesterday) {
            $new_streak = $current_streak + 1;
        } else {
            $new_streak = 1; // Reset streak
        }

        // Award login point
        $stmt = $pdo->prepare("UPDATE user_points SET points = points + 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Update login streak and date
        if ($settings) {
            $stmt = $pdo->prepare("UPDATE user_settings SET last_login_date = ?, login_streak = ? WHERE user_id = ?");
            $stmt->execute([$today, $new_streak, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, last_login_date, login_streak) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $today, $new_streak]);
        }

        // Award bonus for streak milestones
        if ($new_streak % 7 == 0) {
            $bonus_points = floor($new_streak / 7) * 5;
            $stmt = $pdo->prepare("UPDATE user_points SET points = points + ? WHERE user_id = ?");
            $stmt->execute([$bonus_points, $user_id]);
        }

        return ['points' => 1, 'streak' => $new_streak];
    }

    return false;
}

// Function to check and award achievements
function check_achievements($pdo, $user_id, $action, $context = [])
{
    $achievements = [
        'first-habit' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() == 1;
        },
        'three-habits' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() >= 3;
        },
        'ten-points' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() >= 10;
        },
        'group-explorer' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM group_members WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() >= 1;
        },
        'social-butterfly' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT group_id) FROM group_members WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() >= 3;
        },
        'habit-mentor' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = ? AND is_visible_to_group = 1");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn() >= 5;
        },
        'three-day-streak' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $streaks = $stmt->fetchAll();
            foreach ($streaks as $streak) {
                if ($streak['current_streak'] >= 3) {
                    return true;
                }
            }
            return false;
        },
        'seven-day-streak' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $streaks = $stmt->fetchAll();
            foreach ($streaks as $streak) {
                if ($streak['current_streak'] >= 7) {
                    return true;
                }
            }
            return false;
        },
        'fourteen-day-streak' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $streaks = $stmt->fetchAll();
            foreach ($streaks as $streak) {
                if ($streak['current_streak'] >= 14) {
                    return true;
                }
            }
            return false;
        },
        'thirty-day-streak' => function ($pdo, $user_id) {
            $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $streaks = $stmt->fetchAll();
            foreach ($streaks as $streak) {
                if ($streak['current_streak'] >= 30) {
                    return true;
                }
            }
            return false;
        }
    ];

    foreach ($achievements as $achievement_name => $check_function) {
        // Check if user already has this achievement
        $stmt = $pdo->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_name = ?");
        $stmt->execute([$user_id, $achievement_name]);

        if (!$stmt->fetch() && $check_function($pdo, $user_id)) {
            // Award achievement
            $stmt = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_name) VALUES (?, ?)");
            $stmt->execute([$user_id, $achievement_name]);

            // Add points for achievement
            $stmt = $pdo->prepare("UPDATE user_points SET points = points + 5 WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
    }
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
