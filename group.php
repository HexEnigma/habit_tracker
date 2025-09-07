<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$group_id = $_GET['id'] ?? 0;
$csrf_token = generate_csrf_token();

// Get group info and verify membership
$stmt = $pdo->prepare("
    SELECT g.*, gm.role 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE g.id = ? AND gm.user_id = ?
");
$stmt->execute([$group_id, $user_id]);
$group = $stmt->fetch();

if (!$group) {
    $_SESSION['error'] = 'Group not found or access denied';
    header('Location: groups.php');
    exit;
}

// Get group members
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, gm.role, gm.joined_at
    FROM group_members gm
    JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = ?
    ORDER BY gm.role DESC, gm.joined_at ASC
");
$stmt->execute([$group_id]);
$members = $stmt->fetchAll();

// Handle member removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Security token validation failed';
        header("Location: group.php?id=$group_id");
        exit;
    }

    $member_id = $_POST['member_id'];

    // Verify admin privileges
    if ($group['role'] === 'admin') {
        try {
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $member_id]);

            $_SESSION['success'] = 'Member removed successfully';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to remove member: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'You need admin privileges to remove members';
    }

    header("Location: group.php?id=$group_id");
    exit;
}

// Handle group deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_group'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Security token validation failed';
        header("Location: groups.php");
        exit;
    }

    // Verify admin privileges
    if ($group['role'] === 'admin') {
        try {
            // Delete group messages first
            $stmt = $pdo->prepare("DELETE FROM group_messages WHERE group_id = ?");
            $stmt->execute([$group_id]);

            // Delete group members
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ?");
            $stmt->execute([$group_id]);

            // Delete the group
            $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
            $stmt->execute([$group_id]);

            $_SESSION['success'] = 'Group deleted successfully';
            header("Location: groups.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to delete group: ' . $e->getMessage();
            header("Location: group.php?id=$group_id");
            exit;
        }
    } else {
        $_SESSION['error'] = 'You need admin privileges to delete the group';
        header("Location: group.php?id=$group_id");
        exit;
    }
}

// Get group messages
$stmt = $pdo->prepare("
    SELECT gm.*, u.username 
    FROM group_messages gm
    JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = ?
    ORDER BY gm.created_at DESC
    LIMIT 50
");
$stmt->execute([$group_id]);
$messages = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($group['name']); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($group['description']); ?></p>
        </div>
        <div class="flex space-x-2">
            <a href="groups.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Groups
            </a>
            <a href="dashboard.php" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Group Chat -->
        <div class="lg:w-2/3">
            <div class="app-card p-6 h-full flex flex-col">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Group Chat</h2>

                <!-- Messages Container - This will now expand to fill available space -->
                <div class="flex-1 overflow-y-auto mb-4 space-y-4" style="max-height: 50vh;">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comments text-3xl text-gray-300 mb-3"></i>
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_reverse($messages) as $message): ?>
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-blue-500 text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($message['username']); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo date('M j, g:i a', strtotime($message['created_at'])); ?></span>
                                    </div>
                                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($message['message']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Form -->
                <form method="POST" action="group-actions.php" class="flex space-x-2 mt-auto">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="hidden" name="redirect" value="group.php?id=<?php echo $group_id; ?>">

                    <input type="text" name="message" placeholder="Type your message..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-600 transition">
                        Send
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Members -->
            <div class="app-card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Members</h2>
                <div class="space-y-3">
                    <?php foreach ($members as $member): ?>
                        <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-500 text-sm"></i>
                                </div>
                                <div>
                                    <div class="flex items-center">
                                        <a href="user-habits.php?user_id=<?php echo $member['id']; ?>"
                                            class="font-medium text-gray-800 hover:text-blue-600">
                                            <?php echo htmlspecialchars($member['username']); ?>
                                        </a>
                                        <?php if ($member['role'] === 'admin'): ?>
                                            <span class="admin-badge ml-2">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-gray-500"><?php echo $member['role']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-400">
                                    <?php echo date('M j', strtotime($member['joined_at'])); ?>
                                </span>

                                <?php if ($group['role'] === 'admin' && $member['id'] != $user_id): ?>
                                    <form method="POST" class="remove-member-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="remove_member" value="1">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <button type="button" onclick="confirmRemoveMember('<?php echo htmlspecialchars($member['username']); ?>', this.closest('form'))"
                                            class="remove-member-btn" title="Remove member">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($group['role'] === 'admin'): ?>
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="font-semibold text-gray-700 mb-2">Add Member</h3>
                        <form method="POST" action="group-actions.php" class="flex space-x-2">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="add_member">
                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                            <input type="hidden" name="redirect" value="group.php?id=<?php echo $group_id; ?>">

                            <input type="email" name="email" placeholder="Member's email"
                                class="flex-1 px-3 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm font-semibold hover:bg-green-600 transition">
                                Add
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Group Info -->
            <div class="app-card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Group Info</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created</span>
                        <span class="text-gray-800"><?php echo date('M j, Y', strtotime($group['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Members</span>
                        <span class="text-gray-800"><?php echo count($members); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Privacy</span>
                        <span class="text-gray-800"><?php echo $group['is_public'] ? 'Public' : 'Private'; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Your Role</span>
                        <span class="text-gray-800 capitalize"><?php echo $group['role']; ?></span>
                    </div>
                </div>
                <?php if ($group['allow_member_visibility']): ?>
                    <a href="group-members.php?group_id=<?php echo $group_id; ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition inline-block mt-4">
                        <i class="fas fa-users mr-2"></i> View Members' Habits
                    </a>
                <?php endif; ?>

                <?php if ($group['role'] === 'admin'): ?>
                    <div class="admin-controls">
                        <h3 class="font-semibold text-red-700 mb-3">Admin Controls</h3>
                        <form method="POST" class="delete-group-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="delete_group" value="1">
                            <button type="button" onclick="confirmDeleteGroup('<?php echo htmlspecialchars($group['name']); ?>', this.closest('form'))"
                                class="delete-group-btn w-full text-center">
                                <i class="fas fa-trash mr-2"></i>Delete Group
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Confirm member removal
    function confirmRemoveMember(username, form) {
        if (confirm(`Are you sure you want to remove ${username} from the group? This action cannot be undone.`)) {
            form.submit();
        }
    }

    // Confirm group deletion
    function confirmDeleteGroup(groupName, form) {
        if (confirm(`WARNING: Are you sure you want to delete the group "${groupName}"? This will permanently delete all group data, messages, and member associations. This action cannot be undone!`)) {
            form.submit();
        }
    }

    // Show success/error messages
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</script>

<?php require_once 'footer.php'; ?>