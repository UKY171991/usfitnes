<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Get date range filter
$date_range = $_GET['date_range'] ?? 'today';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

// Set date range based on filter
switch($date_range) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = $custom_start;
        $end_date = $custom_end;
        break;
    default: // today
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
}

// Get statistics with error handling
try {
    // Basic statistics
    $stats = [
        // Fix: match branches table structure (status is TINYINT, 1=active)
        'branches' => $conn->query("SELECT COUNT(*) FROM branches WHERE status = 1")->fetchColumn() ?? 0,
        'users' => $conn->query("SELECT COUNT(*) FROM users WHERE status = 1")->fetchColumn() ?? 0,
        'patients' => $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn() ?? 0,
        'tests' => $conn->query("SELECT COUNT(*) FROM tests WHERE status = 1")->fetchColumn() ?? 0,
        'reports' => $conn->query("SELECT COUNT(*) FROM reports")->fetchColumn() ?? 0,
        'revenue' => $conn->query("SELECT COALESCE(SUM(paid_amount), 0) as total FROM payments")->fetchColumn() ?? 0
    ];

    // Period specific statistics
    // Prepare end date for range comparison (exclusive)
    $end_date_exclusive = date('Y-m-d', strtotime($end_date . ' +1 day'));

    $period_stmt_new_patients = $conn->prepare("SELECT COUNT(*) FROM patients WHERE created_at >= ? AND created_at < ?");
    $period_stmt_new_patients->execute([$start_date, $end_date_exclusive]);
    $period_stats['new_patients'] = $period_stmt_new_patients->fetchColumn() ?? 0;

    // Apply similar logic to other period stats for consistency and potential performance benefit
    $period_stmt_completed = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = 'completed' AND created_at >= ? AND created_at < ?");
    $period_stmt_completed->execute([$start_date, $end_date_exclusive]);
    $period_stats['completed_reports'] = $period_stmt_completed->fetchColumn() ?? 0;

    $period_stmt_pending = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = 'pending' AND created_at >= ? AND created_at < ?");
    $period_stmt_pending->execute([$start_date, $end_date_exclusive]);
    $period_stats['pending_reports'] = $period_stmt_pending->fetchColumn() ?? 0;

    $period_stmt_revenue = $conn->prepare("SELECT COALESCE(SUM(paid_amount), 0) FROM payments WHERE created_at >= ? AND created_at < ?");
    $period_stmt_revenue->execute([$start_date, $end_date_exclusive]);
    $period_stats['period_revenue'] = $period_stmt_revenue->fetchColumn() ?? 0;

    // Branch statistics
    $branch_stmt = $conn->query("
        SELECT 
            b.branch_name,
            COUNT(DISTINCT p.id) as patient_count,
            COUNT(DISTINCT r.id) as report_count,
            COALESCE(SUM(py.paid_amount), 0) as revenue
        FROM branches b
        LEFT JOIN patients p ON b.id = p.branch_id
        LEFT JOIN reports r ON p.id = r.patient_id
        LEFT JOIN payments py ON r.id = py.report_id
        WHERE b.status = 'active'
        GROUP BY b.id, b.branch_name
        ORDER BY revenue DESC
        LIMIT 5
    ");
    $branch_stats = $branch_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Test category statistics
    $category_stmt = $conn->query("
        SELECT 
            c.category_name,
            COUNT(DISTINCT t.id) as test_count,
            COUNT(DISTINCT r.id) as report_count
        FROM test_categories c
        LEFT JOIN tests t ON c.id = t.category_id AND t.status = 1
        LEFT JOIN reports r ON t.id = r.test_id
        WHERE c.status = 'active'
        GROUP BY c.id, c.category_name
        ORDER BY report_count DESC
        LIMIT 5
    ");
    $category_stats = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent payments
    $payment_stmt = $conn->prepare("
        SELECT 
            p.id,
            p.paid_amount,
            p.payment_method,
            p.created_at,
            pt.name as patient_name,
            t.test_name,
            b.branch_name
        FROM payments p
        LEFT JOIN reports r ON p.report_id = r.id
        LEFT JOIN patients pt ON r.patient_id = pt.id
        LEFT JOIN tests t ON r.test_id = t.id
        LEFT JOIN branches b ON pt.branch_id = b.id
        WHERE DATE(p.created_at) BETWEEN ? AND ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $payment_stmt->execute([$start_date, $end_date]);
    $recent_payments = $payment_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activities
    $activity_stmt = $conn->prepare("
        SELECT a.*, u.name as user_name 
        FROM activities a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.created_at) BETWEEN ? AND ?
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $activity_stmt->execute([$start_date, $end_date]);
    $activities = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $stats = [
        'branches' => 0,
        'users' => 0,
        'patients' => 0,
        'tests' => 0,
        'reports' => 0,
        'revenue' => 0
    ];
    $period_stats = [
        'new_patients' => 0,
        'completed_reports' => 0,
        'pending_reports' => 0,
        'period_revenue' => 0
    ];
    $branch_stats = [];
    $category_stats = [];
    $recent_payments = [];
    $activities = [];
}

include '../inc/header.php';
?>

<style>
/* Dashboard UI improvements */
.card-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-height: 110px;
    transition: box-shadow 0.2s;
}
.card-stats:hover {
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
}
.card-stats .icon {
    font-size: 2.5rem;
    opacity: 0.7;
}
.table thead th {
    background: #f8f9fa;
}
.table tbody tr:hover {
    background: #f1f3f5;
}
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <form class="row g-2 align-items-center" method="GET">
        <div class="col-auto">
            <label for="date_range" class="form-label mb-0">Date Range:</label>
            <select class="form-select" name="date_range" id="date_range">
                <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $date_range === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="month" <?php echo $date_range === 'month' ? 'selected' : ''; ?>>This Month</option>
                <option value="custom" <?php echo $date_range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
            </select>
        </div>
        <div class="col-auto date-inputs" style="display: none;">
            <label for="start_date" class="form-label mb-0">From:</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($custom_start); ?>">
        </div>
        <div class="col-auto date-inputs" style="display: none;">
            <label for="end_date" class="form-label mb-0">To:</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($custom_end); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>
</div>

<!-- Period Statistics -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card border-primary card-stats">
            <span class="icon text-primary"><i class="bi bi-person-plus"></i></span>
            <div>
                <h6 class="card-subtitle mb-2 text-muted">New Patients</h6>
                <h2 class="card-title" title="New patients in period"><?php echo number_format($period_stats['new_patients']); ?></h2>
                <p class="card-text text-muted">In selected period</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success card-stats">
            <span class="icon text-success"><i class="bi bi-file-earmark-check"></i></span>
            <div>
                <h6 class="card-subtitle mb-2 text-muted">Completed Reports</h6>
                <h2 class="card-title" title="Completed reports in period"><?php echo number_format($period_stats['completed_reports']); ?></h2>
                <p class="card-text text-muted">In selected period</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning card-stats">
            <span class="icon text-warning"><i class="bi bi-hourglass-split"></i></span>
            <div>
                <h6 class="card-subtitle mb-2 text-muted">Pending Reports</h6>
                <h2 class="card-title" title="Pending reports in period"><?php echo number_format($period_stats['pending_reports']); ?></h2>
                <p class="card-text text-muted">In selected period</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info card-stats">
            <span class="icon text-info"><i class="bi bi-currency-rupee"></i></span>
            <div>
                <h6 class="card-subtitle mb-2 text-muted">Revenue</h6>
                <h2 class="card-title" title="Revenue in period">₹<?php echo number_format($period_stats['period_revenue'], 2); ?></h2>
                <p class="card-text text-muted">In selected period</p>
            </div>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div class="row g-3">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-diagram-3"></i></span>
            <div>
                <h5 class="card-title">Total Branches</h5>
                <div class="display-4" title="Total branches (all, not just active)">
                    <?php 
                    // Show total branches (all, not just active)
                    $total_branches = $conn->query("SELECT COUNT(*) FROM branches")->fetchColumn() ?? 0;
                    echo number_format($total_branches);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-people"></i></span>
            <div>
                <h5 class="card-title">Active Users <small class="text-white-50">(not deleted)</small></h5>
                <div class="display-4" title="Active users">
                    <?php echo isset($stats['users']) ? number_format($stats['users']) : '0'; ?>
                </div>
                <a href="users.php" class="btn btn-light btn-sm mt-2">View All Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-person"></i></span>
            <div>
                <h5 class="card-title">Total Patients</h5>
                <div class="display-4" title="Total patients">
                    <?php echo isset($stats['patients']) ? number_format($stats['patients']) : '0'; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-clipboard-data"></i></span>
            <div>
                <h5 class="card-title">Available Tests</h5>
                <div class="display-4" title="Available tests"><?php echo isset($stats['tests']) ? number_format($stats['tests']) : '0'; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-danger text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-file-earmark-text"></i></span>
            <div>
                <h5 class="card-title">Total Reports</h5>
                <div class="display-4" title="Total reports"><?php echo isset($stats['reports']) ? number_format($stats['reports']) : '0'; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-secondary text-white h-100 card-stats">
            <span class="icon"><i class="bi bi-cash-coin"></i></span>
            <div>
                <h5 class="card-title">Total Revenue</h5>
                <div class="display-4" title="Total revenue">₹<?php echo isset($stats['revenue']) ? number_format($stats['revenue'], 2) : '0.00'; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Branch Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Performing Branches</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Patients</th>
                                <th>Reports</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($branch_stats as $branch): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($branch['branch_name']); ?></td>
                                    <td><?php echo number_format($branch['patient_count']); ?></td>
                                    <td><?php echo number_format($branch['report_count']); ?></td>
                                    <td>₹<?php echo number_format($branch['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Category Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Test Categories Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Tests</th>
                                <th>Reports</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($category_stats as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                    <td><?php echo number_format($category['test_count']); ?></td>
                                    <td><?php echo number_format($category['report_count']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Payments -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Payments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_payments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent payments</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['test_name']); ?></td>
                                        <td>₹<?php echo number_format($payment['paid_amount'], 2); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>User</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($activities)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No recent activities</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($activities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['user_name'] ?? 'Unknown User'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRange = document.getElementById('date_range');
    const dateInputs = document.querySelectorAll('.date-inputs');
    
    function toggleDateInputs() {
        const isCustom = dateRange.value === 'custom';
        dateInputs.forEach(input => {
            input.style.display = isCustom ? 'block' : 'none';
        });
    }
    
    dateRange.addEventListener('change', toggleDateInputs);
    toggleDateInputs();
});
</script>

<?php include '../inc/footer.php'; ?>