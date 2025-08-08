$(document).ready(function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Load dashboard data
    loadDashboardStats();
    loadMonthlyChart();
    loadPieChart();
    loadRecentOrders();
    loadRecentActivities();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        loadDashboardStats();
        loadRecentOrders();
        loadRecentActivities();
    }, 300000);
});

// Initialize dashboard functions
function initializeDashboard() {
    console.log('Dashboard initialized');
    
    // Configure toastr if available
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }
}

// Load dashboard statistics
function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'POST',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#patientsCount').text(stats.patients || 0);
                $('#doctorsCount').text(stats.doctors || 0);
                $('#testOrdersCount').text(stats.test_orders || 0);
                $('#equipmentCount').text(stats.equipment || 0);
            } else {
                console.error('Failed to load stats:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Stats error:', error);
            // Set default values on error
            $('#patientsCount').text('--');
            $('#doctorsCount').text('--');
            $('#testOrdersCount').text('--');
            $('#equipmentCount').text('--');
        }
    });
}

// Chart variables
let monthlyChart = null;
let pieChart = null;

// Load monthly statistics chart
function loadMonthlyChart() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'POST',
        data: { action: 'monthly_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                renderMonthlyChart(data);
            } else {
                console.error('Failed to load monthly stats:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Monthly stats error:', error);
        }
    });
}

// Render monthly chart
function renderMonthlyChart(data) {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.months || [],
            datasets: [{
                label: 'Test Orders',
                data: data.orders || [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'New Patients',
                data: data.patients || [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}

// Load pie chart data
function loadPieChart() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'POST',
        data: { action: 'test_types' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                renderPieChart(data);
            } else {
                console.error('Failed to load test types:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Test types error:', error);
        }
    });
}

// Render pie chart
function renderPieChart(data) {
    const ctx = document.getElementById('pieChart').getContext('2d');
    
    if (pieChart) {
        pieChart.destroy();
    }
    
    pieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderWidth: 2
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

// Load recent orders
function loadRecentOrders() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'POST',
        data: { action: 'recent_orders' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const orders = response.data;
                renderRecentOrders(orders);
            } else {
                $('#recentOrdersTable').html('<tr><td colspan="6" class="text-center">No recent orders found</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Recent orders error:', error);
            $('#recentOrdersTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading orders</td></tr>');
        }
    });
}

// Render recent orders
function renderRecentOrders(orders) {
    let html = '';
    
    if (orders.length === 0) {
        html = '<tr><td colspan="6" class="text-center">No recent orders found</td></tr>';
    } else {
        orders.forEach(function(order) {
            const statusBadges = {
                'pending': 'warning',
                'in_progress': 'info',
                'completed': 'success',
                'cancelled': 'danger'
            };
            
            html += `
                <tr>
                    <td><strong>#${order.id}</strong></td>
                    <td>${order.patient_name || 'N/A'}</td>
                    <td>${order.doctor_name || 'N/A'}</td>
                    <td>${order.test_type || 'N/A'}</td>
                    <td><span class="badge badge-${statusBadges[order.status] || 'secondary'}">${(order.status || 'unknown').toUpperCase()}</span></td>
                    <td>${order.created_at ? new Date(order.created_at).toLocaleDateString() : 'N/A'}</td>
                </tr>
            `;
        });
    }
    
    $('#recentOrdersTable').html(html);
}

// Load recent activities
function loadRecentActivities() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'POST',
        data: { action: 'recent_activities' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const activities = response.data;
                renderRecentActivities(activities);
            } else {
                $('#activitiesTimeline').html('<div class="text-center">No recent activities found</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Recent activities error:', error);
            $('#activitiesTimeline').html('<div class="text-center text-danger">Error loading activities</div>');
        }
    });
}

// Render recent activities
function renderRecentActivities(activities) {
    let html = '';
    
    if (activities.length === 0) {
        html = '<div class="text-center">No recent activities found</div>';
    } else {
        activities.forEach(function(activity, index) {
            const icons = {
                'patient_added': 'fas fa-user-plus text-success',
                'order_created': 'fas fa-clipboard-list text-info',
                'result_uploaded': 'fas fa-vial text-warning',
                'user_login': 'fas fa-sign-in-alt text-primary'
            };
            
            html += `
                <div class="timeline-item">
                    <i class="${icons[activity.type] || 'fas fa-circle text-secondary'}"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="far fa-clock"></i> ${activity.created_at ? new Date(activity.created_at).toLocaleDateString() : 'Recent'}</span>
                        <h3 class="timeline-header">${activity.title || 'Activity'}</h3>
                        <div class="timeline-body">
                            ${activity.description || 'No description available'}
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    $('#activitiesTimeline').html(html);
}

// Refresh functions
function refreshDashboard() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing dashboard data...');
    }
    loadDashboardStats();
    loadRecentOrders();
    loadRecentActivities();
}

function refreshMonthlyChart() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing monthly chart...');
    }
    loadMonthlyChart();
}

function refreshPieChart() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing pie chart...');
    }
    loadPieChart();
}

function refreshRecentOrders() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing recent orders...');
    }
    loadRecentOrders();
}

function refreshActivities() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing activities...');
    }
    loadRecentActivities();
}

// Global refresh function
window.refreshData = function() {
    refreshDashboard();
};
