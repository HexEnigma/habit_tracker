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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Group Chat -->
        <div class="lg:col-span-2">
            <div class="app-card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Group Chat</h2>

                <!-- Messages -->
                <div class="h-96 overflow-y-auto mb-4 space-y-4">
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
                <form method="POST" action="group-actions.php" class="flex space-x-2">
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
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-500 text-sm"></i>
                                </div>
                                <div>
                                    <a href="user-habits.php?user_id=<?php echo $member['id']; ?>"
                                        class="font-medium text-gray-800 hover:text-blue-600">
                                        <?php echo htmlspecialchars($member['username']); ?>
                                    </a>
                                    <p class="text-xs text-gray-500"><?php echo $member['role']; ?></p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400">
                                <?php echo date('M j', strtotime($member['joined_at'])); ?>
                            </span>
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
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>