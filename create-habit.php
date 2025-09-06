<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $frequency = $_POST['frequency'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?? null;
    $category = $_POST['category'];

    // Handle custom days
    $custom_days_value = null;
    if ($frequency === 'custom' && !empty($_POST['custom_days'])) {
        $custom_days_value = implode(',', $_POST['custom_days']);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, frequency, custom_days, start_date, end_date, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $name, $description, $frequency, $custom_days_value, $start_date, $end_date, $category]);

        if ($result) {
            $_SESSION['success'] = 'Habit created successfully!';
            header('Location: habits.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to create habit: ' . $e->getMessage();
        header('Location: add-habit.php');
        exit;
    }
}

header('Location: add-habit.php');
exit;
