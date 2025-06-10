<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

// Helper functions for fetching statistics
function getBranchTotalPatients($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchTotalReports($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchTotalRevenue($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchAvailableTests($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM branch_tests WHERE branch_id = ? AND status = 1");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchNewPatientsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ?");
        $stmt->execute([$branch_id]);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchCompletedReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'completed'");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'completed' AND DATE(r.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchPendingReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'pending'");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'pending' AND DATE(r.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchRevenueForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND DATE(py.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}
// End of helper functions

$branch_id = $_SESSION['branch_id'];

// Get date range filter
$date_range = $_GET['date_range'] ?? 'today';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

$is_all_time_filter = ($date_range === 'all');

// Set date range based on filter
if ($is_all_time_filter) {
    $start_date = null; // Will be effectively ignored by helper functions when $is_all_time_filter is true
    $end_date = null;   // Will be effectively ignored by helper functions when $is_all_time_filter is true
} else {
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
            if (empty($custom_start) || empty($custom_end) || strtotime($custom_start) > strtotime($custom_end)) {
                // Default to today if custom dates are invalid or not fully provided
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
                // Optionally, set an error message to display to the user if dates are invalid
            } else {
                $start_date = $custom_start;
                $end_date = $custom_end;
            }
            break;
        case 'today':
        default: // today or any other unexpected value
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
    }
}

try {
    // Get branch details
    $branch_stmt = $conn->prepare("SELECT * FROM branches WHERE id = ?");
    $branch_stmt->execute([$branch_id]);
    $branch = $branch_stmt->fetch(PDO::FETCH_ASSOC);

    // Basic statistics for the branch (using helper functions)
    $stats = [
        'total_patients' => getBranchTotalPatients($conn, $branch_id),
        'total_reports' => getBranchTotalReports($conn, $branch_id),
        'total_revenue' => getBranchTotalRevenue($conn, $branch_id),
        'available_tests' => getBranchAvailableTests($conn, $branch_id)
    ];

    // Period specific statistics (using helper functions)
    // Note: The original logic for $new_patients based on $date_range === 'all' is now inside getBranchNewPatientsForPeriod
    $period_stats = [
        'new_patients' => getBranchNewPatientsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'completed_reports' => getBranchCompletedReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'pending_reports' => getBranchPendingReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'period_revenue' => getBranchRevenueForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter)
    ];

    // Popular tests in this branch
    $popular_tests_stmt = $conn->prepare("
        SELECT 
            t.test_name,
            COUNT(r.id) as report_count,
            COALESCE(SUM(py.paid_amount), 0) as revenue
        FROM tests t
        JOIN branch_tests bt ON t.id = bt.test_id
        LEFT JOIN reports r ON t.id = r.test_id
        LEFT JOIN payments py ON r.id = py.report_id
        LEFT JOIN patients p ON r.patient_id = p.id
        WHERE bt.branch_id = ? AND p.branch_id = ?
        GROUP BY t.id, t.test_name
        ORDER BY report_count DESC
        LIMIT 5
    ");
    $popular_tests_stmt->execute([$branch_id, $branch_id]);
    $popular_tests = $popular_tests_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent payments
    $sql_recent_payments = "
        SELECT 
            py.id,
            py.paid_amount,
            py.payment_method,
            py.created_at,
            p.name as patient_name,
            t.test_name
        FROM payments py
        JOIN reports r ON py.report_id = r.id
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        WHERE p.branch_id = ?";
    $params_recent_payments = [$branch_id];

    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_recent_payments .= " AND DATE(py.created_at) BETWEEN ? AND ?";
        $params_recent_payments[] = $start_date;
        $params_recent_payments[] = $end_date;
    }
    $sql_recent_payments .= " ORDER BY py.created_at DESC LIMIT 5";
    $payment_stmt = $conn->prepare($sql_recent_payments);
    $payment_stmt->execute($params_recent_payments);
    $recent_payments = $payment_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent reports
    $sql_recent_reports = "
        SELECT 
            r.id,
            r.status,
            r.created_at,
            p.name as patient_name,
            t.test_name
        FROM reports r
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        WHERE p.branch_id = ?";
    $params_recent_reports = [$branch_id];

    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_recent_reports .= " AND DATE(r.created_at) BETWEEN ? AND ?";
        $params_recent_reports[] = $start_date;
        $params_recent_reports[] = $end_date;
    }
    $sql_recent_reports .= " ORDER BY r.created_at DESC LIMIT 5";
    $report_stmt = $conn->prepare($sql_recent_reports);
    $report_stmt->execute($params_recent_reports);
    $recent_reports = $report_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Branch Dashboard Error: " . $e->getMessage());
    $stats = [
        'total_patients' => 0,
        'total_reports' => 0,
        'total_revenue' => 0,
        'available_tests' => 0
    ];
    $period_stats = [
        'new_patients' => 0,
        'completed_reports' => 0,
        'pending_reports' => 0,
        'period_revenue' => 0
    ];
    $popular_tests = [];
    $recent_payments = [];
    $recent_reports = [];
    $branch = [];
}

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Branch Dashboard</h1>
        <p class="text-muted">
            <?php
            if (isset($branch['name'])) {
                echo htmlspecialchars($branch['name']);
            } else {
                echo '<span class="text-danger">Branch not found (ID: ' . htmlspecialchars($branch_id) . ')</span>';
            }
            ?>
        </p>
    </div>
    <form class="row g-3 align-items-center" method="GET">
        <div class="col-auto">
            <select class="form-select" name="date_range" id="date_range">
                <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $date_range === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="month" <?php echo $date_range === 'month' ? 'selected' : ''; ?>>This Month</option>
                <option value="custom" <?php echo $date_range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                <option value="all" <?php echo $date_range === 'all' ? 'selected' : ''; ?>>All Time</option>
            </select>
        </div>
        <div class="col-auto date-inputs" style="display: none;">
            <input type="date" class="form-control" name="start_date" value="<?php echo $custom_start; ?>">
        </div>
        <div class="col-auto date-inputs" style="display: none;">
            <input type="date" class="form-control" name="end_date" value="<?php echo $custom_end; ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="patients.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> New Patient
                    </a>
                    <a href="reports.php" class="btn btn-success">
                        <i class="fas fa-file-medical"></i> New Report
                    </a>
                    <a href="tests.php" class="btn btn-info">
                        <i class="fas fa-flask"></i> Manage Tests
                    </a>
                    <a href="payments.php" class="btn btn-warning">
                        <i class="fas fa-money-bill"></i> Record Payment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-user-plus fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">New Patients</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($period_stats['new_patients']); ?></h2>
                        <p class="card-text text-muted small">In selected period</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Completed Reports</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($period_stats['completed_reports']); ?></h2>
                        <p class="card-text text-muted small">In selected period</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Pending Reports</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($period_stats['pending_reports']); ?></h2>
                        <p class="card-text text-muted small">In selected period</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-rupee-sign fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Revenue</h6>
                        <h2 class="card-title mb-0">₹<?php echo number_format($period_stats['period_revenue'], 2); ?></h2>
                        <p class="card-text text-muted small">In selected period</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Patients</h5>
                <div class="display-4"><?php echo number_format($stats['total_patients']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Reports</h5>
                <div class="display-4"><?php echo number_format($stats['total_reports']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Available Tests</h5>
                <div class="display-4"><?php echo number_format($stats['available_tests']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <div class="display-4">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Popular Tests -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Popular Tests</h5>
                <a href="tests.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Reports</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($popular_tests)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No tests found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($popular_tests as $test): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                        <td><?php echo number_format($test['report_count']); ?></td>
                                        <td>₹<?php echo number_format($test['revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Reports</h5>
                <a href="reports.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_reports)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent reports</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_reports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($report['test_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $report['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($report['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($report['created_at'])); ?></td>
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

<div class="row">
    <!-- Recent Payments -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Payments</h5>
                <a href="payments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_payments)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent payments</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['test_name']); ?></td>
                                        <td>₹<?php echo number_format($payment['paid_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
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
</div>

<!-- Recent Reports Section -->
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Test Results</h5>
                <a href="reports.php?status=completed" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Patient</th>
                            <th>Test</th>
                            <th>Result</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get recent completed reports for this branch
                        $stmt = $conn->prepare("
                            SELECT 
                                r.id, 
                                r.result, 
                                r.created_at,
                                p.name as patient_name,
                                t.test_name
                            FROM reports r
                            JOIN patients p ON r.patient_id = p.id
                            JOIN tests t ON r.test_id = t.id
                            WHERE p.branch_id = :branch_id 
                            AND r.status = 'completed'
                            ORDER BY r.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->bindParam(':branch_id', $branch_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $recentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($recentResults) > 0) {
                            foreach ($recentResults as $result) {
                                echo "<tr>";
                                echo "<td>#" . htmlspecialchars($result['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($result['patient_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($result['test_name']) . "</td>";
                                echo "<td>" . (strlen($result['result']) > 30 ? htmlspecialchars(substr($result['result'], 0, 30)) . "..." : htmlspecialchars($result['result'])) . "</td>";
                                echo "<td>" . date('d M Y', strtotime($result['created_at'])) . "</td>";
                                echo "<td>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<a href='javascript:void(0)' class='btn btn-info view-report' data-report-id='" . $result['id'] . "' title='View'><i class='fas fa-eye'></i></a>";
                                echo "<a href='javascript:void(0)' class='btn btn-secondary print-report' data-report-id='" . $result['id'] . "' title='Print'><i class='fas fa-print'></i></a>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No completed reports found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewReportContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printViewedReport">Print Report</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Dashboard Functions -->
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

    // For "View Report" buttons
    document.querySelectorAll('.view-report').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            viewReport(reportId);
        });
    });

    // For "Print Report" buttons
    document.querySelectorAll('.print-report').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            printReport(reportId);
        });
    });

    // Print button in view modal
    document.getElementById('printViewedReport').addEventListener('click', function() {
        const reportId = this.getAttribute('data-report-id');
        printReport(reportId);
    });

    // Function to view report details
    function viewReport(reportId) {
        fetch(`ajax/get-report.php?id=${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const report = data.report;
                const viewContent = document.getElementById('viewReportContent');
                document.getElementById('printViewedReport').setAttribute('data-report-id', reportId);
                
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Patient Information</h5>
                            <p><strong>Name:</strong> ${escapeHtml(report.patient_name)}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Test Information</h5>
                            <p><strong>Test:</strong> ${escapeHtml(report.test_name)}</p>
                            <p><strong>Normal Range:</strong> ${escapeHtml(report.normal_range || 'N/A')}</p>
                            <p><strong>Unit:</strong> ${escapeHtml(report.unit || 'N/A')}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5>Result</h5>
                            <div class="p-3 bg-light rounded">
                                ${report.result ? escapeHtml(report.result) : '<em>No result yet</em>'}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-${report.status === 'completed' ? 'success' : 'warning'}">
                                    ${escapeHtml(report.status.charAt(0).toUpperCase() + report.status.slice(1))}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Price:</strong> ₹${parseFloat(report.test_price).toFixed(2)}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date:</strong> ${new Date(report.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                `;
                viewContent.innerHTML = html;
                
                const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
                viewReportModal.show();
            } else {
                alert(data.message || 'Report details not found.');
            }
        })
        .catch(error => {
            console.error('Error loading report details:', error);
            alert('Error loading report details. Please try again.');
        });
    }

    // Function to print report
    function printReport(reportId) {
        const printWindow = window.open(`print-report.php?id=${reportId}`, '_blank', 'width=800,height=600');
        if (printWindow) {
            printWindow.focus();
            printWindow.onload = function() {
                printWindow.print();
            };
        } else {
            alert('Please allow popups to print the report');
        }
    }

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '-';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>

<?php include '../inc/footer.php'; ?>