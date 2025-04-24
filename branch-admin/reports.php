<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];

// Constants for report status
define('REPORT_STATUS_PENDING', 'pending');
define('REPORT_STATUS_COMPLETED', 'completed');

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$date_range = $_GET['date_range'] ?? 'all';
$search = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Set date range based on filter
switch($date_range) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

// Build the WHERE clause for filters
$where_conditions = ["bt.branch_id = ?"]; 
$params = [$branch_id];

if ($status_filter !== 'all') {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
}

if ($date_range !== 'all') {
    $where_conditions[] = "DATE(r.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR t.test_name LIKE ? OR r.id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count for pagination
$count_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT r.id)
    FROM reports r 
    JOIN patients p ON r.patient_id = p.id 
    JOIN tests t ON r.test_id = t.id
    JOIN branch_tests bt ON t.id = bt.test_id
    WHERE $where_clause
");

$count_stmt->execute($params);
$total_reports = $count_stmt->fetchColumn();
$total_pages = ceil($total_reports / $per_page);

// Get reports for the branch with pagination and filters
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.created_at,
        r.status,
        r.result,
        p.name as patient_name,
        p.id as patient_id,
        t.test_name,
        t.normal_range,
        t.unit,
        t.price as test_price,
        COALESCE((
            SELECT SUM(py.paid_amount) 
            FROM payments py
            WHERE py.patient_id = r.patient_id 
            AND py.branch_id = :branch_id1
            AND py.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
        ), 0) as paid_amount,
        (t.price - COALESCE((
            SELECT SUM(py2.paid_amount) 
            FROM payments py2
            WHERE py2.patient_id = r.patient_id 
            AND py2.branch_id = :branch_id2
            AND py2.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
        ), 0)) as due_amount
    FROM reports r 
    JOIN patients p ON r.patient_id = p.id 
    JOIN tests t ON r.test_id = t.id
    JOIN branch_tests bt ON t.id = bt.test_id
    WHERE bt.branch_id = :branch_id3
");

// Convert WHERE conditions to use named parameters
$named_params = [];
$named_where_conditions = [];

if ($status_filter !== 'all') {
    $named_where_conditions[] = "r.status = :status";
    $named_params[':status'] = $status_filter;
}

if ($date_range !== 'all') {
    $named_where_conditions[] = "DATE(r.created_at) BETWEEN :start_date AND :end_date";
    $named_params[':start_date'] = $start_date;
    $named_params[':end_date'] = $end_date;
}

if ($search) {
    $named_where_conditions[] = "(p.name LIKE :search1 OR t.test_name LIKE :search2 OR r.id LIKE :search3)";
    $search_param = "%$search%";
    $named_params[':search1'] = $search_param;
    $named_params[':search2'] = $search_param;
    $named_params[':search3'] = $search_param;
}

// Add additional WHERE conditions if any exist
if (!empty($named_where_conditions)) {
    $where_clause = " AND " . implode(" AND ", $named_where_conditions);
} else {
    $where_clause = "";
}

$sql = $stmt->queryString . $where_clause . " ORDER BY r.created_at DESC LIMIT :offset, :limit";
$stmt = $conn->prepare($sql);

// Bind all parameters
$stmt->bindValue(':branch_id1', $branch_id, PDO::PARAM_INT);
$stmt->bindValue(':branch_id2', $branch_id, PDO::PARAM_INT);
$stmt->bindValue(':branch_id3', $branch_id, PDO::PARAM_INT);
foreach ($named_params as $param => $value) {
    $stmt->bindValue($param, $value);
}
$stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);

$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics with corrected payment calculation
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT r.id) as total,
        SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending,
        COALESCE(
            (SELECT SUM(paid_amount) 
            FROM payments 
            WHERE branch_id = ? 
            AND invoice_no IN (
                SELECT CONCAT('INV-', LPAD(r2.id, 6, '0'))
                FROM reports r2
                JOIN branch_tests bt2 ON r2.test_id = bt2.test_id
                WHERE bt2.branch_id = ?
            )
            ), 0
        ) as total_revenue
    FROM reports r 
    JOIN branch_tests bt ON r.test_id = bt.test_id
    WHERE bt.branch_id = ?
