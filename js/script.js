// App State
const state = {
    currentUser: null,
    currentView: 'welcome',
    currentMonth: new Date(), // Initialize with current date
    habits: {},
    editingHabitId: null,
    confirmationCallback: null
};

// Global user data (this should be set by your PHP)
let userData = window.userData || { accountCreated: new Date().toISOString() };

// Simple modal handling
document.addEventListener('DOMContentLoaded', function() {
    
    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // Handle settings button
    const settingsBtn = document.getElementById('settings-btn');
    if (settingsBtn) {
        settingsBtn.addEventListener('click', handleSettings);
    }
    
    // Confirmation Modal handlers
    const confirmActionBtn = document.getElementById('confirm-action');
    const cancelConfirmationBtn = document.getElementById('cancel-confirmation');
    const confirmationModal = document.getElementById('confirmation-modal');
    
    // Calendar Navigation
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', navigateToPreviousMonth);
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', navigateToNextMonth);
    }

    // Initialize calendar
    renderCalendar();

    if (confirmActionBtn) {
        confirmActionBtn.addEventListener('click', function() {
            confirmationModal.classList.add('hidden');
        });
    }

    if (cancelConfirmationBtn) {
        cancelConfirmationBtn.addEventListener('click', function() {
            confirmationModal.classList.add('hidden');
        });
    }

    // Close modal when clicking outside
    if (confirmationModal) {
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                confirmationModal.classList.add('hidden');
            }
        });
    }

    // Handle habit button clicks using event delegation
    document.addEventListener('click', function(e) {
        // Complete habit button
        if (e.target.closest('.complete-habit-btn')) {
            const habitId = e.target.closest('.complete-habit-btn').dataset.habitId;
            markHabitComplete(habitId);
        }
        
        // Edit habit button
        if (e.target.closest('.edit-habit-btn')) {
            const habitId = e.target.closest('.edit-habit-btn').dataset.habitId;
            showEditHabitModal(habitId);
        }
        
        // Delete habit button
        if (e.target.closest('.delete-habit-btn')) {
            const habitId = e.target.closest('.delete-habit-btn').dataset.habitId;
            const habitCard = e.target.closest('.app-card');
            const habitName = habitCard.querySelector('h3').textContent;
            showConfirmationModal('Delete Habit', `Are you sure you want to delete "${habitName}"?`, () => {
                deleteHabit(habitId);
            });
        }
    });

    // Handle habit form submission
    const addHabitForm = document.getElementById('add-habit-form');
    if (addHabitForm) {
        addHabitForm.addEventListener('submit', handleAddHabit);
    }

    // Handle reminder form submission
    const reminderForm = document.getElementById('reminder-form');
    if (reminderForm) {
        reminderForm.addEventListener('submit', handleReminderSettings);
    }

    // Initialize date pickers
    const datepickers = document.querySelectorAll('.datepicker');
    if (datepickers.length > 0 && typeof flatpickr !== 'undefined') {
        datepickers.forEach(input => {
            flatpickr(input, {
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });
        });
    }

    // Handle frequency selection change
    const frequencySelect = document.getElementById('habit-frequency');
    if (frequencySelect) {
        frequencySelect.addEventListener('change', function() {
            const customDaysContainer = document.getElementById('custom-days-container');
            if (this.value === 'custom') {
                customDaysContainer.classList.remove('hidden');
            } else {
                customDaysContainer.classList.add('hidden');
            }
        });
    }

    // Handle edit frequency selection change
    const editFrequencySelect = document.getElementById('edit-habit-frequency');
    if (editFrequencySelect) {
        editFrequencySelect.addEventListener('change', function() {
            const editCustomDaysContainer = document.getElementById('edit-custom-days-container');
            if (this.value === 'custom') {
                editCustomDaysContainer.classList.remove('hidden');
            } else {
                editCustomDaysContainer.classList.add('hidden');
            }
        });
    }

    // Initialize all functionality
    initMobileSidebar();
    initHabitFrequencyHandler();
    initHabitFormValidation();
    initHabitsPageEnhancements();
    initHabitsPageFunctionality();
    initHabitCompletionAnimations();
    initProgressTracking();
    initAccessibility();
    initPerformanceOptimizations();
    initErrorHandling();
    initOfflineFunctionality();
    initAnalytics();
    initThemeAndPreferences();
    initDataExportImport();
    finalInitialization();
});

// Calendar navigation functions
function navigateToPreviousMonth() {
    state.currentMonth = new Date(state.currentMonth.getFullYear(), state.currentMonth.getMonth() - 1, 1);
    renderCalendar();
}

function navigateToNextMonth() {
    state.currentMonth = new Date(state.currentMonth.getFullYear(), state.currentMonth.getMonth() + 1, 1);
    renderCalendar();
}

