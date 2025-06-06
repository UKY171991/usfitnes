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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm" action="ajax/update-report.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="report_id" id="editReportId">

                    <!-- Patient Name Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="editPatient">Patient Name <span class="text-danger">*</span></label>
                        <select class="form-select" name="patient_id" id="editPatient" required>
                            <option value="">Select Patient</option>
                            <?php
                            $patient_stmt = $conn->prepare("SELECT id, name FROM patients WHERE branch_id = ? ORDER BY name");
                            $patient_stmt->execute([$branch_id]);
                            while ($patient = $patient_stmt->fetch()) {
                                echo "<option value='" . $patient['id'] . "'>" . htmlspecialchars($patient['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a patient.</div>
                    </div>

                    <!-- Test Results Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold">Test Results <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addResultField">
                                <i class="fas fa-plus"></i> Add Parameter
                            </button>
                        </div>
                        <div id="testResultsContainer" class="border rounded p-3 bg-light">
                            <!-- Initial result field will be added by JS or populated -->
                        </div>
                        <small class="text-muted">Add parameters, values, and units. At least one result is required if status is 'Completed'.</small>
                        <div class="invalid-feedback" id="testResultsFeedback"></div>
                    </div>

                    <!-- Additional Result Details Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="editResult">Additional Result Details / Interpretation</label>
                        <textarea class="form-control" name="result" id="editResult" rows="3" placeholder="Enter additional details..."></textarea>
                        <small class="text-muted">Use this for overall comments or results that don't fit the structure above.</small>
                    </div>

                    <!-- Status Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="editStatus">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="editStatus" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="editNotes">Notes</label>
                        <textarea class="form-control" name="notes" id="editNotes" rows="2" placeholder="Enter any additional notes..."></textarea>
                    </div>

                    <!-- Total Price Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Total Price</label>
                        <p id="totalPrice" class="form-control-plaintext">₹0.00</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editReportForm" class="btn btn-primary">
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
        const testResultsContainer = document.getElementById('testResultsContainer');
        const addResultFieldButton = document.getElementById('addResultField');

        let testOptions = '<option value="">Select Test Name</option>';
        let parameterOptionsByTest = {};
        let testPriceMap = {};

        // Fetch test names and parameters dynamically
        fetch('ajax/get-test-parameters.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.tests.forEach(test => {
                        testOptions += `<option value="${test.id}">${test.test_name}</option>`;
                        testPriceMap[test.id] = parseFloat(test.price) || 0;
                    });

                    parameterOptionsByTest = data.parameters;
                } else {
                    console.error('Failed to fetch test names and parameters:', data.message);
                }
            })
            .catch(error => console.error('Error fetching test names and parameters:', error));

        // Function to add a new test result row
        function addTestResultRow(testId = '', parameterName = '', value = '', unit = '') {
            const row = document.createElement('div');
            row.className = 'row mb-2';

            const parameterOptions = testId && parameterOptionsByTest[testId]
                ? parameterOptionsByTest[testId].map(param => `<option value="${param.parameter_name}" ${param.parameter_name === parameterName ? 'selected' : ''}>${param.parameter_name}</option>`).join('')
                : '<option value="">Select Parameter</option>';

            const defaultUnit = testId && parameterOptionsByTest[testId]
                ? (parameterOptionsByTest[testId].find(param => param.parameter_name === parameterName)?.default_unit || '')
                : '';

            const normalValue = testId && parameterOptionsByTest[testId]
                ? (parameterOptionsByTest[testId].find(param => param.parameter_name === parameterName)?.normal_value || '')
                : '';

            row.innerHTML = `
                <div class="col-md-3">
                    <select class="form-select test-select" name="test_names[]">
                        ${testOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select parameter-select" name="parameters[]">
                        ${parameterOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="values[]" placeholder="Value" value="${value || normalValue}">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="units[]" placeholder="Unit" value="${unit || defaultUnit}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-result">&times;</button>
                </div>
            `;

            testResultsContainer.appendChild(row);

            // Add event listener to the test dropdown to update parameters dynamically
            const testSelect = row.querySelector('.test-select');
            const parameterSelect = row.querySelector('.parameter-select');
            const valueInput = row.querySelector('input[name="values[]"]');
            const unitInput = row.querySelector('input[name="units[]"]');

            testSelect.addEventListener('change', function () {
                const selectedTestId = testSelect.value;
                const newParameterOptions = selectedTestId && parameterOptionsByTest[selectedTestId]
                    ? parameterOptionsByTest[selectedTestId].map(param => `<option value="${param.parameter_name}">${param.parameter_name}</option>`).join('')
                    : '<option value="">Select Parameter</option>';

                parameterSelect.innerHTML = newParameterOptions;
                valueInput.value = ''; // Clear value when test changes
                unitInput.value = ''; // Clear unit when test changes
            });

            parameterSelect.addEventListener('change', function () {
                const selectedTestId = testSelect.value;
                const selectedParameter = parameterSelect.value;
                const newDefaultUnit = selectedTestId && parameterOptionsByTest[selectedTestId]
                    ? (parameterOptionsByTest[selectedTestId].find(param => param.parameter_name === selectedParameter)?.default_unit || '')
                    : '';

                const newNormalValue = selectedTestId && parameterOptionsByTest[selectedTestId]
                    ? (parameterOptionsByTest[selectedTestId].find(param => param.parameter_name === selectedParameter)?.normal_value || '')
                    : '';

                valueInput.value = newNormalValue; // Update value based on selected parameter
                unitInput.value = newDefaultUnit; // Update unit based on selected parameter
            });

            // Add event listener to the remove button
            row.querySelector('.remove-result').addEventListener('click', function () {
                row.remove();
                calculateTotalPrice();
            });
        }

        // Add new test result row on button click
        addResultFieldButton.addEventListener('click', function () {
            addTestResultRow();
            calculateTotalPrice();
        });

        // Serialize test results and submit form via AJAX
        document.getElementById('editReportForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);

            // Serialize test results
            const testResults = [];
            document.querySelectorAll('#testResultsContainer .row').forEach(row => {
                const testName = row.querySelector('.test-select').value;
                const parameter = row.querySelector('.parameter-select').value;
                const value = row.querySelector('input[name="values[]"]').value;
                const unit = row.querySelector('input[name="units[]"]').value;

                if (testName && parameter) {
                    testResults.push({ testName, parameter, value, unit });
                }
            });

            formData.append('test_results', JSON.stringify(testResults));

            // Submit the form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Report updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating report:', error);
                alert('An unexpected error occurred.');
            });
        });

        // Handle edit-report button click
        const editReportButtons = document.querySelectorAll('.edit-report');
        if (editReportButtons) {
            editReportButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');

                    // Show spinner while loading
                    const spinner = document.getElementById('editReportSpinner');
                    if (spinner) spinner.classList.remove('d-none');

                    // Fetch report details
                    fetch(`ajax/get-report.php?id=${reportId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (spinner) spinner.classList.add('d-none'); // Hide spinner

                            if (data.success) {
                                const form = document.getElementById('editReportForm');
                                form.report_id.value = data.report.id;
                                form.result.value = data.report.result || '';
                                form.status.value = data.report.status;
                                form.notes.value = data.report.notes || '';

                                // Populate test results
                                const testResultsContainer = document.getElementById('testResultsContainer');
                                if (testResultsContainer) {
                                    testResultsContainer.innerHTML = '';
                                    if (data.report.test_results && Array.isArray(data.report.test_results)) {
                                        data.report.test_results.forEach(result => {
                                            addTestResultRow(result.testName, result.parameter, result.value, result.unit);
                                        });
                                    }
                                }

                                calculateTotalPrice();

                                // Show the modal
                                const editReportModal = new bootstrap.Modal(document.getElementById('editReportModal'));
                                editReportModal.show();
                            } else {
                                alert('Failed to fetch report details.');
                            }
                        })
                        .catch(error => {
                            if (spinner) spinner.classList.add('d-none'); // Hide spinner
                            console.error('Error fetching report details:', error);
                            alert('An error occurred while fetching report details.');
                        });
                });
            });
        }

        // Handle view-report button click
        const viewReportButtons = document.querySelectorAll('.view-report');
        if (viewReportButtons) {
            viewReportButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');

                    // Fetch report details and display in the modal
                    fetch(`ajax/get-report.php?id=${reportId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const viewReportContent = document.getElementById('viewReportContent');
                                viewReportContent.innerHTML = `
                                    <div>
                                        <h5>Report Details</h5>
                                        <p><strong>Patient Name:</strong> ${data.report.patient_name}</p>
                                        <p><strong>Test Name:</strong> ${data.report.test_name}</p>
                                        <p><strong>Status:</strong> ${data.report.status}</p>
                                        <p><strong>Result:</strong> ${data.report.result}</p>
                                        <p><strong>Notes:</strong> ${data.report.notes}</p>
                                        <p><strong>Created At:</strong> ${data.report.created_at}</p>
                                        <p><strong>Updated At:</strong> ${data.report.updated_at}</p>
                                        <h6>Test Results</h6>
                                        <ul>
                                            ${JSON.parse(data.report.test_results).map(result => `
                                                <li>
                                                    <strong>Test Name:</strong> ${result.testName}, 
                                                    <strong>Parameter:</strong> ${result.parameter}, 
                                                    <strong>Value:</strong> ${result.value}, 
                                                    <strong>Unit:</strong> ${result.unit}
                                                </li>
                                            `).join('')}
                                        </ul>
                                    </div>
                                `;

                                const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
                                viewReportModal.show();
                            } else {
                                alert('Failed to fetch report details.');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching report details:', error);
                            alert('An error occurred while fetching report details.');
                        });
                });
            });
        }

        // Handle print-report button click
        const printReportButtons = document.querySelectorAll('.print-report');
        if (printReportButtons) {
            printReportButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');

                    // Redirect to print page
                    window.open(`print-report.php?report_id=${reportId}`, '_blank');
                });
            });
        }

        // Add a price calculation section
        const calculateTotalPrice = () => {
            let totalPrice = 0;
            document.querySelectorAll('#testResultsContainer .row').forEach(row => {
                const testSelect = row.querySelector('.test-select');
                const selectedTestId = testSelect.value;
                if (selectedTestId && testPriceMap[selectedTestId]) {
                    totalPrice += testPriceMap[selectedTestId];
                }
            });
            document.getElementById('totalPrice').textContent = `₹${totalPrice.toFixed(2)}`;
        };

        // Update price calculation when test results change
        addResultFieldButton.addEventListener('click', function () {
            addTestResultRow();
            calculateTotalPrice();
        });

        document.getElementById('testResultsContainer').addEventListener('change', function (event) {
            if (event.target.classList.contains('test-select')) {
                calculateTotalPrice();
            }
        });
    });
</script>
</script>

<?php include '../inc/footer.php'; ?>
