/**
 * Dashboard JavaScript for USFitness Lab
 * Handles dashboard-specific AJAX operations and charts
 */

$(document).ready(function() {
    // Load dashboard data
    loadDashboardStats();
    loadRecentActivities();
    loadRecentOrders();
    loadMonthlyChart();
    
    // Refresh dashboard data every 60 seconds
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivities();
        loadRecentOrders();
    }, 60000);
    
    showToast('info', 'Dashboard loaded successfully');
});

/**
 * Refresh all dashboard data manually
 */
function refreshData() {
    showToast('info', 'Refreshing dashboard data...');
    loadDashboardStats();
    loadRecentActivities();
    loadRecentOrders();
    loadMonthlyChart();
}

/**
 * Logout function
 */
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

/**
 * Load dashboard statistics
 */
function loadDashboardStats() {
    $.get('api/dashboard_api.php', { action: 'get_counts' })
        .done(function(response) {
            if (response.success && response.data) {
                renderStatsCards(response.data);
            } else {
                showToast('error', 'Failed to load dashboard statistics');
            }
        })
        .fail(function() {
            showToast('error', 'Failed to load dashboard statistics');
        });
}

/**
 * Render statistics cards
 */
function renderStatsCards(data) {
    const statsHTML = `
        <!-- Total Patients -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${data.total_patients || 0}</h3>
                    <p>Total Patients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="patients.php" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${data.todays_orders || 0}</h3>
                    <p>Today's Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="test-orders.php" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Pending Results -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${data.pending_results || 0}</h3>
                    <p>Pending Results</p>
                </div>
                <div class="icon">
                    <i class="fas fa-vial"></i>
                </div>
                <a href="results.php" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Doctors -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${data.total_doctors || 0}</h3>
                    <p>Total Doctors</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <a href="doctors.php" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    `;
    
    $('#stats-cards').html(statsHTML);
}

/**
 * Load recent activities
 */
function loadRecentActivities() {
    $.get('api/dashboard_api.php', { action: 'get_recent_activities', limit: 10 })
        .done(function(response) {
            if (response.success && response.data) {
                renderRecentActivities(response.data.activities || []);
            } else {
                $('#recent-activities').html('<div class="text-center text-muted">No recent activities found</div>');
            }
        })
        .fail(function() {
            $('#recent-activities').html('<div class="text-center text-danger">Failed to load activities</div>');
        });
}

/**
 * Render recent activities
 */
function renderRecentActivities(activities) {
    if (activities.length === 0) {
        $('#recent-activities').html('<div class="text-center text-muted">No recent activities found</div>');
        return;
    }
    
    let html = '';
    
    activities.forEach(function(activity, index) {
        const iconClass = getActivityIcon(activity.action);
        const colorClass = getActivityColor(activity.action);
        
        html += `
            <div class="d-flex align-items-center mb-2">
                <div class="mr-3">
                    <i class="${iconClass} text-${colorClass}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="font-weight-bold">${activity.action}</div>
                    <div class="small text-muted">${activity.details || 'No additional details'}</div>
                    <div class="small text-muted">${activity.time_ago || formatDate(activity.created_at)}</div>
                </div>
            </div>
        `;
    });
    
    $('#recent-activities').html(html);
}

/**
 * Get activity icon based on action
 */
function getActivityIcon(action) {
    const iconMap = {
        'login': 'fas fa-sign-in-alt',
        'logout': 'fas fa-sign-out-alt',
        'patient_created': 'fas fa-user-plus',
        'patient_updated': 'fas fa-user-edit',
        'order_created': 'fas fa-plus-circle',
        'order_updated': 'fas fa-edit',
        'result_added': 'fas fa-vial'
    };
    
    return iconMap[action] || 'fas fa-info-circle';
}

/**
 * Get activity color based on action
 */
function getActivityColor(action) {
    const colorMap = {
        'login': 'success',
        'logout': 'secondary',
        'patient_created': 'info',
        'patient_updated': 'warning',
        'order_created': 'primary',
        'order_updated': 'warning',
        'result_added': 'success'
    };
    
    return colorMap[action] || 'info';
}

/**
 * Load recent orders
 */
function loadRecentOrders() {
    $.get('api/dashboard_api.php', { action: 'get_recent_orders', limit: 10 })
        .done(function(response) {
            if (response.success && response.data) {
                renderRecentOrders(response.data.orders || []);
            } else {
                $('#recent-orders-body').html('<tr><td colspan="5" class="text-center text-muted">No recent orders found</td></tr>');
            }
        })
        .fail(function() {
            $('#recent-orders-body').html('<tr><td colspan="5" class="text-center text-danger">Failed to load orders</td></tr>');
        });
}

/**
 * Render recent orders
 */
function renderRecentOrders(orders) {
    if (orders.length === 0) {
        $('#recent-orders-body').html('<tr><td colspan="5" class="text-center text-muted">No recent orders found</td></tr>');
        return;
    }
    
    let html = '';
    
    orders.forEach(function(order) {
        const statusClass = getStatusClass(order.status);
        
        html += `
            <tr>
                <td>
                    <a href="test-orders.php?id=${order.id}" class="text-primary">
                        ${order.order_number}
                    </a>
                </td>
                <td>
                    <a href="patients.php?id=${order.patient_id}" class="text-info">
                        ${order.patient_name}
                    </a>
                </td>
                <td>${order.doctor_name || '<em>Not assigned</em>'}</td>
                <td>
                    <span class="badge ${statusClass}">
                        ${capitalize(order.status)}
                    </span>
                </td>
                <td>${formatDate(order.order_date)}</td>
            </tr>
        `;
    });
    
    $('#recent-orders-body').html(html);
}

/**
 * Get CSS class for status badges
 */
function getStatusClass(status) {
    const statusMap = {
        'pending': 'badge-warning',
        'processing': 'badge-info',
        'completed': 'badge-success',
        'cancelled': 'badge-danger'
    };
    
    return statusMap[status] || 'badge-secondary';
}

/**
 * Load and render monthly statistics chart
 */
function loadMonthlyChart() {
    $.get('api/dashboard_api.php', { action: 'get_monthly_stats' })
        .done(function(response) {
            if (response.success && response.data) {
                renderMonthlyChart(response.data);
            }
        })
        .fail(function() {
            console.log('Failed to load monthly chart data');
        });
}

/**
 * Render monthly chart using Chart.js
 */
function renderMonthlyChart(data) {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.months || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Test Orders',
                data: data.orders || [12, 19, 3, 5, 2, 3],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Completed Results',
                data: data.results || [7, 11, 5, 8, 3, 7],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Statistics Overview'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