// Form handling functions
function handleSignup(e) {
    e.preventDefault();
    
    // Clear previous errors
    document.querySelectorAll('#signup-form .text-red-500').forEach(el => {
        el.classList.add('hidden');
    });
    
    const formData = new FormData(e.target);
    showLoading();
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show errors
            for (const field in data.errors) {
                const errorElement = document.getElementById(`signup-${field}-error`);
                if (errorElement) {
                    errorElement.textContent = data.errors[field];
                    errorElement.classList.remove('hidden');
                }
            }
            showToast('Please fix the errors', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function handleLogin(e) {
    e.preventDefault();
    
    // Clear previous errors
    document.querySelectorAll('#login-form .text-red-500').forEach(el => {
        el.classList.add('hidden');
    });
    
    const formData = new FormData(e.target);
    showLoading();
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show errors
            for (const field in data.errors) {
                const errorElement = document.getElementById(`login-${field}-error`);
                if (errorElement) {
                    errorElement.textContent = data.errors[field];
                    errorElement.classList.remove('hidden');
                }
            }
            showToast('Invalid credentials', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function handleLogout() {
    showConfirmationModal('Confirm Logout', 'Are you sure you want to log out?', () => {
        showLoading();
        
        const formData = new FormData();
        formData.append('action', 'logout');
        
        fetch('auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Logged out successfully');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                showToast('Logout failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred during logout', 'error');
        })
        .finally(() => {
            hideLoading();
        });
    });
}

// Loading functions
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.classList.remove('hidden');
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.classList.add('hidden');
}

// Toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `p-4 mb-3 rounded-lg flex items-center justify-between ${
        type === 'success' ? 'bg-green-100 text-green-800' : 
        type === 'error' ? 'bg-red-100 text-red-800' : 
        'bg-blue-100 text-blue-800'
    }`;
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        </div>
        <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        if (toastContainer.contains(toast)) {
            toastContainer.removeChild(toast);
        }
    }, 3000);
}

function handleSettings() {
    // For now, just show a message since we need to implement the full settings functionality
    showToast('Settings functionality will be implemented soon', 'info');
    
    // In a complete implementation, this would:
    // 1. Show a settings modal
    // 2. Allow changing notification preferences, theme, etc.
    // 3. Save settings to the database
}

function handleAddHabit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    showLoading();
    
    fetch('habits.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            e.target.reset();
            // Reload the page to show the new habit
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show errors
            for (const field in data.errors) {
                showToast(data.errors[field], 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function handleReminderSettings(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    showLoading();
    
    fetch('settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
        } else {
            showToast('Failed to save settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function markHabitComplete(habitId) {
    showLoading();
    
    const formData = new FormData();
    formData.append('action', 'complete');
    formData.append('habit_id', habitId);
    
    fetch('habits.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            // Update points display
            if (data.points && document.getElementById('total-points')) {
                document.getElementById('total-points').textContent = data.points;
            }
            // Reload to update the UI
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function deleteHabit(habitId) {
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
        if (data.success) {
            showToast(data.message);
            // Reload to update the UI
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function showEditHabitModal(habitId) {
    // For now, just show a message since we need to implement the full edit functionality
    showToast('Edit functionality will be implemented soon', 'info');
    // In a complete implementation, this would:
    // 1. Fetch habit data from server
    // 2. Populate the edit form
    // 3. Show the edit modal
}

function showConfirmationModal(title, message, callback) {
    const modal = document.getElementById('confirmation-modal');
    const titleEl = document.getElementById('confirmation-title');
    const messageEl = document.getElementById('confirmation-message');
    
    if (!modal || !titleEl || !messageEl) return;
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    // Remove any existing event listeners
    const confirmButton = document.getElementById('confirm-action');
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    
    // Add new event listener
    newConfirmButton.addEventListener('click', function() {
        modal.classList.add('hidden');
        if (callback) callback();
    });
    
    // Show the modal
    modal.classList.remove('hidden');
}

function renderCalendar() {
    const calendarContainer = document.getElementById('calendar-days');
    if (!calendarContainer) return;
    
    const currentMonth = state.currentMonth;
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Get user account creation date
    const accountCreated = new Date(userData.accountCreated);
    accountCreated.setHours(0, 0, 0, 0);
    
    // Update month label
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    if (document.getElementById('calendar-month')) {
        document.getElementById('calendar-month').textContent = `${monthNames[month]} ${year}`;
    }
    
    // Clear calendar
    calendarContainer.innerHTML = '';
    
    // Calculate first day of the month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Add empty cells for days before first day of month
    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-empty';
        calendarContainer.appendChild(emptyCell);
    }
    
    // Add days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        date.setHours(0, 0, 0, 0);
        const dateStr = date.toISOString().split('T')[0];
        
        const dayCell = document.createElement('div');
        dayCell.className = 'calendar-day';
        dayCell.textContent = day;
        
        // Check if date is before account creation
        if (date < accountCreated) {
            dayCell.classList.add('calendar-day-before-account');
        } 
        // Check if date is in the future
        else if (date > today) {
            dayCell.classList.add('calendar-day-future');
        }
        // Check if it's today
        else if (date.getTime() === today.getTime()) {
            dayCell.classList.add('calendar-day-today');
        }
        // For past dates, check if any habits were completed
        else {
            // This would normally come from your database
            // For demo purposes, we'll use random data
            const randomStatus = Math.random();
            if (randomStatus > 0.6) {
                dayCell.classList.add('calendar-day-completed');
            } else if (randomStatus < 0.4) {
                dayCell.classList.add('calendar-day-missed');
            }
        }
        
        calendarContainer.appendChild(dayCell);
    }
}

// ==================== MOBILE SIDEBAR FUNCTIONALITY ====================

function initMobileSidebar() {
    const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileSidebarContent = document.getElementById('mobile-sidebar-content');
    const mobileSidebarBackdrop = document.getElementById('mobile-sidebar-backdrop');
    const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

    // If mobile sidebar doesn't exist on this page, exit early
    if (!mobileSidebar) return;

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

    // Event listeners
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', openSidebar);
    }

    if (closeMobileSidebar) {
        closeMobileSidebar.addEventListener('click', closeSidebar);
    }

    if (mobileSidebarBackdrop) {
        mobileSidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking on navigation links
    const sidebarLinks = mobileSidebarContent?.querySelectorAll('a');
    if (sidebarLinks) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    }
}

// ==================== FREQUENCY SELECTION HANDLER ====================

function initHabitFrequencyHandler() {
    const frequencySelect = document.getElementById('habit-frequency');
    const customDaysContainer = document.getElementById('custom-days-container');

    if (!frequencySelect || !customDaysContainer) return;

    frequencySelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDaysContainer.classList.remove('hidden');
        } else {
            customDaysContainer.classList.add('hidden');
        }
    });
}

// ==================== FORM VALIDATION ====================

function initHabitFormValidation() {
    const form = document.getElementById('add-habit-form');
    const frequencySelect = document.getElementById('habit-frequency');

    if (!form || !frequencySelect) return;

    form.addEventListener('submit', function(e) {
        // Validate custom days if frequency is custom
        if (frequencySelect.value === 'custom') {
            const customDays = document.querySelectorAll('input[name="custom_days[]"]:checked');
            if (customDays.length === 0) {
                e.preventDefault();
                showToast('Please select at least one day for custom frequency', 'error');
                
                // Scroll to custom days section
                const customDaysContainer = document.getElementById('custom-days-container');
                if (customDaysContainer) {
                    customDaysContainer.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                
                return false;
            }
        }
        return true;
    });
}

// ==================== HABITS PAGE ENHANCEMENTS ====================
// Description: Monthly preview hover effects and modal functionality
// Usage: Works on habits.php page
// =================================================================

function initHabitsPageEnhancements() {
    const monthlyViewButtons = document.querySelectorAll('.monthly-view-btn');
    const monthlyPreviews = document.querySelectorAll('.monthly-preview');
    const monthlyModal = document.getElementById('monthly-view-modal');
    const closeMonthlyModal = document.getElementById('close-monthly-modal');
    const monthlyModalTitle = document.getElementById('monthly-modal-title');
    const monthlyModalContent = document.getElementById('monthly-modal-content');

    // Enhanced hover effects for monthly preview
    monthlyPreviews.forEach(preview => {
        preview.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        preview.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Click to open monthly view
        preview.addEventListener('click', function() {
            const habitId = this.dataset.habitId;
            const habitName = this.dataset.habitName;
            openMonthlyView(habitId, habitName);
        });
    });

    // Monthly view button click
    monthlyViewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const habitId = this.dataset.habitId;
            const habitName = this.dataset.habitName;
            openMonthlyView(habitId, habitName);
        });
    });

    // Open monthly view modal
    function openMonthlyView(habitId, habitName) {
        monthlyModalTitle.textContent = `Monthly Progress - ${habitName}`;
        monthlyModalContent.innerHTML = `
            <div class="flex items-center justify-center h-40">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="ml-3 text-gray-600">Loading monthly data...</span>
            </div>
        `;
        
        monthlyModal.classList.remove('hidden');
        
        // Load monthly view content via AJAX
        fetch(`monthly-view.php?habit_id=${habitId}`)
            .then(response => response.text())
            .then(data => {
                monthlyModalContent.innerHTML = data;
            })
            .catch(error => {
                monthlyModalContent.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Failed to load monthly view</p>
                    </div>
                `;
            });
    }

    // Close modal
    if (closeMonthlyModal) {
        closeMonthlyModal.addEventListener('click', function() {
            monthlyModal.classList.add('hidden');
        });
    }

    // Close modal when clicking outside
    if (monthlyModal) {
        monthlyModal.addEventListener('click', function(e) {
            if (e.target === monthlyModal) {
                monthlyModal.classList.add('hidden');
            }
        });
    }

    // Enhanced complete habit functionality
    const completeButtons = document.querySelectorAll('.complete-habit-btn');
    completeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const habitId = this.dataset.habitId;
            markHabitComplete(habitId);
        });
    });

    // Enhanced delete habit functionality
    const deleteButtons = document.querySelectorAll('.delete-habit-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const habitId = this.dataset.habitId;
            const habitName = this.dataset.habitName;
            showConfirmationModal('Delete Habit', `Are you sure you want to delete "${habitName}"? This action cannot be undone.`, () => {
                deleteHabit(habitId);
            });
        });
    });

    // Enhanced edit habit functionality
    const editButtons = document.querySelectorAll('.edit-habit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const habitId = this.dataset.habitId;
            const habitName = this.dataset.habitName;
            const habitDescription = this.dataset.habitDescription;
            const habitFrequency = this.dataset.habitFrequency;
            const habitStartDate = this.dataset.habitStartDate;
            const habitEndDate = this.dataset.habitEndDate;
            
            // Populate edit form
            openEditHabitModal(habitId, habitName, habitDescription, habitFrequency, habitStartDate, habitEndDate);
        });
    });
}

