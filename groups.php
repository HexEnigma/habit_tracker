<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$csrf_token = generate_csrf_token();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Security token validation failed';
        header('Location: groups.php');
        exit;
    }

    if (isset($_POST['create_group'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $allow_member_visibility = isset($_POST['allow_member_visibility']) ? 1 : 0;

        if (!empty($name)) {
            try {
                $pdo->beginTransaction();

                // Create group
                $stmt = $pdo->prepare("INSERT INTO groups (name, description, created_by, is_public, allow_member_visibility) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $user_id, $is_public, $allow_member_visibility]);
                $group_id = $pdo->lastInsertId();

                // Add creator as admin
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
                $stmt->execute([$group_id, $user_id]);

                $pdo->commit();
                $_SESSION['success'] = 'Group created successfully!';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = 'Failed to create group: ' . $e->getMessage();
            }
        }
    }

    header('Location: groups.php');
    exit;
}

// Get user's groups
$stmt = $pdo->prepare("
    SELECT g.*, gm.role 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ? 
    ORDER BY g.name ASC
");
$stmt->execute([$user_id]);
$user_groups = $stmt->fetchAll();

// Get public groups user can join
$stmt = $pdo->prepare("
    SELECT g.* 
    FROM groups g 
    WHERE g.is_public = true 
    AND g.id NOT IN (SELECT group_id FROM group_members WHERE user_id = ?)
    ORDER BY g.created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$public_groups = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Groups</h1>
            <p class="text-gray-600">Join groups to stay motivated with others</p>
        </div>
        <a href="dashboard.php" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition flex items-center">
            <i class="fas fa-home mr-2"></i> Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Create Group Form -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Create New Group</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_public" id="is_public" checked class="mr-2">
                    <label for="is_public" class="text-sm text-gray-700">Public group (anyone can join)</label>
                </div>

                <div class="flex items-center mt-4">
                    <input type="checkbox" name="allow_member_visibility" id="allow_member_visibility" checked class="mr-2">
                    <label for="allow_member_visibility" class="text-sm text-gray-700">Allow group members to view each other's habits and points</label>
                </div>

                <button type="submit" name="create_group" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-600 transition">
                    Create Group
                </button>
            </form>
        </div>

        <!-- Your Groups -->
        <div class="app-card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Groups</h2>

            <?php if (empty($user_groups)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-3xl text-gray-300 mb-3"></i>
                    <p>You haven't joined any groups yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($user_groups as $group): ?>
                        <a href="group.php?id=<?php echo $group['id']; ?>" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($group['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo $group['description'] ? htmlspecialchars($group['description']) : 'No description'; ?></p>
                                </div>
                                <span class="bg-blue-100 text-blue-600 text-xs font-semibold px-2 py-1 rounded-full">
                                    <?php echo $group['role']; ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Public Groups -->
    <div class="app-card p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Public Groups You Can Join</h2>

        <?php if (empty($public_groups)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-search text-3xl text-gray-300 mb-3"></i>
                <p>No public groups available at the moment</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($public_groups as $group): ?>
                    <div class="p-4 border rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($group['name']); ?></h3>
                        <p class="text-sm text-gray-500 mb-3"><?php echo $group['description'] ? htmlspecialchars(substr($group['description'], 0, 100)) . '...' : 'No description'; ?></p>

                        <form method="POST" action="group-actions.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                            <input type="hidden" name="action" value="join">

                            <button type="submit" class="w-full bg-green-500 text-white py-1 px-3 rounded-lg text-sm font-semibold hover:bg-green-600 transition">
                                Join Group
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>