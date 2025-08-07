// Dashboard JavaScript - AdminLTE3 with AJAX
$(document).ready(function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Load dashboard data
    loadDashboardStats();
    loadRecentOrders();
    loadRecentActivities();
    loadCharts();
    loadTasks();
    
    // Set up auto-refresh
    setInterval(function() {
        loadDashboardStats();
        loadRecentOrders();
        loadRecentActivities();
    }, 30000); // Refresh every 30 seconds
});

// Initialize dashboard components
function initializeDashboard() {
    // Initialize tooltips and popovers
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    
    // Add loading animations to info boxes
    $('.info-box').addClass('fade-in');
    $('.small-box').addClass('slide-up');
    
    // Add hover effects
    $('.info-box, .small-box').hover(
        function() { $(this).addClass('elevation-3'); },
        function() { $(this).removeClass('elevation-3'); }
    );
}

// Load dashboard statistics
function loadDashboardStats() {
    makeAjaxRequest({
        url: 'api/dashboard_api.php',
        type: 'GET',
        data: { action: 'stats' },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                updateStatistics(response.data);
            }
        },
        error: function() {
            // Show error in stats only, don't show toast for silent refresh
            $('.loading-placeholder').text('Error');
        }
    });
}

// Update statistics display
function updateStatistics(stats) {
    // Update main statistics
    $('#totalPatients').html(stats.total_patients || 0);
    $('#todaysTests').html(stats.todays_tests || 0);
    $('#pendingResults').html(stats.pending_results || 0);
    $('#monthlyRevenue').html(formatCurrency(stats.monthly_revenue || 0));
    
    // Update progress bars
    updateProgressBar('#patientsProgress', stats.patients_progress || 0);
    updateProgressBar('#testsProgress', stats.tests_progress || 0);
    updateProgressBar('#resultsProgress', stats.results_progress || 0);
    updateProgressBar('#revenueProgress', stats.revenue_progress || 0);
    
    // Update small boxes
    $('#activeEquipment').html(stats.active_equipment || 0);
    $('#activeDoctors').html(stats.active_doctors || 0);
    $('#systemUsers').html(stats.system_users || 0);
    
    // Update system health
    updateSystemHealth(stats.system_health || 'good');
}

// Update progress bar
function updateProgressBar(selector, percentage) {
    $(selector).css('width', Math.min(percentage, 100) + '%');
}

// Update system health indicator
function updateSystemHealth(status) {
    const $healthBox = $('#systemHealthBox');
    const $healthText = $('#systemHealth');
    
    // Remove all status classes
    $healthBox.removeClass('bg-success bg-warning bg-danger');
    
    switch(status.toLowerCase()) {
        case 'excellent':
            $healthBox.addClass('bg-success');
            $healthText.html('<i class="fas fa-check-circle"></i> Excellent');
            break;
        case 'good':
            $healthBox.addClass('bg-success');
            $healthText.html('<i class="fas fa-thumbs-up"></i> Good');
            break;
        case 'warning':
            $healthBox.addClass('bg-warning');
            $healthText.html('<i class="fas fa-exclamation-triangle"></i> Warning');
            break;
        case 'critical':
            $healthBox.addClass('bg-danger');
            $healthText.html('<i class="fas fa-times-circle"></i> Critical');
            break;
        default:
            $healthBox.addClass('bg-info');
            $healthText.html('<i class="fas fa-info-circle"></i> Unknown');
    }
}

// Load recent orders
function loadRecentOrders() {
    makeAjaxRequest({
        url: 'ajax/recent_orders.php',
        type: 'GET',
        data: { limit: 5 },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                displayRecentOrders(response.data);
            }
        },
        error: function() {
            $('#recentOrdersTable').html('<tr><td colspan="5" class="text-center text-muted">Failed to load recent orders</td></tr>');
        }
    });
}

// Display recent orders
function displayRecentOrders(orders) {
    let html = '';
    
    if (orders && orders.length > 0) {
        orders.forEach(function(order) {
            html += `
                <tr>
                    <td><strong>#${order.order_number}</strong></td>
                    <td>
                        <a href="patients.php?id=${order.patient_id}" class="text-decoration-none">
                            ${order.patient_name}
                        </a>
                    </td>
                    <td>${order.test_type}</td>
                    <td>${getStatusBadge(order.status)}</td>
                    <td>${formatDate(order.created_date, 'DD/MM/YYYY HH:mm')}</td>
                </tr>
            `;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center text-muted">No recent orders</td></tr>';
    }
    
    $('#recentOrdersTable').html(html);
}

// Load recent activities
function loadRecentActivities() {
    makeAjaxRequest({
        url: 'ajax/recent_activities.php',
        type: 'GET',
        data: { limit: 5 },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                displayRecentActivities(response.data);
            }
        },
        error: function() {
            $('#activitiesTimeline').html('<div class="text-center text-muted">Failed to load activities</div>');
        }
    });
}