// ==================== HABITS PAGE FUNCTIONALITY ====================
// Description: Complete functionality for habits page buttons and modals
// Usage: Works on habits.php page
// ==================================================================

function initHabitsPageFunctionality() {
    // Initialize all habit functionality
    initMonthlyViewModal();
    initCompleteHabitButtons();
    initEditHabitButtons();
    initDeleteHabitButtons();
    initHabitPreviews();
}

// Monthly View Modal functionality
function initMonthlyViewModal() {
    const monthlyModal = document.getElementById('monthly-view-modal');
    const closeMonthlyModal = document.getElementById('close-monthly-modal');
    const monthlyModalTitle = document.getElementById('monthly-modal-title');
    const monthlyModalContent = document.getElementById('monthly-modal-content');

    // Close modal functions
    function closeModal() {
        monthlyModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openModal() {
        monthlyModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close modal events
    if (closeMonthlyModal) {
        closeMonthlyModal.addEventListener('click', closeModal);
    }

    if (monthlyModal) {
        monthlyModal.addEventListener('click', function(e) {
            if (e.target === monthlyModal) {
                closeModal();
            }
        });
    }

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !monthlyModal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Monthly view button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.monthly-view-btn')) {
            const button = e.target.closest('.monthly-view-btn');
            const habitId = button.dataset.habitId;
            const habitName = button.dataset.habitName;
            
            openMonthlyView(habitId, habitName);
        }
    });

    // Monthly preview click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.monthly-preview')) {
            const preview = e.target.closest('.monthly-preview');
            const habitId = preview.dataset.habitId;
            const habitName = preview.dataset.habitName;
            
            openMonthlyView(habitId, habitName);
        }
    });

    // Function to open monthly view
    function openMonthlyView(habitId, habitName) {
        monthlyModalTitle.textContent = `Monthly Progress - ${habitName}`;
        monthlyModalContent.innerHTML = `
            <div class="flex items-center justify-center h-40">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="ml-3 text-gray-600">Loading monthly data...</span>
            </div>
        `;
        
        openModal();
        
        // Load monthly view content
        fetch(`monthly-view.php?habit_id=${habitId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(data => {
                monthlyModalContent.innerHTML = data;
                initMonthlyViewNavigation(); // Initialize navigation within modal
            })
            .catch(error => {
                console.error('Error loading monthly view:', error);
                monthlyModalContent.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Failed to load monthly view</p>
                        <p class="text-sm text-gray-500 mt-1">Please try again later</p>
                    </div>
                `;
            });
    }

    // Initialize navigation within monthly view modal
    function initMonthlyViewNavigation() {
        const navLinks = monthlyModalContent.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const habitId = url.searchParams.get('habit_id');
                const month = url.searchParams.get('month');
                
                monthlyModalContent.innerHTML = `
                    <div class="flex items-center justify-center h-40">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <span class="ml-3 text-gray-600">Loading...</span>
                    </div>
                `;
                
                fetch(`monthly-view.php?habit_id=${habitId}&month=${month}`)
                    .then(response => response.text())
                    .then(data => {
                        monthlyModalContent.innerHTML = data;
                        initMonthlyViewNavigation(); // Re-initialize navigation
                    });
            });
        });
    }
}

