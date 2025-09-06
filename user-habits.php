<?php
require_once 'config.php';
require_login();

$viewed_user_id = $_GET['user_id'] ?? 0;
$current_user_id = $_SESSION['user_id'];

// Check if users share any groups
$stmt = $pdo->prepare("
    SELECT 1 FROM group_members gm1
    JOIN group_members gm2 ON gm1.group_id = gm2.group_id
    WHERE gm1.user_id = ? AND gm2.user_id = ?
    LIMIT 1
");
$stmt->execute([$current_user_id, $viewed_user_id]);
$share_group = $stmt->fetch();

if (!$share_group) {
    $_SESSION['error'] = 'You cannot view this user\'s habits';
    header('Location: groups.php');
    exit;
}

// Get user info
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$viewed_user_id]);
$viewed_user = $stmt->fetch();

if (!$viewed_user) {
    $_SESSION['error'] = 'User not found';
    header('Location: groups.php');
    exit;
}

// Get user's public habits
$stmt = $pdo->prepare("
    SELECT h.*, 
           (SELECT current_streak FROM user_streaks WHERE habit_id = h.id) as current_streak
    FROM habits h 
    WHERE h.user_id = ? AND (h.is_public = true OR h.user_id = ?)
    ORDER BY h.created_at DESC
");
$stmt->execute([$viewed_user_id, $current_user_id]);
$habits = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <?php echo htmlspecialchars($viewed_user['username']); ?>'s Habits
            </h1>
            <p class="text-gray-600">Viewing public habits</p>
        </div>
        <a href="javascript:history.back()" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i> Go Back
        </a>
    </div>

    <!-- Habits List -->
    <div class="grid grid-cols-1 gap-6">
        <?php if (empty($habits)): ?>
            <div class="app-card p-8 text-center">
                <i class="fas fa-list-check text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No public habits</h3>
                <p class="text-gray-500">This user hasn't made any habits public yet</p>
            </div>
        <?php else: ?>
            <?php foreach ($habits as $habit): ?>
                <div class="app-card p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-ellipsis-h text-blue-500"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($habit['name']); ?></h3>
                                <?php if (!empty($habit['description'])): ?>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($habit['description']); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center space-x-4 mt-1">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-800">
                                        <?php echo ucfirst($habit['frequency']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-fire mr-1"></i>
                                        <?php echo $habit['current_streak'] ?? 0; ?> day streak
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>