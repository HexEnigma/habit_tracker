<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: groups.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Security token validation failed';
    header('Location: groups.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$group_id = $_POST['group_id'] ?? 0;

try {
    switch ($action) {
        case 'join':
            // Check if group exists and is public
            $stmt = $pdo->prepare("SELECT is_public FROM groups WHERE id = ?");
            $stmt->execute([$group_id]);
            $group = $stmt->fetch();

            if ($group && $group['is_public']) {
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $stmt->execute([$group_id, $user_id]);
                $_SESSION['success'] = 'Successfully joined the group!';
            } else {
                $_SESSION['error'] = 'Cannot join this group';
            }
            break;

        case 'add_member':
            // Verify user is admin of the group
            $stmt = $pdo->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $user_id]);
            $membership = $stmt->fetch();

            if ($membership && $membership['role'] === 'admin') {
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

                // Find user by email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $target_user = $stmt->fetch();

                if ($target_user) {
                    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$group_id, $target_user['id']]);
                    $_SESSION['success'] = 'User added to group!';
                } else {
                    $_SESSION['error'] = 'User with this email not found';
                }
            } else {
                $_SESSION['error'] = 'You need admin privileges to add members';
            }
            break;

        case 'send_message':
            $message = trim($_POST['message']);
            if (!empty($message)) {
                // Verify user is member of the group
                $stmt = $pdo->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?");
                $stmt->execute([$group_id, $user_id]);

                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, user_id, message) VALUES (?, ?, ?)");
                    $stmt->execute([$group_id, $user_id, $message]);
                    $_SESSION['success'] = 'Message sent!';
                } else {
                    $_SESSION['error'] = 'You are not a member of this group';
                }
            }
            break;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

// Redirect back to appropriate page
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'groups.php';
header("Location: $redirect");
exit;