// Display recent activities
function displayRecentActivities(activities) {
    let html = '';
    
    if (activities && activities.length > 0) {
        activities.forEach(function(activity, index) {
            const iconClass = getActivityIcon(activity.type);
            const colorClass = getActivityColor(activity.type);
            
            html += `
                <div class="timeline-item">
                    <span class="time"><i class="fas fa-clock"></i> ${formatDate(activity.created_date, 'HH:mm')}</span>
                    <h3 class="timeline-header">
                        <i class="${iconClass} ${colorClass}"></i>
                        ${activity.title}
                    </h3>
                    <div class="timeline-body">
                        ${activity.description}
                        ${activity.user_name ? `<br><small class="text-muted">By: ${activity.user_name}</small>` : ''}
                    </div>
                </div>
            `;
        });
    } else {
        html = '<div class="text-center text-muted">No recent activities</div>';
    }
    
    $('#activitiesTimeline').html(html);
}

// Get activity icon based on type
function getActivityIcon(type) {
    const icons = {
        'patient_added': 'fas fa-user-plus',
        'test_ordered': 'fas fa-flask',
        'result_completed': 'fas fa-file-medical',
        'user_login': 'fas fa-sign-in-alt',
        'equipment_maintenance': 'fas fa-tools',
        'report_generated': 'fas fa-chart-bar',
        'default': 'fas fa-info-circle'
    };
    
    return icons[type] || icons.default;
}

// Get activity color based on type
function getActivityColor(type) {
    const colors = {
        'patient_added': 'text-success',
        'test_ordered': 'text-info',
        'result_completed': 'text-primary',
        'user_login': 'text-warning',
        'equipment_maintenance': 'text-danger',
        'report_generated': 'text-secondary',
        'default': 'text-muted'
    };
    
    return colors[type] || colors.default;
}

// Load and display charts
function loadCharts() {
    loadOrdersTrendChart();
    loadTestTypesChart();
}

// Load orders trend chart
function loadOrdersTrendChart() {
    const period = $('#chartPeriod').val() || 30;
    
    makeAjaxRequest({
        url: 'api/dashboard_api.php',
        type: 'GET',
        data: {
            action: 'orders_trend',
            period: period
        },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                renderOrdersTrendChart(response.data);
            }
        }
    });
}

// Render orders trend chart
function renderOrdersTrendChart(data) {
    const ctx = document.getElementById('ordersChart');
    
    if (window.ordersChart) {
        window.ordersChart.destroy();
    }
    
    window.ordersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Test Orders',
                data: data.values || [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    }
                }
            }
        }
    });
}

// Load test types distribution chart
function loadTestTypesChart() {
    makeAjaxRequest({
        url: 'api/dashboard_api.php',
        type: 'GET',
        data: { action: 'test_types_distribution' },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                renderTestTypesChart(response.data);
            }
        }
    });
}

// Render test types chart
function renderTestTypesChart(data) {
    const ctx = document.getElementById('testTypesChart');
    
    if (window.testTypesChart) {
        window.testTypesChart.destroy();
    }
    
    window.testTypesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
}

// Load tasks
function loadTasks() {
    makeAjaxRequest({
        url: 'ajax/dashboard_stats.php',
        type: 'GET',
        data: { action: 'tasks' },
        showLoading: false,
        success: function(response) {
            if (response.success && response.data) {
                displayTasks(response.data);
            }
        }
    });
}

// Display tasks
function displayTasks(tasks) {
    const completedTasks = tasks.completed || [];
    const pendingTasks = tasks.pending || [];
    
    // Display completed tasks
    let completedHtml = '';
    if (completedTasks.length > 0) {
        completedTasks.forEach(function(task) {
            completedHtml += `
                <li class="done">
                    <span class="text">${task.title}</span>
                    <small class="badge badge-success">
                        <i class="far fa-clock"></i> ${formatDate(task.completed_date, 'HH:mm')}
                    </small>
                </li>
            `;
        });
    } else {
        completedHtml = '<li class="text-center text-muted">No completed tasks</li>';
    }
    $('#completedTasks').html(completedHtml);
    
    // Display pending tasks
    let pendingHtml = '';
    if (pendingTasks.length > 0) {
        pendingTasks.forEach(function(task) {
            pendingHtml += `
                <li>
                    <span class="text">${task.title}</span>
                    <small class="badge badge-${task.priority === 'high' ? 'danger' : task.priority === 'medium' ? 'warning' : 'info'}">
                        ${task.priority}
                    </small>
                    <div class="tools">
                        <i class="fas fa-check" onclick="completeTask(${task.id})"></i>
                    </div>
                </li>
            `;
        });
    } else {
        pendingHtml = '<li class="text-center text-muted">No pending tasks</li>';
    }
    $('#pendingTasks').html(pendingHtml);
}

// Update charts when period changes
function updateCharts() {
    loadOrdersTrendChart();
}

// Refresh functions
function refreshRecentOrders() {
    loadRecentOrders();
    showInfoToast('Recent orders refreshed');
}

function refreshActivities() {
    loadRecentActivities();
    showInfoToast('Activities refreshed');
}

// Complete task function
function completeTask(taskId) {
    makeAjaxRequest({
        url: 'ajax/dashboard_stats.php',
        type: 'POST',
        data: {
            action: 'complete_task',
            task_id: taskId
        },
        success: function(response) {
            if (response.success) {
                showSuccessToast('Task completed successfully');
                loadTasks();
            } else {
                showErrorToast(response.message || 'Failed to complete task');
            }
        }
    });
}

// Show add task modal (placeholder)
function showAddTaskModal() {
    // This would open a modal to add new tasks
    showInfoToast('Add task feature coming soon');
}

// Auto-refresh dashboard data every 5 minutes
setInterval(function() {
    loadDashboardStats();
    loadCharts();
}, 300000);