// Complete habit functionality
function initCompleteHabitButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.complete-habit-btn')) {
            const button = e.target.closest('.complete-habit-btn');
            const habitId = button.dataset.habitId;
            
            showLoading();
            
            const formData = new FormData();
            formData.append('action', 'complete');
            formData.append('habit_id', habitId);
            
            fetch('habits.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Habit marked as complete! 🎉', 'success');
                    // Update the UI immediately
                    updateHabitUI(button, habitId);
                } else {
                    showToast(data.message || 'Failed to mark habit as complete', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while completing the habit', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        }
    });
}

// Helper function to update UI after completion
function updateHabitUI(button, habitId) {
    const habitCard = button.closest('.app-card');
    if (habitCard) {
        // Update status badge
        const statusBadge = habitCard.querySelector('.px-3');
        if (statusBadge) {
            statusBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800';
            statusBadge.textContent = 'Completed';
        }
        
        // Remove complete button
        button.remove();
        
        // Update today's date in monthly preview
        const today = new Date().getDate();
        const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).getDay();
        const dayElement = habitCard.querySelector(`.monthly-preview > div:nth-child(${today + firstDay})`);
        if (dayElement) {
            dayElement.className = 'w-4 h-4 rounded-sm bg-green-400 border-green-500';
        }
    }
}

// Edit habit functionality
function initEditHabitButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-habit-btn')) {
            const button = e.target.closest('.edit-habit-btn');
            const habitId = button.dataset.habitId;
            const habitName = button.dataset.habitName;
            
            // For now, show a message - you'll need to implement the edit modal
            showToast(`Edit functionality for "${habitName}" will be implemented soon`, 'info');
            
            // You can implement a proper edit modal here later:
            // openEditHabitModal(habitId, habitName, ...);
        }
    });
}

// Delete habit functionality
function initDeleteHabitButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-habit-btn')) {
            const button = e.target.closest('.delete-habit-btn');
            const habitId = button.dataset.habitId;
            const habitName = button.dataset.habitName;
            
            showConfirmationModal(
                'Delete Habit',
                `Are you sure you want to delete "${habitName}"? This action cannot be undone. All progress data will be permanently lost.`,
                () => {
                    deleteHabit(habitId, habitName);
                }
            );
        }
    });
}

