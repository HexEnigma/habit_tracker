<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$csrf_token = generate_csrf_token();

// Get all available achievements
$stmt = $pdo->prepare("SELECT * FROM achievement_definitions ORDER BY id");
$stmt->execute();
$all_achievements = $stmt->fetchAll();

// Get user's unlocked achievements
$stmt = $pdo->prepare("
    SELECT ua.*, ad.description, ad.icon_class 
    FROM user_achievements ua 
    JOIN achievement_definitions ad ON ua.achievement_name = ad.achievement_name 
    WHERE ua.user_id = ? 
    ORDER BY ua.unlocked_at DESC
");
$stmt->execute([$user_id]);
$user_achievements = $stmt->fetchAll();

// Get user points
$stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_points = $stmt->fetch()['points'];

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Achievements</h1>
            <p class="text-gray-600">Track your progress and unlocked achievements</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-200 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <div class="bg-blue-50 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-star text-yellow-400 mr-2"></i>
                <span class="font-semibold"><?php echo $user_points; ?></span>
                <span class="text-sm text-gray-600 ml-1">points</span>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="app-card p-6 text-center">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trophy text-purple-500 text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-gray-800 mb-2">
                <?php echo count($user_achievements); ?>
            </div>
            <div class="text-gray-600">Unlocked Achievements</div>
        </div>

        <div class="app-card p-6 text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock-open text-blue-500 text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-gray-800 mb-2">
                <?php echo count($all_achievements) - count($user_achievements); ?>
            </div>
            <div class="text-gray-600">Achievements Left</div>
        </div>

        <div class="app-card p-6 text-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-percentage text-green-500 text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-gray-800 mb-2">
                <?php echo count($all_achievements) > 0 ? round((count($user_achievements) / count($all_achievements)) * 100) : 0; ?>%
            </div>
            <div class="text-gray-600">Completion Rate</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="app-card p-6 mb-8">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700">Progress</span>
            <span class="text-sm text-gray-500">
                <?php echo count($user_achievements); ?>/<?php echo count($all_achievements); ?> achievements
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-blue-500 h-3 rounded-full transition-all duration-500"
                style="width: <?php echo count($all_achievements) > 0 ? (count($user_achievements) / count($all_achievements)) * 100 : 0; ?>%">
            </div>
        </div>
    </div>

    <!-- Achievements Grid -->
    <div class="app-card p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            All Achievements
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($all_achievements as $achievement):
                $is_unlocked = false;
                $unlocked_date = '';

                foreach ($user_achievements as $user_achievement) {
                    if ($user_achievement['achievement_name'] === $achievement['achievement_name']) {
                        $is_unlocked = true;
                        $unlocked_date = $user_achievement['unlocked_at'];
                        break;
                    }
                }
            ?>
                <div class="flex items-center p-4 border rounded-lg transition-all duration-300 <?php echo $is_unlocked ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200' : 'bg-gray-50 border-gray-200 opacity-75'; ?>">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mr-4 flex-shrink-0 <?php echo $is_unlocked ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-400'; ?>">
                        <i class="fas <?php echo $achievement['icon_class']; ?> text-lg"></i>
                    </div>

                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-800 <?php echo $is_unlocked ? '' : 'text-gray-500'; ?>">
                            <?php echo ucfirst(str_replace(['-', '_'], ' ', $achievement['achievement_name'])); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo $achievement['description']; ?></p>

                        <?php if ($is_unlocked && $unlocked_date): ?>
                            <p class="text-xs text-green-600 mt-2">
                                <i class="fas fa-unlock mr-1"></i>
                                Unlocked <?php echo date('M j, Y', strtotime($unlocked_date)); ?>
                            </p>
                        <?php elseif (!$is_unlocked): ?>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-lock mr-1"></i>
                                Locked - Keep progressing to unlock!
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_unlocked): ?>
                        <div class="ml-2 text-green-500">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Unlocks -->
    <?php if (!empty($user_achievements)): ?>
        <div class="app-card p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-history text-blue-500 mr-2"></i>
                Recently Unlocked
            </h2>

            <div class="space-y-4">
                <?php foreach (array_slice($user_achievements, 0, 5) as $achievement): ?>
                    <div class="flex items-center p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas <?php echo $achievement['icon_class']; ?> text-blue-500"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800"><?php echo ucfirst(str_replace(['-', '_'], ' ', $achievement['achievement_name'])); ?></h4>
                            <p class="text-sm text-gray-600"><?php echo $achievement['description']; ?></p>
                        </div>
                        <div class="text-xs text-blue-600 text-right">
                            <div><?php echo date('M j', strtotime($achievement['unlocked_at'])); ?></div>
                            <div><?php echo date('g:i A', strtotime($achievement['unlocked_at'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .app-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .app-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<?php require_once 'footer.php'; ?>