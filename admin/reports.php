<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Get date range for filtering
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get branch filter
$branch_id = $_GET['branch_id'] ?? '';

// Build query
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
    WHERE r.created_at BETWEEN ? AND ?
";
$params = [$start_date, $end_date];

if(!empty($branch_id)) {
    $query .= " AND p.branch_id = ?";
    $params[] = $branch_id;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for filter
$branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $branch_id == $b['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Reports</h5>
                <h2 class="display-4"><?php echo number_format($total_reports); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Amount</h5>
                <h2 class="display-4">₹<?php echo number_format($total_amount, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Reports</h5>
                <h2 class="display-4"><?php echo number_format($pending_reports); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Completed Reports</h5>
                <h2 class="display-4"><?php echo number_format($completed_reports); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $report): ?>
                <tr>
                    <td><?php echo $report['id']; ?></td>
                    <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($report['branch_name']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                    <td>₹<?php echo number_format($report['total_amount'], 2); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewReport(<?php echo $report['id']; ?>)" title="View Report">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="printReport(<?php echo $report['id']; ?>)" title="Print Report">
                                <i class="fas fa-print"></i>
                            </button>
                            <a href="download-report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary" title="Download Report">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printCurrentReport()">Print</button>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
    
    // Global function to view report
    window.viewReport = function(reportId) {
        // Show loading state
        document.getElementById('reportContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        // Show the modal
        viewReportModal.show();
        
        // Fetch report details
        fetch('ajax/get-report.php?id=' + reportId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reportContent').innerHTML = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Patient Details</h6>
                                <p>
                                    <strong>Name:</strong> ${data.report.patient_name}<br>
                                    <strong>Test:</strong> ${data.report.test_name}<br>
                                    <strong>Date:</strong> ${data.report.created_at}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Report Details</h6>
                                <p>
                                    <strong>Status:</strong> <span class="badge bg-${data.report.status === 'completed' ? 'success' : 'warning'}">${data.report.status}</span><br>
                                    <strong>Result:</strong> ${data.report.result || 'Not available'}<br>
                                    <strong>Amount:</strong> ₹${data.report.total_amount}
                                </p>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('reportContent').innerHTML = `
                        <div class="alert alert-danger">
                            ${data.message || 'Failed to load report details'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportContent').innerHTML = `
                    <div class="alert alert-danger">
                        An error occurred while loading the report. Please try again.
                    </div>
                `;
            });
    };

    // Global function to print report
    window.printReport = function(reportId) {
        const printWindow = window.open('print-report.php?id=' + reportId, '_blank', 'width=800,height=600');
        printWindow.focus();
    };

    // Global function to print current report from modal
    window.printCurrentReport = function() {
        const content = document.getElementById('reportContent').innerHTML;
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Report</title>
                <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        body { padding: 20px; }
                    }
                </style>
            </head>
            <body>
                ${content}
                <script>
                    window.onload = function() {
                        window.print();
                        window.onfocus = function() { window.close(); }
                    }
                </script>
            </body>
            </html>
        `);
        printWindow.document.close();
    };
});
</script>

<?php include '../inc/footer.php'; ?> 