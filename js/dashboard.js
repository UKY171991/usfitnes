// Dashboard JavaScript
// AdminLTE3 Template with AJAX Operations

let monthlyChart;

$(document).ready(function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Load dashboard statistics
    loadDashboardStats();
    
    // Initialize monthly chart
    initializeMonthlyChart();
    
    // Load recent orders
    loadRecentOrders();
    
    // Load system alerts
    loadSystemAlerts();
    
    // Auto-refresh data every 5 minutes
    setInterval(function() {
        refreshDashboardData();
    }, 300000);
}

async function loadDashboardStats() {
    try {
        const response = await ajaxRequest({
            url: 'api/dashboard_api.php?action=stats',
            type: 'GET',
            showLoader: false
        });
        
        if (response.success) {
            updateStatistics(response.data);
        }
    } catch (error) {
        console.error('Failed to load dashboard statistics:', error);
        // Set default values
        updateStatistics({
            total_patients: 0,
            todays_tests: 0,
            pending_results: 0,
            total_doctors: 0
        });
    }
}

function updateStatistics(stats) {
    $('#totalPatients').text(formatNumber(stats.total_patients || 0));
    $('#todaysTests').text(formatNumber(stats.todays_tests || 0));
    $('#pendingResults').text(formatNumber(stats.pending_results || 0));
    $('#totalDoctors').text(formatNumber(stats.total_doctors || 0));
    
    // Animate counters
    animateCounters();
}

function animateCounters() {
    $('.info-box-number').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text().replace(/,/g, ''));
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(formatNumber(Math.floor(this.countNum)));
            },
            complete: function() {
                $this.text(formatNumber(this.countNum));
            }
        });
    });
}

async function initializeMonthlyChart() {
    try {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        const response = await ajaxRequest({
            url: 'api/dashboard_api.php?action=monthly_stats',
            type: 'GET',
            showLoader: false
        });
        
        let chartData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            values: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        };
        
        if (response.success && response.data) {
            chartData = response.data;
        }
        
        monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Tests Performed',
                    data: chartData.values,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#007bff',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
    } catch (error) {
        console.error('Failed to initialize monthly chart:', error);
        showFallbackChart();
    }
}

function showFallbackChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['No Data Available'],
            datasets: [{
                label: 'Tests Performed',
                data: [0],
                borderColor: '#6c757d',
                backgroundColor: 'rgba(108, 117, 125, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

async function loadRecentOrders() {
    try {
        const response = await ajaxRequest({
            url: 'api/dashboard_api.php?action=recent_orders',
            type: 'GET',
            showLoader: false
        });
        
        if (response.success) {
            displayRecentOrders(response.data);
        } else {
            showNoRecentOrders();
        }
    } catch (error) {
        console.error('Failed to load recent orders:', error);
        showNoRecentOrders();
    }
}

function displayRecentOrders(orders) {
    if (!orders || orders.length === 0) {
        showNoRecentOrders();
        return;
    }
    
    let html = '<table class="table table-striped">';
    html += '<thead><tr><th>Patient</th><th>Tests</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>';
    html += '<tbody>';
    
    orders.forEach(order => {
        const statusClass = getOrderStatusClass(order.status);
        html += `
            <tr>
                <td><strong>${order.patient_name}</strong></td>
                <td><span class="badge badge-info">${order.test_count} test(s)</span></td>
                <td><span class="badge badge-${statusClass}">${capitalizeFirst(order.status.replace('_', ' '))}</span></td>
                <td>${formatDate(order.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewTestOrder(${order.id})" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    $('#recentOrdersContainer').html(html);
}

function showNoRecentOrders() {
    const html = `
        <div class="text-center p-4">
            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Recent Test Orders</h5>
            <p class="text-muted">Test orders will appear here when created.</p>
            <button class="btn btn-primary" onclick="showAddTestOrderModal()">
                <i class="fas fa-plus mr-2"></i>Create First Test Order
            </button>
        </div>
    `;
    $('#recentOrdersContainer').html(html);
}

async function loadSystemAlerts() {
    try {
        const response = await ajaxRequest({
            url: 'api/dashboard_api.php?action=alerts',
            type: 'GET',
            showLoader: false
        });
        
        if (response.success) {
            displaySystemAlerts(response.data);
        } else {
            showNoAlerts();
        }
    } catch (error) {
        console.error('Failed to load system alerts:', error);
        showNoAlerts();
    }
}

function displaySystemAlerts(alerts) {
    if (!alerts || alerts.length === 0) {
        showNoAlerts();
        return;
    }
    
    let html = '';
    alerts.forEach(alert => {
        html += `
            <div class="alert alert-${alert.type} alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon ${alert.icon}"></i> ${alert.title}!</h5>
                ${alert.message}
            </div>
        `;
    });
    
    $('#systemAlertsContainer').html(html);
}

function showNoAlerts() {
    const html = `
        <div class="text-center">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="text-success">All Systems Normal</h5>
            <p class="text-muted">No alerts at this time.</p>
        </div>
    `;
    $('#systemAlertsContainer').html(html);
}

// Quick action functions
function showAddPatientModal() {
    // Redirect to patients page with add action
    window.location.href = 'patients.php?action=add';
}

function showAddTestOrderModal() {
    // Redirect to test orders page with create action
    window.location.href = 'test-orders.php?action=create';
}

function showAddDoctorModal() {
    // Redirect to doctors page with add action
    window.location.href = 'doctors.php?action=add';
}

function viewTestOrder(id) {
    // Redirect to test orders page with view action
    window.location.href = `test-orders.php?action=view&id=${id}`;
}

// Refresh functions
async function refreshDashboardData() {
    await loadDashboardStats();
    await loadRecentOrders();
    await loadSystemAlerts();
    
    // Update chart data
    if (monthlyChart) {
        try {
            const response = await ajaxRequest({
                url: 'api/dashboard_api.php?action=monthly_stats',
                type: 'GET',
                showLoader: false
            });
            
            if (response.success && response.data) {
                monthlyChart.data.labels = response.data.labels;
                monthlyChart.data.datasets[0].data = response.data.values;
                monthlyChart.update();
            }
        } catch (error) {
            console.error('Failed to refresh chart data:', error);
        }
    }
}

function refreshRecentOrders() {
    loadRecentOrders();
}

function refreshAlerts() {
    loadSystemAlerts();
}

// Utility functions
function getOrderStatusClass(status) {
    const statusClasses = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return statusClasses[status] || 'secondary';
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

// Global functions for external access
window.showAddPatientModal = showAddPatientModal;
window.showAddTestOrderModal = showAddTestOrderModal;
window.showAddDoctorModal = showAddDoctorModal;
window.viewTestOrder = viewTestOrder;
window.refreshRecentOrders = refreshRecentOrders;
window.refreshAlerts = refreshAlerts;