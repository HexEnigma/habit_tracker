<?php
require_once 'config.php';
require_login();

$habit_id = $_GET['habit_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$habit_id) {
    die('Habit ID is required');
}

// Get user account creation date
$stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();
$account_created = new DateTime($user_data['created_at']);
$account_created_date = $account_created->format('Y-m-d');

// Verify user owns this habit
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);
$habit = $stmt->fetch();

if (!$habit) {
    die('Habit not found');
}

// Get monthly data
$month = $_GET['month'] ?? date('Y-m');
$stmt = $pdo->prepare("
    SELECT progress_date, completed 
    FROM habit_progress 
    WHERE habit_id = ? AND progress_date LIKE ? 
    ORDER BY progress_date
");
$stmt->execute([$habit_id, "$month%"]);
$progress_data = $stmt->fetchAll();

// Convert to associative array for easier access
$monthly_data = [];
foreach ($progress_data as $progress) {
    $day = date('j', strtotime($progress['progress_date']));
    $monthly_data[$day] = (bool)$progress['completed'];
}

// Previous and next month links
$prev_month = date('Y-m', strtotime($month . ' -1 month'));
$next_month = date('Y-m', strtotime($month . ' +1 month'));
?>

<div class="monthly-view-container">
    <!-- Month Navigation -->
    <div class="flex items-center justify-between mb-6">
        <a href="?habit_id=<?php echo $habit_id; ?>&month=<?php echo $prev_month; ?>"
            class="p-2 rounded-lg hover:bg-gray-100 transition flex items-center">
            <i class="fas fa-chevron-left mr-1"></i> Previous
        </a>

        <h3 class="text-xl font-semibold text-gray-800"><?php echo date('F Y', strtotime($month)); ?></h3>

        <a href="?habit_id=<?php echo $habit_id; ?>&month=<?php echo $next_month; ?>"
            class="p-2 rounded-lg hover:bg-gray-100 transition flex items-center">
            Next <i class="fas fa-chevron-right ml-1"></i>
        </a>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-2 mb-4 text-center">
        <div class="font-semibold text-gray-600 py-2">Sun</div>
        <div class="font-semibold text-gray-600 py-2">Mon</div>
        <div class="font-semibold text-gray-600 py-2">Tue</div>
        <div class="font-semibold text-gray-600 py-2">Wed</div>
        <div class="font-semibold text-gray-600 py-2">Thu</div>
        <div class="font-semibold text-gray-600 py-2">Fri</div>
        <div class="font-semibold text-gray-600 py-2">Sat</div>
    </div>

    <div class="grid grid-cols-7 gap-2">
        <?php
        $first_day = date('w', strtotime($month . '-01')); // 0 for Sunday, 6 for Saturday
        $days_in_month = date('t', strtotime($month . '-01'));
        $today = date('Y-m-d');

        // Empty cells for days before month starts
        for ($i = 0; $i < $first_day; $i++) {
            echo '<div class="h-12 bg-gray-100 rounded-lg flex items-center justify-center"></div>';
        }

        // Days of the month
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = $month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            $is_today = $date == $today;
            $is_future = $date > $today;
            $is_before_account = $date < $account_created_date;

            // Get status from monthly data
            $status = null;
            foreach ($progress_data as $progress) {
                if (date('j', strtotime($progress['progress_date'])) == $day) {
                    $status = (bool)$progress['completed'];
                    break;
                }
            }

            $class = 'w-4 h-4 rounded-sm border flex items-center justify-center text-xs ';

            // Check if date is before account creation
            if ($is_before_account) {
                $class .= 'bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed ';
                echo '<div class="' . $class . '" title="Before account creation (' . $account_created_date . ')">' . $day . '</div>';
                continue;
            }

            // Apply status-based styling
            if ($status === true) {
                $class .= 'bg-green-400 border-green-500 text-white ';
            } elseif ($status === false) {
                $class .= 'bg-red-400 border-red-500 text-white ';
            } elseif ($is_today) {
                $class .= 'border-blue-400 bg-blue-100 text-blue-800 ';
            } elseif ($is_future) {
                $class .= 'bg-gray-100 border-gray-300 text-gray-500 ';
            } else {
                $class .= 'bg-white border-gray-200 text-gray-800 ';
            }

            echo '<div class="' . $class . '" title="' . $date . '">' . $day . '</div>';
        }
        ?>
    </div>

    <!-- Legend -->
    <div class="mt-6 pt-4 border-t">
        <h4 class="font-semibold text-gray-700 mb-3">Legend</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-400 border-2 border-green-500 rounded mr-2"></div>
                <span>Completed</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-400 border-2 border-red-500 rounded mr-2"></div>
                <span>Missed</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-100 border-2 border-blue-400 rounded mr-2"></div>
                <span>Today</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-white border-2 border-gray-200 rounded mr-2"></div>
                <span>Not tracked</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-100 border-2 border-gray-300 rounded mr-2"></div>
                <span>Before account</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-6 pt-4 border-t">
        <h4 class="font-semibold text-gray-700 mb-3">Monthly Stats</h4>
        <div class="grid grid-cols-3 gap-4 text-center">
            <?php
            $completed = array_filter($monthly_data, fn($val) => $val === true);
            $missed = array_filter($monthly_data, fn($val) => $val === false);
            $total_days = count(array_filter($monthly_data, fn($val) => $val !== null));
            $completion_rate = $total_days > 0 ? round((count($completed) / $total_days) * 100) : 0;
            ?>
            <div>
                <div class="text-2xl font-bold text-green-600"><?php echo count($completed); ?></div>
                <div class="text-sm text-gray-600">Completed</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-red-600"><?php echo count($missed); ?></div>
                <div class="text-sm text-gray-600">Missed</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-blue-600"><?php echo $completion_rate; ?>%</div>
                <div class="text-sm text-gray-600">Success Rate</div>
            </div>
        </div>
    </div>
    <script>
        // Handle calendar navigation via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            // Get the parent window elements
            const calendarModal = window.parent.document.getElementById('calendar-modal');
            const calendarModalContent = window.parent.document.getElementById('calendar-modal-content');

            // Add click event listeners to navigation links
            document.querySelectorAll('a[href*="month="]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href, window.location.origin);
                    const habitId = url.searchParams.get('habit_id');
                    const month = url.searchParams.get('month');

                    // Show loading indicator
                    if (calendarModalContent) {
                        calendarModalContent.innerHTML = `
                    <div class="flex items-center justify-center h-40">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <span class="ml-3 text-gray-600">Loading...</span>
                    </div>
                `;

                        // Load the new month
                        fetch(`monthly-view.php?habit_id=${habitId}&month=${month}`)
                            .then(response => response.text())
                            .then(data => {
                                calendarModalContent.innerHTML = data;
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                calendarModalContent.innerHTML = `
                            <div class="text-center py-8 text-red-500">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Failed to load calendar</p>
                                <p class="text-sm text-gray-500 mt-1">Please try again later</p>
                            </div>
                        `;
                            });
                    }
                });
            });
        });
    </script>
</div>