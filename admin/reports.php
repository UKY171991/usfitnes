<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Get filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$branch_id = $_GET['branch_id'] ?? '';
$status_filter = $_GET['status'] ?? ''; // New status filter
$search_term = $_GET['search'] ?? '';   // New search term

// Build base query
$query = "
    SELECT 
        r.*,
        p.name as patient_name,
        b.branch_name,
        t.test_name,
        t.price as test_price,
        COALESCE((
            SELECT SUM(py.paid_amount)
            FROM payments py
            WHERE py.patient_id = r.patient_id
            AND py.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
        ), 0) as total_amount
    FROM reports r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN branches b ON p.branch_id = b.id
    LEFT JOIN tests t ON r.test_id = t.id
    WHERE 1=1 
"; // Start with 1=1 to easily append conditions

$params = [];

// Apply date range filter
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND DATE(r.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

// Apply branch filter
if(!empty($branch_id)) {
    $query .= " AND p.branch_id = ?";
    $params[] = $branch_id;
}

// Apply status filter
if(!empty($status_filter)) {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
}

// Apply search term filter
if (!empty($search_term)) {
    $query .= " AND (p.name LIKE ? OR r.id LIKE ? OR t.test_name LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for filter dropdown
$branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics (based on the filtered reports)
$total_reports = count($reports);
$total_amount = array_sum(array_column($reports, 'total_amount'));
$pending_reports = count(array_filter($reports, function($report) {
    return $report['status'] == 'pending';
}));
$completed_reports = count(array_filter($reports, function($report) {
    return $report['status'] == 'completed';
}));

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $branch_id == $b['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"> <!-- Status Filter -->
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
             <div class="col-md-2"> <!-- Search -->
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Patient, ID, Test..." value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="col-md-12 mt-3 text-end">
                <button type="submit" class="btn btn-primary btn-sm">Filter Reports</button>
                <a href="reports.php" class="btn btn-secondary btn-sm">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Reports</h5>
                <p class="card-text fs-4 fw-bold"><?php echo number_format($total_reports); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Amount</h5>
                <p class="card-text fs-4 fw-bold">₹<?php echo number_format($total_amount, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-dark bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending Reports</h5>
                <p class="card-text fs-4 fw-bold"><?php echo number_format($pending_reports); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Completed Reports</h5>
                <p class="card-text fs-4 fw-bold"><?php echo number_format($completed_reports); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Test Name</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                         <tr>
                             <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <p>No reports found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($reports as $report): ?>
                            <tr>
                                <td><?php echo $report['id']; ?></td>
                                <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['test_name'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($report['branch_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                                <td>₹<?php echo number_format($report['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <!-- VIEW BUTTON -->
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewReport(<?php echo $report['id']; ?>)" title="View Report">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <!-- PRINT BUTTON -->
                                        <!-- <button type="button" class="btn btn-sm btn-secondary" onclick="printReport(<?php echo $report['id']; ?>)" title="Print Report">
                                            <i class="fas fa-print"></i>
                                        </button> -->
                                        <!-- DOWNLOAD BUTTON -->
                                        <!-- <a href="download-report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary" title="Download Report" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                     <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Report Modal (Ensure this HTML exists correctly) -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">View Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" 
                        onclick="printReport(document.getElementById('viewReportModal').dataset.reportId)" 
                        id="modalPrintButton">Print</button> 
            </div>
        </div>
    </div>
</div>

<script>
// Make sure functions are globally available
let viewReportModalInstance = null;

// Function to view report
window.viewReport = function(reportId) {
    console.log("viewReport called with ID:", reportId);
    const modalElement = document.getElementById('viewReportModal');
    if (!viewReportModalInstance) {
        console.error('View Report Modal instance not initialized!');
        return;
    }
    // Store the report ID on the modal element itself for the print button to access
    modalElement.dataset.reportId = reportId; 
    
    const reportContentEl = document.getElementById('reportContent');
    if (!reportContentEl) {
        console.error('Element with ID reportContent not found!');
        return;
    }

    // Show loading state
    reportContentEl.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    viewReportModalInstance.show();

    // Fetch report details
    fetch('ajax/get-report.php?id=' + reportId)
        .then(response => {
            if (!response.ok) {
                 throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
         })
        .then(data => {
            console.log("AJAX response received:", data);
            if (data.success && data.report) {
                reportContentEl.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Patient Details</h6>
                            <p>
                                <strong>Name:</strong> ${data.report.patient_name || 'N/A'}<br>
                                <strong>Test:</strong> ${data.report.test_name || 'N/A'}<br>
                                <strong>Date:</strong> ${data.report.created_at || 'N/A'}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Report Details</h6>
                            <p>
                                <strong>Status:</strong> <span class="badge bg-${data.report.status === 'completed' ? 'success' : 'warning'}">${data.report.status || 'N/A'}</span><br>
                                <strong>Result:</strong> ${data.report.result || 'Not available'}<br>
                                <strong>Amount:</strong> ₹${data.report.total_amount || '0.00'}
                            </p>
                        </div>
                    </div>
                `;
            } else {
                reportContentEl.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load report details'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error fetching report:', error);
            reportContentEl.innerHTML = `<div class="alert alert-danger">An error occurred: ${error.message}. Please check console.</div>`;
        });
};

// Function to print report (opens new window)
window.printReport = function(reportId) {
    console.log("printReport called with ID:", reportId);
    if (!reportId) {
        console.error("printReport called with invalid ID:", reportId);
        alert("Cannot print report: Invalid ID provided.");
        return;
    }
    const printUrl = `print-report.php?id=${reportId}`;
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes');
    if (printWindow) {
        printWindow.focus();
    } else {
        alert('Please allow popups for this website to print the report.');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded and parsed");
    
    const modalElement = document.getElementById('viewReportModal');
    if (modalElement) {
        viewReportModalInstance = new bootstrap.Modal(modalElement);
        console.log("View Report Modal Initialized");
    } else {
        console.error("View Report Modal element not found!");
    }
    
    // Add any other initializations here
});
</script>

<?php include '../inc/footer.php'; ?>