// Enhanced delete habit function
function deleteHabit(habitId, habitName) {
    showLoading();
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('habit_id', habitId);
    
    fetch('habits.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(`"${habitName}" has been deleted successfully`, 'success');
            // Remove the habit card from UI
            const habitCard = document.querySelector(`.delete-habit-btn[data-habit-id="${habitId}"]`)?.closest('.app-card');
            if (habitCard) {
                habitCard.style.opacity = '0';
                habitCard.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    habitCard.remove();
                    // If no habits left, show empty state
                    if (document.querySelectorAll('.app-card').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            showToast(data.message || 'Failed to delete habit', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the habit', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// Monthly preview hover effects
function initHabitPreviews() {
    const monthlyPreviews = document.querySelectorAll('.monthly-preview');
    
    monthlyPreviews.forEach(preview => {
        // Hover effects
        preview.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            this.style.cursor = 'pointer';
        });
        
        preview.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
        
        // Add tooltip on hover
        preview.addEventListener('mouseenter', function() {
            const habitName = this.dataset.habitName;
            this.title = `Click to view monthly progress for ${habitName}`;
        });
    });
}

// ==================== HABIT COMPLETION ANIMATIONS ====================
// Description: Visual feedback for habit completion
// Usage: Works on habits.php page
// =====================================================================

function initHabitCompletionAnimations() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.complete-habit-btn')) {
            const button = e.target.closest('.complete-habit-btn');
            const habitCard = button.closest('.app-card');
            
            // Add completion animation
            habitCard.classList.add('habit-completed');
            
            // Create confetti effect
            createConfettiEffect(habitCard);
            
            // Show success message after a delay
            setTimeout(() => {
                const habitName = habitCard.querySelector('h3').textContent;
                showToast(`Great job! You completed "${habitName}"! 🎉`, 'success');
            }, 500);
        }
    });
}

// Create confetti effect
function createConfettiEffect(element) {
    const rect = element.getBoundingClientRect();
    const confettiCount = 30;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.cssText = `
            position: fixed;
            width: 8px;
            height: 8px;
            background: ${getRandomColor()};
            border-radius: 1px;
            left: ${rect.left + rect.width/2}px;
            top: ${rect.top + rect.height/2}px;
            pointer-events: none;
            z-index: 1000;
        `;
        
        document.body.appendChild(confetti);
        
        // Animate confetti
        const angle = Math.random() * Math.PI * 2;
        const speed = 2 + Math.random() * 3;
        const x = Math.cos(angle) * speed;
        const y = Math.sin(angle) * speed;
        
        let opacity = 1;
        let position = 0;
        
        const animate = () => {
            position++;
            opacity -= 0.02;
            
            confetti.style.transform = `translate(${x * position}px, ${y * position + 0.5 * 0.1 * position * position}px)`;
            confetti.style.opacity = opacity;
            
            if (opacity > 0) {
                requestAnimationFrame(animate);
            } else {
                confetti.remove();
            }
        };
        
        animate();
    }
}

// Get random color for confetti
function getRandomColor() {
    const colors = [
        '#ef4444', '#f97316', '#eab308', '#22c55e', 
        '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

// ==================== PROGRESS TRACKING ENHANCEMENTS ====================
// Description: Enhanced progress tracking with visual indicators
// Usage: Works on all pages with progress tracking
// ========================================================================

function initProgressTracking() {
    // Update progress bars
    updateProgressBars();
    
    // Add progress change animations
    initProgressAnimations();
    
    // Initialize streak tracking
    initStreakTracking();
}

// Update all progress bars on the page
function updateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const progress = bar.dataset.progress || 0;
        const fill = bar.querySelector('.progress-fill');
        
        if (fill) {
            // Set width with smooth transition
            setTimeout(() => {
                fill.style.width = `${progress}%`;
                fill.style.transition = 'width 0.8s ease-in-out';
                
                // Update text if present
                const text = bar.querySelector('.progress-text');
                if (text) {
                    text.textContent = `${Math.round(progress)}%`;
                }
            }, 100);
        }
    });
}

// Add animations for progress changes
function initProgressAnimations() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-progress') {
                const bar = mutation.target;
                const progress = bar.dataset.progress || 0;
                const fill = bar.querySelector('.progress-fill');
                
                if (fill) {
                    fill.style.width = `${progress}%`;
                    
                    // Add pulse animation for significant progress
                    if (progress > 0) {
                        fill.classList.add('progress-pulse');
                        setTimeout(() => {
                            fill.classList.remove('progress-pulse');
                        }, 1000);
                    }
                }
            }
        });
    });
    
    // Observe all progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        observer.observe(bar, { attributes: true });
    });
}

// Track and display streaks
function initStreakTracking() {
    const streakElements = document.querySelectorAll('.streak-display');
    
    streakElements.forEach(element => {
        const streak = parseInt(element.dataset.streak) || 0;
        
        if (streak > 0) {
            // Add fire emoji for streaks
            const fireCount = Math.min(Math.floor(streak / 7) + 1, 5);
            const fireEmojis = '🔥'.repeat(fireCount);
            
            element.innerHTML += ` <span class="streak-fire">${fireEmojis}</span>`;
            
            // Add celebration for milestone streaks
            if (streak % 7 === 0) {
                element.classList.add('streak-milestone');
                
                // Show celebration message for new milestones
                if (streak === 7 || streak === 30 || streak === 90) {
                    setTimeout(() => {
                        showToast(`Amazing! You've reached a ${streak}-day streak! 🎉`, 'success');
                    }, 1000);
                }
            }
        }
    });
}

// ==================== ACCESSIBILITY IMPROVEMENTS ====================
// Description: Enhanced accessibility features
// Usage: Works across all pages
// ====================================================================

