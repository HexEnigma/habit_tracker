<?php
require_once 'config.php';
require_login();

// Handle POST actions (mark complete, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $habit_id = $_POST['habit_id'] ?? 0;
    $user_id = $_SESSION['user_id'];

    header('Content-Type: application/json');

    try {
        switch ($action) {
            case 'complete':
                // Check if habit belongs to user
                $stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
                $stmt->execute([$habit_id, $user_id]);
                $habit = $stmt->fetch();

                if (!$habit) {
                    echo json_encode(['success' => false, 'message' => 'Habit not found']);
                    exit;
                }

                $today = date('Y-m-d');

                // Check if already completed today
                $stmt = $pdo->prepare("SELECT * FROM habit_progress WHERE habit_id = ? AND progress_date = ?");
                $stmt->execute([$habit_id, $today]);

                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Habit already completed today']);
                    exit;
                }

                // Record completion
                $stmt = $pdo->prepare("INSERT INTO habit_progress (habit_id, progress_date, completed) VALUES (?, ?, 1)");
                $stmt->execute([$habit_id, $today]);

                // Update user points
                $stmt = $pdo->prepare("UPDATE user_points SET points = points + 1 WHERE user_id = ?");
                $stmt->execute([$user_id]);

                // Get updated points
                $stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $points = $stmt->fetch()['points'];

                echo json_encode([
                    'success' => true,
                    'message' => 'Habit marked as complete!',
                    'points' => $points
                ]);
                exit;

            case 'delete':
                // Check if habit belongs to user
                $stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
                $stmt->execute([$habit_id, $user_id]);

                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Habit not found']);
                    exit;
                }

                // Delete habit progress first
                $stmt = $pdo->prepare("DELETE FROM habit_progress WHERE habit_id = ?");
                $stmt->execute([$habit_id]);

                // Delete habit
                $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
                $stmt->execute([$habit_id, $user_id]);

                echo json_encode(['success' => true, 'message' => 'Habit deleted successfully']);
                exit;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}

// If it's a GET request, show the habits page
$user_id = $_SESSION['user_id'];
// Check for missed habits (runs once per day)
check_missed_habits($pdo, $user_id);
$username = $_SESSION['username'];
$csrf_token = generate_csrf_token();

// Get user habits
$stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$habits = $stmt->fetchAll();

// Get user points
$stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
$stmt->execute([$user_id]);
$points = $stmt->fetch()['points'];

