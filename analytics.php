<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get basic stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_habits FROM habits WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_habits = $stmt->fetch()['total_habits'];

$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT COUNT(*) as completed_count 
    FROM habit_progress hp 
    JOIN habits h ON hp.habit_id = h.id 
    WHERE h.user_id = ? AND hp.completed = 1 AND hp.progress_date = ?
");
$stmt->execute([$user_id, $today]);
$today_completed = $stmt->fetch()['completed_count'];

// Calculate success rate (last 30 days)
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN hp.completed = 1 THEN 1 ELSE 0 END) as completed_days
    FROM habits h
    LEFT JOIN habit_progress hp ON h.id = hp.habit_id AND hp.progress_date BETWEEN ? AND ?
    WHERE h.user_id = ?
");
$stmt->execute([$thirty_days_ago, $today, $user_id]);
$progress_data = $stmt->fetch();

$total_days = $progress_data['total_days'];
$completed_days = $progress_data['completed_days'];
$success_rate = $total_days > 0 ? round(($completed_days / $total_days) * 100) : 0;

// Get weekly progress data (last 7 days)
$weekly_data = [];
$week_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_count 
        FROM habit_progress hp 
        JOIN habits h ON hp.habit_id = h.id 
        WHERE h.user_id = ? AND hp.completed = 1 AND hp.progress_date = ?
    ");
    $stmt->execute([$user_id, $date]);
    $completed = $stmt->fetch()['completed_count'];

    $weekly_data[] = $completed;
}

// Get habit distribution by category
$stmt = $pdo->prepare("
    SELECT category, COUNT(*) as count 
    FROM habits 
    WHERE user_id = ? 
    GROUP BY category
");
$stmt->execute([$user_id]);
$category_data = $stmt->fetchAll();

// Prepare data for charts
$category_counts = [
    'health' => 0,
    'productivity' => 0,
    'learning' => 0,
    'fitness' => 0,
    'others' => 0
];

$category_labels = [];
$category_values = [];
$category_colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#6b7280'];

foreach ($category_data as $category) {
    $category_counts[$category['category']] = $category['count'];
}

foreach ($category_counts as $category => $count) {
    $category_labels[] = ucfirst($category);
    $category_values[] = $count;
}

require_once 'header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Analytics</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-3xl font-bold text-blue-500 mb-2"><?php echo $total_habits; ?></div>
            <div class="text-gray-600">Total Habits</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-3xl font-bold text-green-500 mb-2"><?php echo $today_completed; ?></div>
            <div class="text-gray-600">Completed Today</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-3xl font-bold text-purple-500 mb-2"><?php echo $success_rate; ?>%</div>
            <div class="text-gray-600">Success Rate (30 days)</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Weekly Progress</h2>
            <div class="h-64"> <!-- Fixed height container -->
                <canvas id="weeklyChart" height="256"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Habit Distribution</h2>
            <div class="h-64"> <!-- Fixed height container -->
                <canvas id="habitChart" height="256"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Weekly progress chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Completed Habits',
                data: <?php echo json_encode($weekly_data); ?>,
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Habit distribution chart
    const habitCtx = document.getElementById('habitChart').getContext('2d');
    new Chart(habitCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($category_values); ?>,
                backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#6b7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });
</script>

<?php require_once 'footer.php'; ?>