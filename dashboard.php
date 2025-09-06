<?php
require_once 'config.php';
require_login();

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user account creation date
$stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();
$user_created_at = $user_data['created_at'];
$user_created_at = date('Y-m-d', strtotime($user_created_at));

// Get user habits
$stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$habits = $stmt->fetchAll();

// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_habits FROM habits WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_habits = $stmt->fetch()['total_habits'];

// Get completion rate (last 30 days)
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total, 
           SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed
    FROM habit_progress hp
    JOIN habits h ON hp.habit_id = h.id
    WHERE h.user_id = ? AND hp.progress_date >= ?
");
$stmt->execute([$user_id, $thirty_days_ago]);
$completion_data = $stmt->fetch();
$completion_rate = $completion_data['total'] > 0
    ? round(($completion_data['completed'] / $completion_data['total']) * 100)
    : 0;

// Get user points
$stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
$stmt->execute([$user_id]);
$points = $stmt->fetch()['points'];

// Get user streaks
$stmt = $pdo->prepare("
    SELECT h.name, us.current_streak, us.longest_streak 
    FROM user_streaks us
    JOIN habits h ON us.habit_id = h.id
    WHERE us.user_id = ? AND us.current_streak > 0
    ORDER BY us.current_streak DESC
");
$stmt->execute([$user_id]);
$streaks = $stmt->fetchAll();

// Get user achievements
$stmt = $pdo->prepare("
    SELECT achievement_name 
    FROM user_achievements 
    WHERE user_id = ? 
    ORDER BY unlocked_at DESC
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();

require_once 'header.php';
?>

<!-- Dashboard View -->
<div id="dashboard-view" class="flex">
    <!-- Sidebar Navigation -->
    <div class="w-64 bg-white shadow-lg min-h-screen hidden md:block">
        <div class="p-6 border-b">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-500"></i>
                </div>
                <div>
                    <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($username); ?></div>
                    <div class="text-sm text-gray-500"><?php echo $points; ?> points</div>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-2">
            <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="add-habit.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-plus-circle w-5"></i>
                <span>Add Habit</span>
            </a>
            <a href="habits.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-list-check w-5"></i>
                <span>My Habits</span>
            </a>
            <a href="analytics.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-chart-bar w-5"></i>
                <span>Analytics</span>
            </a>
            <a href="reminders.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-bell w-5"></i>
                <span>Reminders</span>
            </a>
            <a href="achievements.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-trophy w-5"></i>
                <span>Achievements</span>
            </a>
            <a href="profile.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-user w-5"></i>
                <span>Profile</span>
            </a>
            <!-- UPDATE SETTINGS BUTTON LINK -->
            <a href="settings.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="p-4 border-t mt-4">
            <a href="auth.php?action=logout" class="flex items-center space-x-3 p-3 rounded-lg text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <!-- Mobile Header -->
        <div class="bg-white shadow-sm p-4 md:hidden">
            <div class="flex items-center justify-between">
                <button id="mobile-sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
                <div class="w-8"></div> <!-- Spacer for balance -->
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="p-6">
            <!-- Welcome Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <div class="flex flex-col mb-4 md:mb-0">
                    <div class="flex items-center mb-1">
                        <span class="text-lg greeting-text 
                            <?php
                            $hour = date('G');
                            if ($hour < 12) echo 'text-amber-600';
                            elseif ($hour < 17) echo 'text-blue-600';
                            else echo 'text-purple-600';
                            ?>">
                            <?php
                            if ($hour < 12) echo 'Good morning';
                            elseif ($hour < 17) echo 'Good afternoon';
                            else echo 'Good evening';
                            ?>,
                        </span>
                        <span class="text-lg font-semibold text-blue-600 ml-1"><?php echo htmlspecialchars($username); ?></span>
                        <span class="text-lg text-gray-600">!</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 bg-blue-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-star text-yellow-400"></i>
                        <span class="font-semibold"><?php echo $points; ?></span>
                        <span class="text-sm text-gray-600">points</span>
                    </div>
                    <!-- ADD LOGOUT BUTTON HERE -->
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="app-card p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-list-check text-blue-500 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mb-2"><?php echo $total_habits; ?></div>
                    <div class="text-gray-600">Total Habits</div>
                </div>

                <div class="app-card p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mb-2"><?php echo $completion_rate; ?>%</div>
                    <div class="text-gray-600">Completion Rate</div>
                </div>

                <div class="app-card p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-fire text-purple-500 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mb-2">
                        <?php echo !empty($streaks) ? $streaks[0]['current_streak'] : '0'; ?>
                    </div>
                    <div class="text-gray-600">Current Streak</div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- My Habits Section -->
                <div class="app-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list-check text-blue-500 mr-2"></i>
                            My Habits
                        </h2>
                        <a href="habits.php" class="text-blue-500 hover:text-blue-600 text-sm font-semibold">
                            View All →
                        </a>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($habits)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-list-check text-3xl text-gray-300 mb-3"></i>
                                <p>No habits yet</p>
                                <a href="add-habit.php" class="text-blue-500 hover:text-blue-600 text-sm">
                                    Add your first habit
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($habits, 0, 5) as $habit):
                                $today = date('Y-m-d');
                                $stmt = $pdo->prepare("SELECT completed FROM habit_progress WHERE habit_id = ? AND progress_date = ?");
                                $stmt->execute([$habit['id'], $today]);
                                $progress = $stmt->fetch();
                                $is_completed = $progress ? (bool)$progress['completed'] : false;

                                $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE habit_id = ?");
                                $stmt->execute([$habit['id']]);
                                $streak_data = $stmt->fetch();
                                $streak = $streak_data ? $streak_data['current_streak'] : 0;
                            ?>
                                <div class="flex items-center justify-between p-3 border rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $is_completed ? 'bg-green-100' : 'bg-yellow-100'; ?>">
                                            <i class="fas <?php echo $is_completed ? 'fa-check text-green-500' : 'fa-clock text-yellow-500'; ?> text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800"><?php echo htmlspecialchars($habit['name']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo ucfirst($habit['frequency']); ?></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500"><?php echo $streak; ?>d</span>
                                        <?php if (!$is_completed): ?>
                                            <button class="complete-habit-btn p-1 text-green-500 hover:bg-green-100 rounded"
                                                data-habit-id="<?php echo $habit['id']; ?>" title="Mark as complete">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Enhanced Achievements Section -->
                <div class="app-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-trophy text-blue-500 mr-2"></i>
                            Recent Achievements
                        </h2>
                        <a href="achievements.php" class="text-blue-500 hover:text-blue-600 text-sm font-semibold">
                            View All →
                        </a>
                    </div>

                    <?php if (empty($achievements)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-trophy text-3xl text-gray-300 mb-3"></i>
                            <p class="text-lg font-medium mb-2">No achievements yet</p>
                            <p class="text-sm">Complete habits to unlock achievements!</p>
                            <div class="mt-4 grid grid-cols-4 gap-2 opacity-60">
                                <div class="text-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-seedling text-green-500"></i>
                                    </div>
                                    <p class="text-xs">First Habit</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-fire text-purple-500"></i>
                                    </div>
                                    <p class="text-xs">7-Day Streak</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-list-check text-blue-500"></i>
                                    </div>
                                    <p class="text-xs">3 Habits</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-star text-amber-500"></i>
                                    </div>
                                    <p class="text-xs">10 Points</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $achievement_count = 0;
                            foreach ($achievements as $achievement):
                                if ($achievement_count >= 4) break; // Limit to 4 achievements
                                $icon_class = '';
                                $color_class = 'bg-blue-100 text-blue-600';
                                $title = '';

                                switch ($achievement['achievement_name']) {
                                    case 'first_habit':
                                    case 'first-habit':
                                        $icon_class = 'fa-seedling';
                                        $color_class = 'bg-green-100 text-green-600';
                                        $title = 'First Habit Created';
                                        break;
                                    case 'three_day_streak':
                                    case 'three-day-streak':
                                        $icon_class = 'fa-calendar-check';
                                        $color_class = 'bg-purple-100 text-purple-600';
                                        $title = '3-Day Streak';
                                        break;
                                    case 'seven_day_streak':
                                    case 'seven-day-streak':
                                        $icon_class = 'fa-fire';
                                        $color_class = 'bg-orange-100 text-orange-600';
                                        $title = '7-Day Streak';
                                        break;
                                    case 'thirty_day_streak':
                                    case 'thirty-day-streak':
                                        $icon_class = 'fa-award';
                                        $color_class = 'bg-yellow-100 text-yellow-600';
                                        $title = '30-Day Streak';
                                        break;
                                    case 'three_habits':
                                    case 'three-habits':
                                        $icon_class = 'fa-list-check';
                                        $color_class = 'bg-indigo-100 text-indigo-600';
                                        $title = '3 Active Habits';
                                        break;
                                    case 'ten_points':
                                    case 'ten-points':
                                        $icon_class = 'fa-star';
                                        $color_class = 'bg-amber-100 text-amber-600';
                                        $title = '10 Points Earned';
                                        break;
                                    case 'five_habits':
                                    case 'five-habits':
                                        $icon_class = 'fa-list-ol';
                                        $color_class = 'bg-teal-100 text-teal-600';
                                        $title = '5 Active Habits';
                                        break;
                                    case 'fifty_points':
                                    case 'fifty-points':
                                        $icon_class = 'fa-trophy';
                                        $color_class = 'bg-rose-100 text-rose-600';
                                        $title = '50 Points Earned';
                                        break;
                                    default:
                                        $icon_class = 'fa-trophy';
                                        $title = ucfirst(str_replace(['_', '-'], ' ', $achievement['achievement_name']));
                                }

                                // Format the date if available
                                $unlocked_date = '';
                                if (!empty($achievement['unlocked_at'])) {
                                    $unlocked_date = date('M j, Y', strtotime($achievement['unlocked_at']));
                                }
                            ?>
                                <div class="flex items-center p-4 bg-white rounded-lg border border-gray-200 hover:shadow-md transition">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center <?php echo $color_class; ?> mr-4 flex-shrink-0">
                                        <i class="fas <?php echo $icon_class; ?> text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 truncate"><?php echo $title; ?></div>
                                        <?php if ($unlocked_date): ?>
                                            <div class="text-sm text-gray-500">Unlocked <?php echo $unlocked_date; ?></div>
                                        <?php else: ?>
                                            <div class="text-sm text-gray-500">Achievement unlocked</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php
                                $achievement_count++;
                            endforeach; ?>
                        </div>

                        <?php if (count($achievements) > 4): ?>
                            <div class="mt-4 text-center">
                                <p class="text-sm text-gray-500">
                                    +<?php echo (count($achievements) - 4); ?> more achievements
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Social Groups Widget -->
                <div class="app-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-users text-blue-500 mr-2"></i>
                            Your Groups
                        </h2>
                        <a href="groups.php" class="text-blue-500 hover:text-blue-600 text-sm font-semibold">
                            Manage Groups →
                        </a>
                    </div>

                    <?php
                    // Get user's groups
                    $stmt = $pdo->prepare("
                        SELECT g.*, 
                               (SELECT COUNT(*) FROM group_messages gm WHERE gm.group_id = g.id AND gm.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)) as recent_activity
                        FROM groups g
                        JOIN group_members gm ON g.id = gm.group_id
                        WHERE gm.user_id = ?
                        ORDER BY recent_activity DESC, g.name ASC
                        LIMIT 3
                    ");
                    $stmt->execute([$user_id]);
                    $user_groups = $stmt->fetchAll();
                    ?>

                    <?php if (empty($user_groups)): ?>
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-users text-3xl text-gray-300 mb-2"></i>
                            <p class="mb-2">You're not in any groups yet</p>
                            <a href="groups.php" class="text-blue-500 hover:text-blue-600 text-sm font-semibold">
                                Join or create a group
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($user_groups as $group):
                                // Get unread message count (simplified - would need message tracking in real implementation)
                                $stmt = $pdo->prepare("
                                    SELECT COUNT(*) as unread_count 
                                    FROM group_messages 
                                    WHERE group_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)
                                ");
                                $stmt->execute([$group['id']]);
                                $unread_data = $stmt->fetch();
                                $unread_count = $unread_data['unread_count'];
                            ?>
                                <a href="group.php?id=<?php echo $group['id']; ?>" class="block p-3 border rounded-lg hover:bg-gray-50 transition group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-800 truncate group-hover:text-blue-600 transition">
                                                <?php echo htmlspecialchars($group['name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500 truncate">
                                                <?php echo $group['recent_activity'] > 0 ? $group['recent_activity'] . ' recent messages' : 'No recent activity'; ?>
                                            </p>
                                        </div>
                                        <?php if ($unread_count > 0): ?>
                                            <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                                <?php echo $unread_count; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="groups.php" class="text-blue-500 hover:text-blue-600 text-sm font-semibold flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                View all groups
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="app-card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-blue-500 mr-2"></i>
                        Quick Actions
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="add-habit.php" class="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
                            <i class="fas fa-plus-circle text-blue-500 text-xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-800">Add Habit</div>
                        </a>
                        <a href="analytics.php" class="p-4 bg-green-50 rounded-lg text-center hover:bg-green-100 transition">
                            <i class="fas fa-chart-bar text-green-500 text-xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-800">Analytics</div>
                        </a>
                        <a href="reminders.php" class="p-4 bg-purple-50 rounded-lg text-center hover:bg-purple-100 transition">
                            <i class="fas fa-bell text-purple-500 text-xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-800">Reminders</div>
                        </a>
                        <a href="settings.php" class="p-4 bg-gray-50 rounded-lg text-center hover:bg-gray-100 transition">
                            <i class="fas fa-cog text-gray-500 text-xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-800">Settings</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="fixed inset-0 z-50 hidden md:hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" id="mobile-sidebar-backdrop"></div>
    <div class="absolute left-0 top-0 h-full w-64 bg-white shadow-xl transform -translate-x-full transition-transform duration-300" id="mobile-sidebar-content">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-500"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($username); ?></div>
                        <div class="text-sm text-gray-500"><?php echo $points; ?> points</div>
                    </div>
                </div>
                <button id="close-mobile-sidebar" class="p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times text-gray-600"></i>
                </button>
            </div>
        </div>

        <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100%-8rem)]">
            <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="add-habit.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-plus-circle w-5"></i>
                <span>Add Habit</span>
            </a>
            <a href="habits.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-list-check w-5"></i>
                <span>My Habits</span>
            </a>
            <a href="analytics.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-chart-bar w-5"></i>
                <span>Analytics</span>
            </a>
            <a href="reminders.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-bell w-5"></i>
                <span>Reminders</span>
            </a>
            <a href="achievements.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-trophy w-5"></i>
                <span>Achievements</span>
            </a>
            <a href="profile.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-user w-5"></i>
                <span>Profile</span>
            </a>
            <a href="settings.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="p-4 border-t absolute bottom-0 w-full bg-white">
            <a href="auth.php?action=logout" class="flex items-center space-x-3 p-3 rounded-lg text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<script>
    // Mobile sidebar functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileSidebarContent = document.getElementById('mobile-sidebar-content');
        const mobileSidebarBackdrop = document.getElementById('mobile-sidebar-backdrop');
        const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

        function openSidebar() {
            mobileSidebar.classList.remove('hidden');
            setTimeout(() => {
                mobileSidebarContent.classList.remove('-translate-x-full');
            }, 10);
        }

        function closeSidebar() {
            mobileSidebarContent.classList.add('-translate-x-full');
            setTimeout(() => {
                mobileSidebar.classList.add('hidden');
            }, 300);
        }

        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', openSidebar);
        }

        if (closeMobileSidebar) {
            closeMobileSidebar.addEventListener('click', closeSidebar);
        }

        if (mobileSidebarBackdrop) {
            mobileSidebarBackdrop.addEventListener('click', closeSidebar);
        }

        // Close sidebar when clicking on links
        const sidebarLinks = mobileSidebarContent.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    });
</script>

<?php require_once 'footer.php'; ?>