require_once 'header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Habits</h1>
            <p class="text-gray-600">Manage and track your daily habits</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-200 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <div class="bg-blue-50 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-star text-yellow-400 mr-2"></i>
                <span class="font-semibold"><?php echo $points; ?></span>
                <span class="text-sm text-gray-600 ml-1">points</span>
            </div>
            <a href="add-habit.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-600 transition flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Add New Habit
            </a>
        </div>
    </div>

    <!-- Habits List -->
    <div class="grid grid-cols-1 gap-6">
        <?php if (empty($habits)): ?>
            <div class="app-card p-8 text-center">
                <i class="fas fa-list-check text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No habits yet</h3>
                <p class="text-gray-500 mb-4">Start by adding your first habit to track</p>
                <a href="add-habit.php" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600 transition inline-block">
                    Create Your First Habit
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($habits as $habit):
                $today = date('Y-m-d');
                // Debug each habit check
                error_log("Checking habit {$habit['id']} - {$habit['name']} for date: $today");

                $stmt = $pdo->prepare("SELECT completed, progress_date FROM habit_progress WHERE habit_id = ? AND progress_date = ?");
                $stmt->execute([$habit['id'], $today]);
                $progress = $stmt->fetch();

                if ($progress) {
                    error_log("Habit {$habit['id']} - Found progress: " . print_r($progress, true));
                    $is_completed = (bool)$progress['completed'];
                } else {
                    error_log("Habit {$habit['id']} - No progress found for today");
                    $is_completed = false;
                }

                error_log("Habit {$habit['id']} - Completed today: " . ($is_completed ? 'Yes' : 'No'));

                $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE habit_id = ?");
                $stmt->execute([$habit['id']]);
                $streak_data = $stmt->fetch();
                $streak = $streak_data ? $streak_data['current_streak'] : 0;

                // Get progress data for the current month
                $current_month = date('Y-m');
                $stmt = $pdo->prepare("
                    SELECT progress_date, completed 
                    FROM habit_progress 
                    WHERE habit_id = ? AND progress_date LIKE ? 
                    ORDER BY progress_date
                ");
                $stmt->execute([$habit['id'], "$current_month%"]);
                $progress_data = $stmt->fetchAll();

                // Convert to associative array for easier access
                $monthly_data = [];
                foreach ($progress_data as $progress) {
                    $day = date('j', strtotime($progress['progress_date']));
                    $monthly_data[$day] = $progress['completed'];
                }
            ?>
                <div class="app-card p-6 habit-<?php echo $is_completed ? 'completed' : 'pending'; ?>">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center <?php echo $is_completed ? 'bg-green-100' : 'bg-blue-100'; ?>">
                                        <i class="fas <?php echo $is_completed ? 'fa-check text-green-500' : 'fa-ellipsis-h text-blue-500'; ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 text-lg"><?php echo htmlspecialchars($habit['name']); ?></h3>
                                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                            <?php echo ucfirst($habit['category']); ?>
                                        </span>
                                        <?php if (!empty($habit['description'])): ?>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($habit['description']); ?></p>
                                        <?php endif; ?>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-xs font-medium px-2 py-1 rounded-full <?php echo $is_completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $is_completed ? 'Completed today' : 'Pending today'; ?>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-fire mr-1"></i> <?php echo $streak; ?> day streak
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-repeat mr-1"></i> <?php echo ucfirst($habit['frequency']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-2">
                                    <!-- Calendar Button -->
                                    <button class="calendar-btn bg-gray-100 text-gray-600 p-2 rounded-lg hover:bg-gray-200 transition"
                                        data-habit-id="<?php echo $habit['id']; ?>"
                                        data-habit-name="<?php echo htmlspecialchars($habit['name']); ?>"
                                        title="View calendar">
                                        <i class="fas fa-calendar"></i>
                                    </button>

                                    <?php if (!$is_completed): ?>
                                        <!-- Complete Button -->
                                        <button class="complete-habit-btn bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 transition"
                                            data-habit-id="<?php echo $habit['id']; ?>"
                                            title="Mark as complete">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Edit Button -->
                                    <a href="add-habit.php?edit=<?php echo $habit['id']; ?>" class="bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600 transition"
                                        title="Edit habit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete Button -->
                                    <button class="delete-habit-btn bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition"
                                        data-habit-id="<?php echo $habit['id']; ?>"
                                        data-habit-name="<?php echo htmlspecialchars($habit['name']); ?>"
                                        title="Delete habit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Monthly Progress Preview -->
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2"><?php echo date('F Y'); ?> Progress</h4>
                                <div class="flex flex-wrap gap-1">
                                    <?php
                                    $first_day_of_week = date('N', strtotime($current_month . '-01')); // 1 (Monday) to 7 (Sunday)
                                    $days_in_month = date('t', strtotime($current_month . '-01'));
                                    $today_date = date('Y-m-d');

                                    // Get user account creation date
                                    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
                                    $stmt->execute([$user_id]);
                                    $user_data = $stmt->fetch();
                                    $account_created = new DateTime($user_data['created_at']);
                                    $account_created_date = $account_created->format('Y-m-d');

                                    // Empty cells for days before the first day of the month
                                    for ($i = 1; $i < $first_day_of_week; $i++) {
                                        echo '<div class="w-4 h-4 bg-gray-100 rounded-sm"></div>';
                                    }

                                    // Days of the month
                                    for ($day = 1; $day <= $days_in_month; $day++) {
                                        $date = $current_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                        $is_today = $date == $today_date;
                                        $is_future = $date > $today_date;
                                        $is_before_account = $date < $account_created_date;

                                        // Get status from monthly data
                                        $status = null;
                                        foreach ($progress_data as $progress) {
                                            if (date('j', strtotime($progress['progress_date'])) == $day) {
                                                $status = (bool)$progress['completed'];
                                                break;
                                            }
                                        }

                                        $class = 'w-4 h-4 rounded-sm border ';

                                        // Check if date is before account creation
                                        if ($is_before_account) {
                                            $class .= 'bg-gray-100 border-gray-300';
                                            echo '<div class="' . $class . '" title="Before account creation"></div>';
                                            continue;
                                        }

                                        // Apply status-based styling
                                        if ($status === true) {
                                            $class .= 'bg-green-400 border-green-500';
                                        } elseif ($status === false) {
                                            $class .= 'bg-red-400 border-red-500';
                                        } elseif ($is_today) {
                                            $class .= 'border-blue-400 bg-blue-100';
                                        } elseif ($is_future) {
                                            $class .= 'bg-gray-100 border-gray-300';
                                        } else {
                                            $class .= 'bg-white border-gray-200';
                                        }

                                        echo '<div class="' . $class . '" title="' . $date . '"></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendar-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="calendar-modal-title" class="text-xl font-semibold text-gray-800">Monthly Progress</h3>
                <button id="close-calendar-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="calendar-modal-content"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FIXED: Use event delegation for habit completion - ONLY THIS VERSION SHOULD EXIST
        document.addEventListener('click', function(e) {
            // Check if the clicked element is a complete habit button
            const button = e.target.closest('.complete-habit-btn');
            if (!button) return;

            // Prevent default and stop propagation
            e.preventDefault();
            e.stopPropagation();

            // Ensure we don't process already-in-progress requests
            if (button.disabled || button.classList.contains('processing')) {
                e.stopImmediatePropagation();
                return;
            }

            // Mark as processing
            button.classList.add('processing');

            const habitId = button.dataset.habitId;
            const habitCard = button.closest('.app-card');

            // Immediately disable button to prevent multiple clicks
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;

            const formData = new FormData();
            formData.append('action', 'complete');
            formData.append('habit_id', habitId);
            formData.append('csrf_token', getCSRFToken());

            fetch('habits.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Habit marked as complete! 🎉', 'success');

                        // Remove the button completely
                        button.remove();

                        // Update status badge
                        const statusBadge = habitCard.querySelector('.px-2');
                        if (statusBadge) {
                            statusBadge.className = 'text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800';
                            statusBadge.textContent = 'Completed today';
                        }

                        // Update icon
                        const iconContainer = habitCard.querySelector('.w-12.h-12');
                        if (iconContainer) {
                            iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-green-100';
                            iconContainer.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                        }

                        // Update points if available
                        if (data.points) {
                            document.querySelectorAll('.fa-star').forEach(icon => {
                                const pointsElement = icon.closest('.bg-blue-50').querySelector('.font-semibold');
                                if (pointsElement) {
                                    pointsElement.textContent = data.points;
                                }
                            });
                        }
                    } else {
                        // Show error but re-enable button
                        showToast(data.message, 'error');
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                        button.classList.remove('processing');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                    // Re-enable button on error
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    button.classList.remove('processing');
                });
        });

        // Handle habit deletion
        document.querySelectorAll('.delete-habit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const habitId = this.dataset.habitId;
                const habitName = this.dataset.habitName;
                const habitCard = this.closest('.app-card');

                if (confirm(`Are you sure you want to delete "${habitName}"? This action cannot be undone.`)) {
                    showLoading();

                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('habit_id', habitId);

                    fetch('habits.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();

                            if (data.success) {
                                showToast(data.message, 'success');

                                // Remove the habit card with animation
                                habitCard.style.opacity = '0';
                                habitCard.style.transition = 'opacity 0.3s ease';

                                setTimeout(() => {
                                    habitCard.remove();

                                    // If no habits left, show empty state
                                    if (document.querySelectorAll('.app-card').length === 0) {
                                        location.reload();
                                    }
                                }, 300);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            console.error('Error:', error);
                            showToast('An error occurred', 'error');
                        });
                }
            });
        });

        // Calendar modal functionality
        const calendarModal = document.getElementById('calendar-modal');
        const calendarModalTitle = document.getElementById('calendar-modal-title');
        const calendarModalContent = document.getElementById('calendar-modal-content');
        const closeCalendarModal = document.getElementById('close-calendar-modal');

        // Open calendar modal
        document.querySelectorAll('.calendar-btn').forEach(button => {
            button.addEventListener('click', function() {
                const habitId = this.dataset.habitId;
                const habitName = this.dataset.habitName;

                showLoading();
                calendarModalTitle.textContent = `Monthly Progress - ${habitName}`;
                calendarModalContent.innerHTML = `
                <div class="flex items-center justify-center h-40">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-3 text-gray-600">Loading calendar...</span>
                </div>
            `;

                calendarModal.classList.remove('hidden');

                // Load calendar content
                fetch(`monthly-view.php?habit_id=${habitId}`)
                    .then(response => response.text())
                    .then(data => {
                        hideLoading();
                        calendarModalContent.innerHTML = data;
                        // Attach AJAX navigation for next/prev month links
                        attachCalendarNavHandlers(habitId);
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error loading calendar:', error);
                        calendarModalContent.innerHTML = `
                        <div class="text-center py-8 text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Failed to load calendar</p>
                            <p class="text-sm text-gray-500 mt-1">Please try again later</p>
                        </div>
                    `;
                    });
            });
        });

        // Attach AJAX navigation for next/prev month links inside the modal
        function attachCalendarNavHandlers(habitId) {
            // Find all next/prev month links inside the modal content
            const navLinks = calendarModalContent.querySelectorAll('a[href*="?habit_id="]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Parse month from href
                    const url = new URL(link.href, window.location.origin);
                    const month = url.searchParams.get('month');
                    // Show loading spinner
                    calendarModalContent.innerHTML = `
                        <div class="flex items-center justify-center h-40">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <span class="ml-3 text-gray-600">Loading calendar...</span>
                        </div>
                    `;
                    // Build AJAX URL
                    let ajaxUrl = `monthly-view.php?habit_id=${habitId}`;
                    if (month) {
                        ajaxUrl += `&month=${month}`;
                    }
                    fetch(ajaxUrl)
                        .then(response => response.text())
                        .then(data => {
                            calendarModalContent.innerHTML = data;
                            // Re-attach handlers for new content
                            attachCalendarNavHandlers(habitId);
                        })
                        .catch(error => {
                            console.error('Error loading calendar:', error);
                            calendarModalContent.innerHTML = `
                                <div class="text-center py-8 text-red-500">
                                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                    <p>Failed to load calendar</p>
                                    <p class="text-sm text-gray-500 mt-1">Please try again later</p>
                                </div>
                            `;
                        });
                });
            });
        }

        // Close calendar modal
        closeCalendarModal.addEventListener('click', function() {
            calendarModal.classList.add('hidden');
        });

        calendarModal.addEventListener('click', function(e) {
            if (e.target === calendarModal) {
                calendarModal.classList.add('hidden');
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !calendarModal.classList.contains('hidden')) {
                calendarModal.classList.add('hidden');
            }
        });
    });
</script>

<?php require_once 'footer.php'; ?>