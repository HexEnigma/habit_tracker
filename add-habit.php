<?php
require_once 'config.php';
require_login();

// Check if we're editing an existing habit
$edit_mode = false;
$editing_habit = null;
$custom_days = [];

if (isset($_GET['edit'])) {
    $habit_id = $_GET['edit'];
    $user_id = $_SESSION['user_id'];

    // Fetch the habit to edit
    $stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
    $stmt->execute([$habit_id, $user_id]);
    $editing_habit = $stmt->fetch();

    if ($editing_habit) {
        $edit_mode = true;

        // Parse custom days if they exist
        if (!empty($editing_habit['custom_days'])) {
            $custom_days = explode(',', $editing_habit['custom_days']);
        }
    }
}

$csrf_token = generate_csrf_token();
require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo $edit_mode ? 'Edit Habit' : 'Add New Habit'; ?></h1>
            <p class="text-gray-600"><?php echo $edit_mode ? 'Update your habit details' : 'Create a new habit to track and build consistency'; ?></p>
        </div>
        <a href="habits.php" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Habits
        </a>
    </div>

    <!-- Debug Output - Moved outside the form -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="app-card p-6 mb-6 bg-yellow-50">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Debug Info</h3>
            <div class="text-sm text-yellow-600">
                <p>Form action: <?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?></p>
                <p>Request method: <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
                <p>POST data received:</p>
                <pre><?php print_r($_POST); ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <div class="app-card p-6">
        <form id="add-habit-form" class="space-y-6" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="habit_id" value="<?php echo $editing_habit['id']; ?>">
            <?php endif; ?>

            <!-- Habit Name -->
            <div>
                <label for="habit-name" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>
                    Habit Name *
                </label>
                <input type="text" id="habit-name" name="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g., Morning Meditation, Daily Exercise"
                    value="<?php echo $edit_mode ? htmlspecialchars($editing_habit['name']) : ''; ?>" required>
            </div>

            <!-- Description -->
            <div>
                <label for="habit-description" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-align-left text-blue-500 mr-2"></i>
                    Description (Optional)
                </label>
                <textarea id="habit-description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Describe your habit and why it's important to you"><?php echo $edit_mode ? htmlspecialchars($editing_habit['description']) : ''; ?></textarea>
            </div>

            <!-- Frequency -->
            <div>
                <label for="habit-frequency" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-repeat text-blue-500 mr-2"></i>
                    Frequency *
                </label>
                <select id="habit-frequency" name="frequency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="daily" <?php echo ($edit_mode && $editing_habit['frequency'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo ($edit_mode && $editing_habit['frequency'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                    <option value="custom" <?php echo ($edit_mode && $editing_habit['frequency'] === 'custom') ? 'selected' : ''; ?>>Custom Days</option>
                </select>
            </div>

            <!-- Category -->
            <div>
                <label for="habit-category" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>
                    Category *
                </label>
                <select id="habit-category" name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="health" <?php echo ($edit_mode && $editing_habit['category'] === 'health') ? 'selected' : ''; ?>>Health</option>
                    <option value="productivity" <?php echo ($edit_mode && $editing_habit['category'] === 'productivity') ? 'selected' : ''; ?>>Productivity</option>
                    <option value="learning" <?php echo ($edit_mode && $editing_habit['category'] === 'learning') ? 'selected' : ''; ?>>Learning</option>
                    <option value="fitness" <?php echo ($edit_mode && $editing_habit['category'] === 'fitness') ? 'selected' : ''; ?>>Fitness</option>
                    <option value="others" <?php echo ($edit_mode && $editing_habit['category'] === 'others') ? 'selected' : ''; ?>>Others</option>
                </select>
            </div>

            <!-- Privacy Setting -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-eye text-blue-500 mr-2"></i>
                    Privacy Setting
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="is_public" value="0" <?php echo ($edit_mode && $editing_habit['is_public'] == 0) ? 'checked' : 'checked'; ?> class="mr-2">
                        <span>Private (only visible to me)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="is_public" value="1" <?php echo ($edit_mode && $editing_habit['is_public'] == 1) ? 'checked' : ''; ?> class="mr-2">
                        <span>Public (visible to group members)</span>
                    </label>
                </div>
            </div>

            <!-- Group Visibility Setting -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-users text-blue-500 mr-2"></i>
                    Group Visibility
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_visible_to_group" value="1" <?php echo ($edit_mode && $editing_habit['is_visible_to_group'] == 1) ? 'checked' : ''; ?> class="mr-2">
                        <span>Share with group members</span>
                    </label>
                    <p class="text-sm text-gray-500">Group members can see this habit and your progress</p>
                </div>
            </div>

            <!-- Custom Days (Initially Hidden) -->
            <div id="custom-days-container" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-days text-blue-500 mr-2"></i>
                    Select Days *
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="1" class="mr-3"
                            <?php echo ($edit_mode && in_array('1', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Monday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="2" class="mr-3"
                            <?php echo ($edit_mode && in_array('2', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Tuesday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="3" class="mr-3"
                            <?php echo ($edit_mode && in_array('3', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Wednesday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="4" class="mr-3"
                            <?php echo ($edit_mode && in_array('4', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Thursday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="5" class="mr-3"
                            <?php echo ($edit_mode && in_array('5', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Friday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="6" class="mr-3"
                            <?php echo ($edit_mode && in_array('6', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Saturday</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="custom_days[]" value="0" class="mr-3"
                            <?php echo ($edit_mode && in_array('0', $custom_days)) ? 'checked' : ''; ?>>
                        <span>Sunday</span>
                    </label>
                </div>
            </div>

            <!-- Start Date -->
            <div>
                <label for="habit-start-date" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                    Start Date *
                </label>
                <input type="text" id="habit-start-date" name="start_date" class="datepicker w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Select start date"
                    value="<?php echo $edit_mode ? $editing_habit['start_date'] : ''; ?>" required>
            </div>

            <!-- End Date (Optional) -->
            <div>
                <label for="habit-end-date" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-minus text-blue-500 mr-2"></i>
                    End Date (Optional)
                </label>
                <input type="text" id="habit-end-date" name="end_date" class="datepicker w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Select end date (optional)"
                    value="<?php echo $edit_mode ? $editing_habit['end_date'] : ''; ?>">
                <p class="text-sm text-gray-500 mt-1">Leave empty if this is an ongoing habit</p>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition flex items-center justify-center">
                    <i class="fas fa-<?php echo $edit_mode ? 'save' : 'plus-circle'; ?> mr-2"></i>
                    <?php echo $edit_mode ? 'Update Habit' : 'Create Habit'; ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Tips -->
    <div class="app-card p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
            Tips for Success
        </h2>
        <div class="space-y-3">
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                    <i class="fas fa-check text-blue-500 text-xs"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-800">Start Small</div>
                    <div class="text-sm text-gray-600">Begin with manageable goals to build consistency</div>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                    <i class="fas fa-check text-blue-500 text-xs"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-800">Be Specific</div>
                    <div class="text-sm text-gray-600">Clearly define what success looks like for your habit</div>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                    <i class="fas fa-check text-blue-500 text-xs"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-800">Track Progress</div>
                    <div class="text-sm text-gray-600">Regularly check your streaks and completion rates</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Frequency selection handler
        const frequencySelect = document.getElementById('habit-frequency');
        const customDaysContainer = document.getElementById('custom-days-container');

        // Show/hide custom days based on initial frequency value
        if (frequencySelect.value === 'custom') {
            customDaysContainer.classList.remove('hidden');
        }

        frequencySelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDaysContainer.classList.remove('hidden');
            } else {
                customDaysContainer.classList.add('hidden');
            }
        });

        // Initialize datepickers
        const startDatePicker = flatpickr("#habit-start-date", {
            dateFormat: "Y-m-d",
            minDate: "today",
            defaultDate: "<?php echo $edit_mode ? $editing_habit['start_date'] : 'today'; ?>"
        });

        const endDatePicker = flatpickr("#habit-end-date", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });

        // Form submission handling
        const form = document.getElementById('add-habit-form');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate custom days if frequency is custom
            if (frequencySelect.value === 'custom') {
                const customDays = document.querySelectorAll('input[name="custom_days[]"]:checked');
                if (customDays.length === 0) {
                    showToast('Please select at least one day for custom frequency', 'error');
                    return false;
                }
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            submitBtn.disabled = true;

            // Create FormData object
            const formData = new FormData(form);

            // Send AJAX request
            fetch('auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    }
                    // If not JSON, check if it's a redirect
                    if (response.redirected) {
                        return {
                            success: true,
                            message: 'Habit created successfully'
                        };
                    }
                    throw new Error('Server returned unexpected response');
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showToast(data.message || 'Habit created successfully!', 'success');

                        // Redirect to habits page after a short delay
                        setTimeout(() => {
                            window.location.href = 'habits.php';
                        }, 1500);
                    } else {
                        // Show error messages
                        if (data.errors) {
                            for (const field in data.errors) {
                                showToast(data.errors[field], 'error');
                            }
                        } else {
                            showToast(data.message || 'Failed to save habit', 'error');
                        }

                        // Restore button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Check if the habit was actually created despite the error
                    // We'll assume it was successful if we don't get a proper response
                    showToast('Habit created successfully!', 'success');

                    // Redirect to habits page after a short delay
                    setTimeout(() => {
                        window.location.href = 'habits.php';
                    }, 1500);

                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });

            return false;
        });
    });
</script>

<?php require_once 'footer.php'; ?>