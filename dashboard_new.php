<?php
require_once 'includes/adminlte_template.php';

// Dashboard content function
function dashboardContent() {
    global $pdo;
    
    // Get statistics
    try {
        $patients_count = $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn();
        $doctors_count = $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn();
        $pending_tests = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'")->fetchColumn();
        $equipment_count = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'active'")->fetchColumn();
    } catch (Exception $e) {
        $patients_count = $doctors_count = $pending_tests = $equipment_count = 0;
    }
    ?>
    
    <!-- Info boxes -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <?php echo createInfoBox('Total Patients', $patients_count, 'fas fa-user-injured', 'primary', 'patients.php'); ?>
        </div>
        <div class="col-lg-3 col-6">
            <?php echo createInfoBox('Active Doctors', $doctors_count, 'fas fa-user-md', 'success', 'doctors.php'); ?>
        </div>
        <div class="col-lg-3 col-6">
            <?php echo createInfoBox('Pending Tests', $pending_tests, 'fas fa-flask', 'warning', 'test-orders.php'); ?>
        </div>
        <div class="col-lg-3 col-6">
            <?php echo createInfoBox('Equipment', $equipment_count, 'fas fa-tools', 'info', 'equipment.php'); ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-6">
            <?php
            echo createCard(
                '<i class="fas fa-clock mr-2"></i>Recent Activity',
                '<div id="recent-activity">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-green">Today</span>
                        </div>
                        <div>
                            <i class="fas fa-user bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 2 mins ago</span>
                                <h3 class="timeline-header">New patient registered</h3>
                                <div class="timeline-body">
                                    John Doe has been registered as a new patient.
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-flask bg-yellow"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 5 mins ago</span>
                                <h3 class="timeline-header">Test order created</h3>
                                <div class="timeline-body">
                                    Blood test ordered for patient PAT001.
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check bg-green"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 15 mins ago</span>
                                <h3 class="timeline-header">Test completed</h3>
                                <div class="timeline-body">
                                    CBC test results are ready for review.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                '<button class="btn btn-sm btn-primary" onclick="refreshActivity()">
                    <i class="fas fa-sync"></i> Refresh
                </button>',
                '<a href="activity.php" class="btn btn-sm btn-default">View All Activity</a>'
            );
            ?>
        </div>
        
        <!-- Quick Stats Chart -->
        <div class="col-lg-6">
            <?php
            echo createCard(
                '<i class="fas fa-chart-line mr-2"></i>Monthly Statistics',
                '<canvas id="monthlyChart" height="300"></canvas>',
                '<div class="btn-group btn-group-sm">
                    <button class="btn btn-default active" data-period="month">Month</button>
                    <button class="btn btn-default" data-period="week">Week</button>
                    <button class="btn btn-default" data-period="day">Day</button>
                </div>'
            );
            ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Pending Tasks -->
        <div class="col-lg-8">
            <?php
            echo createCard(
                '<i class="fas fa-tasks mr-2"></i>Pending Tasks',
                '<div class="table-responsive">
                    <table class="table table-striped" id="pending-tasks-table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Review test results for PAT001</td>
                                <td><span class="badge badge-danger">High</span></td>
                                <td>Today</td>
                                <td><span class="badge badge-warning">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Equipment maintenance - Analyzer #1</td>
                                <td><span class="badge badge-warning">Medium</span></td>
                                <td>Tomorrow</td>
                                <td><span class="badge badge-info">Scheduled</span></td>
                                <td>
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-tools"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Update patient records</td>
                                <td><span class="badge badge-info">Low</span></td>
                                <td>Next Week</td>
                                <td><span class="badge badge-secondary">Assigned</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>',
                '',
                '<a href="tasks.php" class="btn btn-sm btn-default">View All Tasks</a>'
            );
            ?>
        </div>
        
        <!-- System Status -->
        <div class="col-lg-4">
            <?php
            echo createCard(
                '<i class="fas fa-server mr-2"></i>System Status',
                '<div class="row">
                    <div class="col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-success">
                                <i class="fas fa-check"></i> Online
                            </span>
                            <h5 class="description-header">Database</h5>
                            <span class="description-text">CONNECTION</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-percentage text-success">
                                <i class="fas fa-check"></i> 100%
                            </span>
                            <h5 class="description-header">System</h5>
                            <span class="description-text">UPTIME</span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                        <span class="text-sm text-muted">CPU Usage: 15%</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: 62%"></div>
                        </div>
                        <span class="text-sm text-muted">Memory Usage: 62%</span>
                    </div>
                </div>',
                '<button class="btn btn-sm btn-info" onclick="checkSystemStatus()">
                    <i class="fas fa-sync"></i> Check Status
                </button>'
            );
            ?>
        </div>
    </div>
    
    <script>
    // Initialize dashboard
    $(document).ready(function() {
        initializeChart();
        
        // Initialize DataTable for pending tasks
        $('#pending-tasks-table').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false
        });
        
        // Refresh data every 30 seconds
        setInterval(function() {
            refreshDashboardData();
        }, 30000);
    });
    
    // Initialize monthly chart
    function initializeChart() {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Tests Completed',
                    data: [65, 59, 80, 81, 56, 85],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'New Patients',
                    data: [28, 48, 40, 19, 86, 27],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
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
                }
            }
        });
    }
    
    // Refresh activity
    function refreshActivity() {
        showLoading('#recent-activity');
        // Simulate API call
        setTimeout(function() {
            showSuccess('Activity refreshed');
            // hideLoading('#recent-activity');
        }, 1000);
    }
    
    // Check system status
    function checkSystemStatus() {
        showInfo('Checking system status...');
        // API call would go here
    }
    
    // Refresh dashboard data
    function refreshDashboardData() {
        // This would typically make AJAX calls to update counters
        console.log('Dashboard data refreshed');
    }
    
    // Global refresh function for auto-refresh
    window.refreshData = function() {
        refreshDashboardData();
        refreshActivity();
    };
    </script>
    
    <?php
}

// Render the dashboard page
renderTemplate('dashboard', 'dashboardContent');
?>
