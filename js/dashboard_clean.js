// Dashboard JavaScript - AdminLTE3 with AJAX
// PathLab Pro - Dashboard Management System

// Initialize dashboard when DOM is ready
$(document).ready(function() {
    console.log('Dashboard initializing...');
    initializeDashboard();
});

// Main initialization function
function initializeDashboard() {
    try {
        // Load dashboard statistics
        loadDashboardStats();
        
        // Initialize charts (if available)
        initializeCharts();
        
        // Set up auto-refresh
        setupAutoRefresh();
        
        console.log('Dashboard initialized successfully');
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        showToast('error', 'Failed to initialize dashboard: ' + error.message);
    }
}

// Load dashboard statistics
function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard_api.php',
        type: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                updateStatCards(response.data);
                updateRecentActivities(response.data.recent_activities || []);
            } else {
                console.log('No dashboard data available');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load dashboard stats:', error);
            // Don't show error toast for dashboard stats - just log it
        }
    });
}

// Update statistics cards
function updateStatCards(data) {
    // Update total patients
    if (data.total_patients !== undefined) {
        $('#totalPatients').find('.loading-placeholder').html(data.total_patients);
        animateValue('#totalPatients .loading-placeholder', 0, data.total_patients, 1000);
    }
    
    // Update today's tests
    if (data.todays_tests !== undefined) {
        $('#todaysTests').find('.loading-placeholder').html(data.todays_tests);
        animateValue('#todaysTests .loading-placeholder', 0, data.todays_tests, 1000);
    }
    
    // Update pending results
    if (data.pending_results !== undefined) {
        $('#pendingResults').find('.loading-placeholder').html(data.pending_results);
        animateValue('#pendingResults .loading-placeholder', 0, data.pending_results, 1000);
    }
    
    // Update monthly revenue
    if (data.monthly_revenue !== undefined) {
        $('#monthlyRevenue').find('.loading-placeholder').html('₹' + formatNumber(data.monthly_revenue));
    }
    
    // Update progress bars based on data
    updateProgressBars(data);
}

// Update progress bars
function updateProgressBars(data) {
    // Patients progress (example: show percentage of target)
    var patientsProgress = Math.min((data.total_patients || 0) / 100 * 100, 100);
    $('#patientsProgress').css('width', patientsProgress + '%');
    
    // Tests progress
    var testsProgress = Math.min((data.todays_tests || 0) / 50 * 100, 100);
    $('#testsProgress').css('width', testsProgress + '%');
    
    // Results progress
    var resultsProgress = Math.min((data.pending_results || 0) / 20 * 100, 100);
    $('#resultsProgress').css('width', resultsProgress + '%');
    
    // Revenue progress
    var revenueProgress = Math.min((data.monthly_revenue || 0) / 100000 * 100, 100);
    $('#revenueProgress').css('width', revenueProgress + '%');
}

// Update recent activities
function updateRecentActivities(activities) {
    var container = $('#recentActivities');
    if (!container.length) return;
    
    var html = '';
    
    if (activities.length > 0) {
        activities.forEach(function(activity) {
            html += `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="${activity.icon || 'fas fa-info-circle'}"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text">${activity.message}</p>
                        <small class="text-muted">${activity.time}</small>
                    </div>
                </div>
            `;
        });
    } else {
        html = '<div class="text-center text-muted">No recent activities</div>';
    }
    
    container.html(html);
}

// Initialize charts (placeholder for chart libraries)
function initializeCharts() {
    // This would initialize Chart.js or other chart libraries
    // For now, just log that charts would be initialized here
    console.log('Charts would be initialized here');
}

// Set up auto-refresh for dashboard
function setupAutoRefresh() {
    // Refresh dashboard stats every 5 minutes
    setInterval(function() {
        loadDashboardStats();
    }, 300000); // 5 minutes
}

// Animate counter values
function animateValue(selector, start, end, duration) {
    var element = $(selector);
    var range = end - start;
    var current = start;
    var increment = end > start ? 1 : -1;
    var stepTime = Math.abs(Math.floor(duration / range));
    
    var timer = setInterval(function() {
        current += increment;
        element.html(current);
        if (current === end) {
            clearInterval(timer);
        }
    }, stepTime);
}

// Format numbers with commas
function formatNumber(num) {
    if (!num) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Refresh dashboard manually
function refreshDashboard() {
    showToast('info', 'Refreshing dashboard...');
    loadDashboardStats();
}

// Quick actions
function quickAddPatient() {
    window.location.href = 'patients.php';
}

function quickAddDoctor() {
    window.location.href = 'doctors.php';
}

function quickViewReports() {
    window.location.href = 'reports.php';
}

// Make functions globally accessible
window.refreshDashboard = refreshDashboard;
window.quickAddPatient = quickAddPatient;
window.quickAddDoctor = quickAddDoctor;
window.quickViewReports = quickViewReports;