function initAccessibility() {
    // Add keyboard navigation
    initKeyboardNavigation();
    
    // Improve focus indicators
    initFocusManagement();
    
    // Add screen reader enhancements
    initScreenReaderSupport();
    
    // Handle reduced motion preferences
    handleReducedMotion();
}

// Keyboard navigation
function initKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal:not(.hidden)');
            openModals.forEach(modal => {
                modal.classList.add('hidden');
            });
        }
        
        // Tab key management for modals
        if (e.key === 'Tab') {
            const activeModal = document.querySelector('.modal:not(.hidden)');
            if (activeModal) {
                const focusableElements = activeModal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                
                if (focusableElements.length === 0) return;
                
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            }
        }
    });
}

// Focus management
function initFocusManagement() {
    // Improve focus visibility
    document.addEventListener('focus', function(e) {
        const target = e.target;
        if (target.tagName === 'BUTTON' || target.tagName === 'A' || target.tagName === 'INPUT') {
            target.classList.add('focus-visible');
        }
    }, true);
    
    document.addEventListener('blur', function(e) {
        const target = e.target;
        target.classList.remove('focus-visible');
    }, true);
}

// Screen reader support
function initScreenReaderSupport() {
    // Add ARIA live regions for dynamic content
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.className = 'sr-only';
    document.body.appendChild(liveRegion);
    
    // Enhanced toast notifications for screen readers
    const originalShowToast = window.showToast;
    window.showToast = function(message, type = 'success') {
        originalShowToast(message, type);
        
        // Announce to screen readers
        liveRegion.textContent = message;
        
        // Clear after a delay
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 3000);
    };
}

// Reduced motion support
function handleReducedMotion() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReducedMotion) {
        // Disable animations
        document.documentElement.style.setProperty('--animation-duration', '0.01ms');
        document.documentElement.style.setProperty('--transition-duration', '0.01ms');
        
        // Remove animation classes
        document.querySelectorAll('.animate-spin, .animate-pulse, .animate-bounce').forEach(el => {
            el.classList.remove('animate-spin', 'animate-pulse', 'animate-bounce');
        });
    }
}

// ==================== PERFORMANCE OPTIMIZATIONS ====================
// Description: Performance improvements for better user experience
// Usage: Works across all pages
// ===================================================================

function initPerformanceOptimizations() {
    // Lazy loading for images
    initLazyLoading();
    
    // Debounce expensive operations
    initDebouncing();
    
    // Memory management
    initMemoryManagement();
}

// Lazy loading for images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Debounce expensive operations
function initDebouncing() {
    // Debounce resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Handle resize operations here
            updateResponsiveElements();
        }, 250);
    });
    
    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            // Handle scroll operations here
            checkScrollPosition();
        }, 100);
    });
}

// Update responsive elements
function updateResponsiveElements() {
    // Update any responsive elements here
    const calendar = document.getElementById('calendar-days');
    if (calendar) {
        // Adjust calendar layout if needed
    }
}

// Check scroll position for various features
function checkScrollPosition() {
    // Implement scroll-based features here
}

// Memory management
function initMemoryManagement() {
    // Clean up event listeners when elements are removed
    const originalRemoveChild = Node.prototype.removeChild;
    Node.prototype.removeChild = function(child) {
        if (child && typeof child.cleanup === 'function') {
            child.cleanup();
        }
        return originalRemoveChild.call(this, child);
    };
    
    // Clean up when modals are closed
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.removedNodes.forEach(function(node) {
                if (node.nodeType === 1 && typeof node.cleanup === 'function') {
                    node.cleanup();
                }
            });
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
}

// ==================== ERROR HANDLING AND RESILIENCE ====================
// Description: Comprehensive error handling and fallback mechanisms
// Usage: Works across all pages
// =======================================================================

function initErrorHandling() {
    // Global error handler
    window.addEventListener('error', handleGlobalError);
    
    // Promise rejection handler
    window.addEventListener('unhandledrejection', handlePromiseRejection);
    
    // Network status monitoring
    initNetworkMonitoring();
    
    // Fallback mechanisms
    initFallbackMechanisms();
}

// Replace the existing handleGlobalError function with this:

// Smart error handler - only shows notifications for important errors
function handleGlobalError(event) {
    console.error('Global error:', event.error);
    
    // List of errors to completely ignore (no toast, no logging)
    const ignoreErrors = [
        'ResizeObserver',
        'flatpickr',
        'Script error',
        'Loading CSS',
        'Loading font',
        'net::ERR',
        'Failed to fetch',
        'NetworkError'
    ];
    
    // Check if this is an error we should ignore
    const errorMessage = event.error?.message || event.message || '';
    const shouldIgnore = ignoreErrors.some(ignoreTerm => 
        errorMessage.includes(ignoreTerm)
    );
    
    if (shouldIgnore) {
        console.log('Ignoring non-critical error:', errorMessage);
        return;
    }
    
    // List of errors that might be worth showing to users
    const importantErrors = [
        'Authentication',
        'Permission',
        'Database',
        'Session',
        'SyntaxError',
        'TypeError',
        'ReferenceError'
    ];
    
    const isImportant = importantErrors.some(importantTerm =>
        errorMessage.includes(importantTerm)
    );
    
    // Only show toast for important errors
    if (isImportant) {
        showToast('An unexpected error occurred. Please refresh the page.', 'error');
    }
    
    // Always log to console for debugging
    console.error('Error details:', {
        message: errorMessage,
        error: event.error,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno
    });
}

