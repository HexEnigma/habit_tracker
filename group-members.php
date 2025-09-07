<?php
require_once 'config.php';
require_login();

$group_id = $_GET['group_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user is member of this group and group allows member visibility
$stmt = $pdo->prepare("
    SELECT g.*, gm.role 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE g.id = ? AND gm.user_id = ? AND g.allow_member_visibility = 1
");
$stmt->execute([$group_id, $user_id]);
$group = $stmt->fetch();

if (!$group) {
    $_SESSION['error'] = 'Group not found or member visibility is disabled';
    header('Location: groups.php');
    exit;
}

// Get all group members
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, up.points, gm.joined_at
    FROM group_members gm
    JOIN users u ON gm.user_id = u.id
    LEFT JOIN user_points up ON u.id = up.user_id
    WHERE gm.group_id = ?
    ORDER BY u.username ASC
");
$stmt->execute([$group_id]);
$members = $stmt->fetchAll();

// Get public habits for each member
$member_habits = [];
foreach ($members as $member) {
    $stmt = $pdo->prepare("
        SELECT h.*, 
               (SELECT COUNT(*) FROM habit_progress hp WHERE hp.habit_id = h.id AND hp.completed = 1) as completed_count,
               (SELECT COUNT(*) FROM habit_progress hp WHERE hp.habit_id = h.id) as total_count
        FROM habits h 
        WHERE h.user_id = ? AND (h.is_public = 1 OR h.is_visible_to_group = 1)
        ORDER BY h.created_at DESC
    ");
    $stmt->execute([$member['id']]);
    $member_habits[$member['id']] = $stmt->fetchAll();
}

require_once 'header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Group Members - <?php echo htmlspecialchars($group['name']); ?></h1>
            <p class="text-gray-600">View public habits and progress of your group members</p>
        </div>
        <a href="group.php?id=<?php echo $group_id; ?>" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Group
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <?php foreach ($members as $member): ?>
            <div class="app-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-500"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($member['username']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo $member['points'] ?? 0; ?> points</p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500">
                        Joined <?php echo date('M j, Y', strtotime($member['joined_at'])); ?>
                    </span>
                </div>

                <h4 class="font-medium text-gray-700 mb-3">Public Habits</h4>
                <div class="space-y-3">
                    <?php if (empty($member_habits[$member['id']])): ?>
                        <p class="text-gray-500 text-sm">No shared habits yet</p>
                    <?php else: ?>
                        <?php foreach ($member_habits[$member['id']] as $habit):
                            $completion_rate = $habit['total_count'] > 0
                                ? round(($habit['completed_count'] / $habit['total_count']) * 100)
                                : 0;
                        ?>
                            <div class="member-habit-card flex items-center justify-between p-4 border rounded-lg mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($habit['name']); ?></div>
                                        <?php if ($habit['is_visible_to_group']): ?>
                                            <span class="group-visible-badge">Group</span>
                                        <?php endif; ?>
                                        <?php if ($habit['is_public']): ?>
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded ml-2">Public</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-sm text-gray-600 mb-2">
                                        <?php echo ucfirst($habit['frequency']); ?> • <?php echo ucfirst($habit['category']); ?>
                                    </div>

                                    <?php if (!empty($habit['description'])): ?>
                                        <p class="text-sm text-gray-500 mb-2"><?php echo htmlspecialchars($habit['description']); ?></p>
                                    <?php endif; ?>

                                    <!-- Progress bar -->
                                    <div class="habit-progress-bar">
                                        <div class="habit-progress-fill" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                </div>

                                <div class="text-right ml-4">
                                    <div class="text-lg font-semibold <?php echo $completion_rate >= 80 ? 'text-green-600' : ($completion_rate >= 50 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                        <?php echo $completion_rate; ?>%
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo $habit['completed_count']; ?>/<?php echo $habit['total_count']; ?> completed
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>