");
$stats_stmt->execute([$branch_id, $branch_id, $branch_id]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Test Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReportModal">
            <i class="fas fa-plus"></i> New Report
        </button>
    </div>
</div>

<!-- New Report Modal -->
<div class="modal fade" id="newReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newReportForm" method="POST" action="ajax/save-report.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Patient</label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php
                                $patient_stmt = $conn->prepare("SELECT id, name, phone FROM patients WHERE branch_id = ? ORDER BY name");
                                $patient_stmt->execute([$branch_id]);
                                while ($patient = $patient_stmt->fetch()) {
                                    echo "<option value='" . $patient['id'] . "'>" . htmlspecialchars($patient['name']) . " (" . $patient['phone'] . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Test</label>
                            <select class="form-select" name="test_id" required>
                                <option value="">Select Test</option>
                                <?php
                                $test_stmt = $conn->prepare("
                                    SELECT t.id, t.test_name, t.price, c.category_name 
                                    FROM tests t 
                                    JOIN test_categories c ON t.category_id = c.id
                                    JOIN branch_tests bt ON t.id = bt.test_id
                                    WHERE bt.branch_id = ? 
                                    ORDER BY c.category_name, t.test_name
                                ");
                                $test_stmt->execute([$branch_id]);
                                $current_category = '';
                                while ($test = $test_stmt->fetch()) {
                                    if ($current_category != $test['category_name']) {
                                        if ($current_category != '') echo "</optgroup>";
                                        echo "<optgroup label='" . htmlspecialchars($test['category_name']) . "'>";
                                        $current_category = $test['category_name'];
                                    }
                                    echo "<option value='" . $test['id'] . "' data-price='" . $test['price'] . "'>" 
                                        . htmlspecialchars($test['test_name']) 
                                        . " (₹" . number_format($test['price'], 2) . ")</option>";
                                }
                                if ($current_category != '') echo "</optgroup>";
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Test Price</label>
                            <input type="number" class="form-control" name="price" id="testPrice" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Amount</label>
                            <input type="number" class="form-control" name="paid_amount" required min="0" step="0.01">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="netbanking">Net Banking</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" name="transaction_id">
                            <small class="text-muted">Required for Card/UPI/Net Banking payments</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Due Amount</label>
                            <input type="number" class="form-control" id="dueAmount" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="newReportForm" class="btn btn-primary">Create Report</button>
            </div>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewReportContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editReportSpinner" class="text-center d-none mb-3">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <form id="editReportForm" action="ajax/update-report.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="report_id" id="editReportId">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold">Test Results <span class="text-danger" id="resultsRequiredIndicator" style="display: none;">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addResultField">
                                <i class="fas fa-plus"></i> Add Parameter
                            </button>
                        </div>
                        <div id="testResultsContainer">
                            <!-- Initial result field will be added by JS or populated -->
                        </div>
                        <small class="text-muted">Add parameters, values, and units. At least one result is required if status is 'Completed'.</small>
                        <div class="invalid-feedback" id="testResultsFeedback"></div> 
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label" for="editResult">Additional Result Details / Interpretation</label>
                        <textarea class="form-control" name="result" id="editResult" rows="3"></textarea>
                        <small class="text-muted">Use this for overall comments or results that don't fit the structure above.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editStatus">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="editStatus" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editNotes">Notes</label>
                        <textarea class="form-control" name="notes" id="editNotes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editReportForm" class="btn btn-primary" id="updateReportSubmitBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Update Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-file-medical fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Total Reports</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['total']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Completed</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['completed']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Pending</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['pending']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-rupee-sign fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Total Revenue</h6>
                        <h2 class="card-title mb-0">₹<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date Range</label>
                <select name="date_range" class="form-select" id="dateRange">
                    <option value="all" <?php echo $date_range == 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="today" <?php echo $date_range == 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo $date_range == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $date_range == 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="custom" <?php echo $date_range == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            <div class="col-md-2 custom-date <?php echo $date_range == 'custom' ? '' : 'd-none'; ?>">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-2 custom-date <?php echo $date_range == 'custom' ? '' : 'd-none'; ?>">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Reports Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Patient</th>
                        <th>Test</th>
                        <th>Status</th>
                        <th>Test Price</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($reports)): ?>
                    <tr>
                        <td colspan="10" class="text-center">No reports found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['id']); ?></td>
                            <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['test_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td>₹<?php echo number_format($report['test_price'], 2); ?></td>
                            <td>₹<?php echo number_format($report['paid_amount'], 2); ?></td>
                            <td>₹<?php echo number_format($report['due_amount'], 2); ?></td>
                            <td>
                                <?php if($report['due_amount'] > 0): ?>
                                    <span class="badge bg-danger">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info view-report" 
                                            data-report-id="<?php echo $report['id']; ?>"
                                            title="View Report">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($report['status'] == 'pending'): ?>
                                    <button type="button" class="btn btn-sm btn-primary edit-report"
                                            data-report-id="<?php echo $report['id']; ?>"
                                            title="Edit Report">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if($report['due_amount'] > 0): ?>
                                    <a href="payments.php?report_id=<?php echo $report['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Update Payment">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-secondary print-report"
                                            data-report-id="<?php echo $report['id']; ?>"
                                            title="Print Report">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date_range=<?php echo $date_range; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date_range=<?php echo $date_range; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date_range=<?php echo $date_range; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reportTableBody = document.querySelector('.table tbody');

        if (reportTableBody) {
            reportTableBody.addEventListener('click', function (event) {
                const targetButton = event.target.closest('button');

                if (!targetButton) return; // Exit if click wasn't on a button

                const reportId = targetButton.dataset.reportId;
                if (!reportId) return; // Exit if button doesn't have reportId

                if (targetButton.classList.contains('view-report')) {
                    viewReport(reportId);
                } else if (targetButton.classList.contains('edit-report')) {
                    editReport(reportId);
                } else if (targetButton.classList.contains('print-report')) {
                    printReport(reportId);
                }
            });
        }

        function viewReport(reportId) {
            fetch(`ajax/get-report.php?id=${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const viewContent = document.getElementById('viewReportContent');
                        const report = data.report;

                        // Render report details in a structured format
                        viewContent.innerHTML = `
                            <div>
                                <h5>Report Details</h5>
                                <p><strong>Report ID:</strong> ${report.id}</p>
                                <p><strong>Patient Name:</strong> ${report.patient_name}</p>
                                <p><strong>Test Name:</strong> ${report.test_name}</p>
                                <p><strong>Status:</strong> ${report.status}</p>
                                <p><strong>Test Price:</strong> ₹${parseFloat(report.test_price).toFixed(2)}</p>
                                <p><strong>Paid Amount:</strong> ₹${parseFloat(report.paid_amount).toFixed(2)}</p>
                                <p><strong>Due Amount:</strong> ₹${parseFloat(report.due_amount).toFixed(2)}</p>
                                <p><strong>Notes:</strong> ${report.notes || 'N/A'}</p>
                                <p><strong>Created At:</strong> ${report.created_at}</p>
                            </div>
                        `;

                        const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
                        viewReportModal.show();
                    } else {
                        alert('Failed to fetch report details.');
                    }
                })
                .catch(error => console.error('Error fetching report:', error));
        }

        function editReport(reportId) {
            fetch(`ajax/get-report.php?id=${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const editForm = document.getElementById('editReportForm');
                        editForm.report_id.value = data.report.id;
                        editForm.result.value = data.report.result || '';
                        editForm.status.value = data.report.status;
                        editForm.notes.value = data.report.notes || '';

                        // Display test name
                        const testNameElement = document.getElementById('testName');
                        if (testNameElement) {
                            testNameElement.textContent = data.report.test_name || 'N/A';
                        }

                        // Populate test results if available
                        if (Array.isArray(data.report.test_results)) {
                            populateTestResults(data.report.test_results);
                        } else {
                            populateTestResults([]); // Clear existing rows if no test results
                        }

                        const editReportModal = new bootstrap.Modal(document.getElementById('editReportModal'));
                        editReportModal.show();
                    } else {
                        alert('Failed to fetch report details for editing.');
                    }
                })
                .catch(error => console.error('Error fetching report for editing:', error));
        }

        function printReport(reportId) {
            window.open(`print-report.php?report_id=${reportId}`, '_blank');
        }

        const editReportModalEl = document.getElementById('editReportModal');
        const testResultsContainer = document.getElementById('testResultsContainer');
        const addResultFieldButton = document.getElementById('addResultField');

        // Function to add a new test result row
        function addTestResultRow(testName = '', parameter = '', value = '', unit = '') {
            const row = document.createElement('div');
            row.className = 'row mb-2';

            row.innerHTML = `
                <div class="col-md-3">
                    <select class="form-select" name="test_names[]">
                        <option value="">Select Test Name</option>
                        <option value="Test1" ${testName === 'Test1' ? 'selected' : ''}>Test1</option>
                        <option value="Test2" ${testName === 'Test2' ? 'selected' : ''}>Test2</option>
                        <!-- Add more test names as needed -->
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="parameters[]">
                        <option value="">Select Parameter</option>
                        <option value="Parameter1" ${parameter === 'Parameter1' ? 'selected' : ''}>Parameter1</option>
                        <option value="Parameter2" ${parameter === 'Parameter2' ? 'selected' : ''}>Parameter2</option>
                        <!-- Add more parameters as needed -->
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="values[]" placeholder="Value" value="${value}">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="units[]" placeholder="Unit" value="${unit}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-result">&times;</button>
                </div>
            `;

            testResultsContainer.appendChild(row);

            // Add event listener to the remove button
            row.querySelector('.remove-result').addEventListener('click', function () {
                row.remove();
            });
        }

        // Add new test result row on button click
        addResultFieldButton.addEventListener('click', function () {
            addTestResultRow();
        });

        // Populate test results when editing a report
        function populateTestResults(results) {
            testResultsContainer.innerHTML = ''; // Clear existing rows
            results.forEach(result => {
                addTestResultRow(result.test_name, result.parameter, result.value, result.unit);
            });
        }
    });
</script>

<?php include '../inc/footer.php'; ?>