// Replace the existing event listener with this more controlled version:
if (typeof window !== 'undefined') {
    // Remove any existing error listener first
    window.removeEventListener('error', handleGlobalError);
    
    // Add our improved error listener
    window.addEventListener('error', function(e) {
        // Give a small delay to avoid multiple rapid-fire errors
        setTimeout(() => handleGlobalError(e), 100);
    });
}

// Promise rejection handler
function handlePromiseRejection(event) {
    console.error('Unhandled promise rejection:', event.reason);
    
    // Don't show error for aborted fetch requests
    if (event.reason && event.reason.name === 'AbortError') {
        return;
    }
    
    showToast('An operation failed. Please try again.', 'error');
    
    // Log to server if available
    logErrorToServer(event.reason);
}

// Log errors to server
function logErrorToServer(error) {
    // Only log in production
    if (window.location.hostname !== 'localhost' && 
        window.location.hostname !== '127.0.0.1') {
        
        const errorData = {
            message: error.message,
            stack: error.stack,
            url: window.location.href,
            timestamp: new Date().toISOString()
        };
        
        // Send to server (implementation depends on your backend)
        try {
            navigator.sendBeacon('/error-log', JSON.stringify(errorData));
        } catch (e) {
            // Silent fail for error logging
        }
    }
}

// Network monitoring
function initNetworkMonitoring() {
    // Online/offline detection
    window.addEventListener('online', function() {
        showToast('Connection restored', 'success');
        // Sync any pending operations
        syncPendingOperations();
    });
    
    window.addEventListener('offline', function() {
        showToast('You are offline. Some features may not work.', 'error');
    });
    
    // Check initial network status
    if (!navigator.onLine) {
        showToast('You are offline. Some features may not work.', 'error');
    }
}

// Sync pending operations when coming back online
function syncPendingOperations() {
    // Implement any pending sync operations here
    const pendingOperations = JSON.parse(localStorage.getItem('pendingOperations') || '[]');
    
    if (pendingOperations.length > 0) {
        showToast('Syncing your data...', 'info');
        
        // Process each pending operation
        pendingOperations.forEach(op => {
            // Implement your sync logic here
        });
        
        // Clear pending operations after successful sync
        localStorage.removeItem('pendingOperations');
    }
}

// Fallback mechanisms
function initFallbackMechanisms() {
    // Fallback for missing Intersection Observer
    if (!('IntersectionObserver' in window)) {
        // Load all images immediately
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.src = img.dataset.src;
        });
    }
    
    // Fallback for missing flatpickr
    if (typeof flatpickr === 'undefined') {
        document.querySelectorAll('.datepicker').forEach(input => {
            input.type = 'date';
        });
    }
}

// ==================== ANALYTICS AND MONITORING ====================
// Description: Basic analytics and performance monitoring
// Usage: Works across all pages
// ==================================================================

function initAnalytics() {
    // Basic page view tracking
    trackPageView();
    
    // User interaction tracking
    trackUserInteractions();
    
    // Performance monitoring
    monitorPerformance();
}

// Track page views
function trackPageView() {
    const pageData = {
        url: window.location.pathname,
        title: document.title,
        timestamp: new Date().toISOString(),
        referrer: document.referrer
    };
    
    // Send to analytics endpoint
    sendAnalyticsData('pageview', pageData);
}

// Track user interactions
function trackUserInteractions() {
    // Track button clicks
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button');
        if (button) {
            const interactionData = {
                element: 'button',
                text: button.textContent.trim(),
                id: button.id,
                classes: button.className,
                page: window.location.pathname,
                timestamp: new Date().toISOString()
            };
            
            sendAnalyticsData('click', interactionData);
        }
    });
    
    // Track form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const formData = {
            formId: form.id,
            formAction: form.action,
            page: window.location.pathname,
            timestamp: new Date().toISOString()
        };
        
        sendAnalyticsData('form_submit', formData);
    });
}

// Monitor performance
function monitorPerformance() {
    // Only track performance if supported
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(function() {
                const perfData = performance.timing;
                const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                
                const performanceData = {
                    loadTime: loadTime,
                    dnsTime: perfData.domainLookupEnd - perfData.domainLookupStart,
                    tcpTime: perfData.connectEnd - perfData.connectStart,
                    requestTime: perfData.responseEnd - perfData.requestStart,
                    domReadyTime: perfData.domContentLoadedEventEnd - perfData.navigationStart,
                    page: window.location.pathname
                };
                
                sendAnalyticsData('performance', performanceData);
            }, 0);
        });
    }
}

// Send analytics data
function sendAnalyticsData(type, data) {
    // Only send in production
    if (window.location.hostname === 'localhost' || 
        window.location.hostname === '127.0.0.1') {
        return;
    }
    
    const analyticsData = {
        type: type,
        data: data,
        userId: getUserId(), // Implement your user identification
        sessionId: getSessionId() // Implement session tracking
    };
    
    // Use sendBeacon if available for better performance
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/analytics', JSON.stringify(analyticsData));
    } else {
        // Fallback to fetch
        fetch('/analytics', {
            method: 'POST',
            body: JSON.stringify(analyticsData),
            keepalive: true
        });
    }
}

