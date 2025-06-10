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
<link rel="stylesheet" href="admin-shared.css">

<div class="dashboard-cards-row">
<?php
$overall_queries = [
    [
        'label' => 'Total Branches uuu',
        'query' => "SELECT COUNT(*) FROM branches",
        'icon' => 'bi-diagram-3',
        'border' => 'border-primary',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'branches.php',
        'format' => 'number',
    ],
    [
        'label' => 'Test Categories',
        'query' => "SELECT COUNT(*) FROM test_categories",
        'icon' => 'bi-tags',
        'border' => 'border-dark',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'test-categories.php',
        'format' => 'number',
    ],
    [
        'label' => 'Active Users',
        'query' => "SELECT COUNT(*) FROM users WHERE status = 1",
        'icon' => 'bi-people',
        'border' => 'border-success',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'users.php',
        'format' => 'number',
    ],
    [
        'label' => 'Total Patients',
        'query' => "SELECT COUNT(*) FROM patients",
        'icon' => 'bi-person',
        'border' => 'border-info',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'patients.php',
        'format' => 'number',
    ],
    [
        'label' => 'Available Tests',
        'query' => "SELECT COUNT(*) FROM tests WHERE status = 1",
        'icon' => 'bi-clipboard-data',
        'border' => 'border-warning',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'test-master.php',
        'format' => 'number',
    ],
    [
        'label' => 'Total Reports',
        'query' => "SELECT COUNT(*) FROM reports",
        'icon' => 'bi-file-earmark-text',
        'border' => 'border-danger',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => 'reports.php',
        'format' => 'number',
    ],
    [
        'label' => 'Total Revenue',
        'query' => "SELECT COALESCE(SUM(paid_amount), 0) FROM payments",
        'icon' => 'bi-cash-coin',
        'border' => 'border-secondary',
        'footer' => 'More info <i class=\"bi bi-arrow-right-circle\"></i>',
        'footer_link' => '#',
        'format' => 'currency',
    ],
];
foreach ($overall_queries as $meta) {
    $stmt = $conn->query($meta['query']);
    $value = $stmt->fetchColumn() ?? 0;
    if ($meta['format'] === 'currency') {
        $value = '₹' . number_format($value, 2);
    } else {
        $value = number_format($value);
    }
    ?>
    <div class="dashboard-card <?php echo $meta['border']; ?>">
        <div>
            <div class="card-value"><?php echo $value; ?></div>
            <div class="card-label"><?php echo $meta['label']; ?></div>
            <a class="card-footer d-block" href="<?php echo $meta['footer_link']; ?>"><?php echo $meta['footer']; ?></a>
        </div>
        <span class="card-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
    </div>
<?php } ?>
</div>

<div class="row mb-4 g-3">
<?php
$period_queries = [
    'New Patients' => [
        'query' => "SELECT COUNT(*) FROM patients WHERE created_at >= ? AND created_at < ?",
        'icon' => 'bi-person-plus',
        'border' => 'primary',
        'text' => 'primary',
        'format' => 'number',
    ],
    'Completed Reports' => [
        'query' => "SELECT COUNT(*) FROM reports WHERE status = 'completed' AND created_at >= ? AND created_at < ?",
        'icon' => 'bi-file-earmark-check',
        'border' => 'success',
        'text' => 'success',
        'format' => 'number',
    ],
    'Pending Reports' => [
        'query' => "SELECT COUNT(*) FROM reports WHERE status = 'pending' AND created_at >= ? AND created_at < ?",
        'icon' => 'bi-hourglass-split',
        'border' => 'warning',
        'text' => 'warning',
        'format' => 'number',
    ],
    'Revenue' => [
        'query' => "SELECT COALESCE(SUM(paid_amount), 0) FROM payments WHERE created_at >= ? AND created_at < ?",
        'icon' => 'bi-currency-rupee',
        'border' => 'info',
        'text' => 'info',
        'format' => 'currency',
    ],
];
$end_date_exclusive = date('Y-m-d', strtotime($end_date . ' +1 day'));
foreach ($period_queries as $title => $meta) {
    $stmt = $conn->prepare($meta['query']);
    $stmt->execute([$start_date, $end_date_exclusive]);
    $value = $stmt->fetchColumn() ?? 0;
    if ($meta['format'] === 'currency') {
        $value = '₹' . number_format($value, 2);
    } else {
        $value = number_format($value);
    }
    ?>
    <div class="col-md-3">
        <div class="card border-<?php echo $meta['border']; ?> card-stats">
            <span class="icon text-<?php echo $meta['text']; ?>"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
            <div>
                <h6 class="card-subtitle mb-2 text-muted"><?php echo $title; ?></h6>
                <h2 class="card-title" title="<?php echo $title; ?> in period"><?php echo $value; ?></h2>
                <p class="card-text text-muted">In selected period</p>
            </div>
        </div>
    </div>
<?php } ?>
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

<!-- Small Quick Stats Cards Row -->
<div class="dashboard-cards-row-small">
<?php
$quick_stats = [
    [
        'label' => 'Active Branches',
        'value' => $stats['branches'],
        'icon' => 'bi-diagram-3',
        'border' => 'border-primary',
    ],
    [
        'label' => 'Active Users',
        'value' => $stats['users'],
        'icon' => 'bi-people',
        'border' => 'border-success',
    ],
    [
        'label' => "Today's Revenue",
        'value' => '₹' . number_format($period_stats['period_revenue'], 2),
        'icon' => 'bi-cash-coin',
        'border' => 'border-info',
    ],
    [
        'label' => 'Pending Reports',
        'value' => $period_stats['pending_reports'],
        'icon' => 'bi-hourglass-split',
        'border' => 'border-warning',
    ],
];
foreach ($quick_stats as $meta) {
    ?>
    <div class="dashboard-card-small <?php echo $meta['border']; ?>">
        <div>
            <div class="card-value"><?php echo $meta['value']; ?></div>
            <div class="card-label"><?php echo $meta['label']; ?></div>
        </div>
        <span class="card-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
    </div>
<?php } ?>
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