// Get user ID (pseudonymous)
function getUserId() {
    let userId = localStorage.getItem('user_id');
    if (!userId) {
        userId = 'user_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('user_id', userId);
    }
    return userId;
}

// Get session ID
function getSessionId() {
    let sessionId = sessionStorage.getItem('session_id');
    if (!sessionId) {
        sessionId = 'session_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('session_id', sessionId);
    }
    return sessionId;
}

// ==================== THEME AND PREFERENCES ====================
// Description: Theme switching and user preferences
// Usage: Works across all pages
// ===============================================================

function initThemeAndPreferences() {
    // Load saved theme
    loadSavedTheme();
    
    // Initialize theme switcher
    initThemeSwitcher();
    
    // Save user preferences
    initPreferencesSaving();
}

// Load saved theme
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Use saved theme, or system preference if no saved theme
    const theme = savedTheme === 'system' ? (prefersDark ? 'dark' : 'light') : savedTheme;
    
    applyTheme(theme);
}

// Apply theme to document
function applyTheme(theme) {
    document.documentElement.classList.remove('light', 'dark');
    document.documentElement.classList.add(theme);
    document.documentElement.setAttribute('data-theme', theme);
}

// Initialize theme switcher
function initThemeSwitcher() {
    const themeSwitcher = document.getElementById('theme-switcher');
    if (!themeSwitcher) return;
    
    // Set initial state based on current theme
    const currentTheme = localStorage.getItem('theme') || 'light';
    themeSwitcher.value = currentTheme;
    
    // Handle theme change
    themeSwitcher.addEventListener('change', function() {
        const newTheme = this.value;
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme === 'system' ? 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : 
            newTheme
        );
        
        showToast(`Theme changed to ${newTheme}`, 'success');
    });
}

// Initialize preferences saving
function initPreferencesSaving() {
    // Save other preferences as needed
    const fontSizeSelect = document.getElementById('font-size');
    if (fontSizeSelect) {
        const savedFontSize = localStorage.getItem('fontSize') || 'normal';
        fontSizeSelect.value = savedFontSize;
        document.documentElement.style.fontSize = getFontSizeValue(savedFontSize);
        
        fontSizeSelect.addEventListener('change', function() {
            const fontSize = this.value;
            localStorage.setItem('fontSize', fontSize);
            document.documentElement.style.fontSize = getFontSizeValue(fontSize);
            showToast(`Font size changed to ${fontSize}`, 'success');
        });
    }
}

// Get font size value
function getFontSizeValue(size) {
    const sizes = {
        'small': '14px',
        'normal': '16px',
        'large': '18px',
        'x-large': '20px'
    };
    return sizes[size] || '16px';
}

// ==================== FINAL INITIALIZATION ====================
// Description: Final initialization and cleanup
// Usage: Runs when everything is loaded
// ==============================================================

function finalInitialization() {
    // Set current year in footer
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize copy buttons
    initCopyButtons();
    
    // Initialize share functionality
    initShareFunctionality();
    
    // Clean up any temporary data
    cleanupTemporaryData();
}

// Initialize tooltips
function initTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');
    
    elements.forEach(el => {
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            
            this.tooltipElement = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.tooltipElement = null;
            }
        });
    });
}

// Initialize copy buttons
function initCopyButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-copy]')) {
            const button = e.target.closest('[data-copy]');
            const text = button.dataset.copy;
            
            navigator.clipboard.writeText(text)
                .then(() => {
                    showToast('Copied to clipboard', 'success');
                })
                .catch(() => {
                    showToast('Failed to copy', 'error');
                });
        }
    });
}

// Initialize share functionality
function initShareFunctionality() {
    const shareButtons = document.querySelectorAll('[data-share]');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const shareData = {
                title: this.dataset.shareTitle || document.title,
                text: this.dataset.shareText || '',
                url: this.dataset.shareUrl || window.location.href
            };
            
            if (navigator.share) {
                navigator.share(shareData)
                    .then(() => showToast('Shared successfully', 'success'))
                    .catch(() => showToast('Share cancelled', 'info'));
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareData.url)
                    .then(() => showToast('Link copied to clipboard', 'success'))
                    .catch(() => showToast('Failed to copy link', 'error'));
            }
        });
    });
}

// Clean up temporary data
function cleanupTemporaryData() {
    // Clear any temporary storage that might be left over
    const now = Date.now();
    const tempData = JSON.parse(localStorage.getItem('tempData') || '{}');
    
    Object.keys(tempData).forEach(key => {
        if (tempData[key].expiry && tempData[key].expiry < now) {
            delete tempData[key];
        }
    });
    
    localStorage.setItem('tempData', JSON.stringify(tempData));
}

// Make important functions available globally
window.markHabitComplete = markHabitComplete;
window.deleteHabit = deleteHabit;
window.showEditHabitModal = showEditHabitModal;
window.showConfirmationModal = showConfirmationModal;
window.showToast = showToast;
window.showLoading = showLoading;
window.hideLoading = hideLoading;

// Show loading indicator
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.classList.remove('hidden');
}

// Hide loading indicator
function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.classList.add('hidden');
}

// Show toast notification
function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed bottom-4 right-4 z-50';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `p-4 mb-3 rounded-lg flex items-center justify-between ${
        type === 'success' ? 'bg-green-100 text-green-800' : 
        type === 'error' ? 'bg-red-100 text-red-800' : 
        'bg-blue-100 text-blue-800'
    }`;
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        </div>
        <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}