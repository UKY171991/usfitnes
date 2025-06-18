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
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#searchInput {
    transition: all 0.3s ease;
}

#searchInput:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #80bdff;
}

#clearSearch {
    border-left: 0;
}

.input-group .btn {
    z-index: 2;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>

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
<div class="dashboard-cards-row mb-4">
    <?php
    $report_stats = [
        [
            'label' => 'Total Reports',
            'value' => number_format($total_reports),
            'icon' => 'bi-file-earmark-text',
            'border' => 'border-primary',
        ],
        [
            'label' => 'Total Amount',
            'value' => '₹' . number_format($total_amount, 2),
            'icon' => 'bi-cash-coin',
            'border' => 'border-success',
        ],
        [
            'label' => 'Pending Reports',
            'value' => number_format($pending_reports),
            'icon' => 'bi-hourglass-split',
            'border' => 'border-warning',
        ],
        [
            'label' => 'Completed Reports',
            'value' => number_format($completed_reports),
            'icon' => 'bi-check-circle',
            'border' => 'border-info',
        ],
    ];
    foreach ($report_stats as $meta) {
        ?>
        <div class="dashboard-card <?php echo $meta['border']; ?>">
            <div>
                <div class="card-value"><?php echo $meta['value']; ?></div>
                <div class="card-label"><?php echo $meta['label']; ?></div>
            </div>
            <span class="card-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
        </div>
    <?php } ?>
</div>

<!-- Reports Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Reports List</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search reports...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Test Name</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="reports-table-body">
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
                        <?php $sr_no = 1; // Initialize serial number ?>
                        <?php foreach($reports as $report): ?>
                            <tr>
                                <td><?php echo $sr_no++; ?></td>
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
                     <?php endif; ?>                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination-controls">
                <!-- Pagination controls will be inserted here by JavaScript -->
            </ul>
        </nav>
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
let currentPage = 1;
const itemsPerPage = 10;
let searchTerm = '';
let searchTimeout = null;

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal
    const modalElement = document.getElementById('viewReportModal');
    if (modalElement) {
        viewReportModalInstance = new bootstrap.Modal(modalElement);
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchTerm = this.value.trim();
                currentPage = 1; // Reset to first page when searching
                fetchReports(currentPage);
            }, 300); // Debounce search by 300ms
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchTerm = '';
                currentPage = 1;
                fetchReports(currentPage);
            }
        });
    }

    // Load initial data
    fetchReports(currentPage);
});

function fetchReports(page) {
    const searchParam = searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '';
    fetch(`ajax/get_reports.php?page=${page}&itemsPerPage=${itemsPerPage}${searchParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderTable(data.reports, (page - 1) * itemsPerPage);
                renderPagination(data.totalPages, parseInt(data.currentPage));
                currentPage = parseInt(data.currentPage);
            } else {
                console.error('Error fetching reports:', data.message);
                document.getElementById('reports-table-body').innerHTML = `<tr><td colspan="8" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading reports: ${data.message}</p></div></td></tr>`;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            document.getElementById('reports-table-body').innerHTML = `<tr><td colspan="8" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Could not connect to server.</p></div></td></tr>`;
        });
}

function renderTable(reports, offset) {
    const tbody = document.getElementById('reports-table-body');
    tbody.innerHTML = '';
    if (reports.length === 0) {
        const message = searchTerm ? 
            `<div class="text-muted"><i class="fas fa-search fa-2x mb-2"></i><p>No reports found matching "${searchTerm}"</p><p class="small">Try adjusting your search terms</p></div>` :
            '<div class="text-muted"><i class="fas fa-file-alt fa-2x mb-2"></i><p>No reports found</p></div>';
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4">${message}</td></tr>`;
        return;
    }

    reports.forEach((report, index) => {
        const sr_no = offset + index + 1;
        const statusBadge = report.status === 'completed' ? 
            '<span class="badge bg-success">Completed</span>' : 
            '<span class="badge bg-warning">Pending</span>';
        
        // Use highlighting for search results
        const patientName = highlightSearchTerm(report.patient_name, searchTerm);
        const testName = highlightSearchTerm(report.test_name, searchTerm);
        const branchName = highlightSearchTerm(report.branch_name, searchTerm);
        const amount = highlightSearchTerm(report.amount_formatted, searchTerm);
        
        const row = `
            <tr>
                <td>${sr_no}</td>
                <td>${patientName}</td>
                <td>${testName}</td>
                <td>${branchName || '-'}</td>
                <td>${report.created_formatted}</td>
                <td>${amount}</td>
                <td>${statusBadge}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-info" onclick="viewReport(${report.id})" title="View Report">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="print-report.php?id=${report.id}" class="btn btn-outline-primary" title="Print Report" target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function renderPagination(totalPages, currentPage) {
    const paginationControls = document.getElementById('pagination-controls');
    if (!paginationControls) return;
    
    paginationControls.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    const prevA = document.createElement('a');
    prevA.className = 'page-link';
    prevA.href = '#';
    prevA.textContent = 'Previous';
    prevA.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) fetchReports(currentPage - 1);
    });
    prevLi.appendChild(prevA);
    paginationControls.appendChild(prevLi);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            fetchReports(i);
        });
        li.appendChild(a);
        paginationControls.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    const nextA = document.createElement('a');
    nextA.className = 'page-link';
    nextA.href = '#';
    nextA.textContent = 'Next';
    nextA.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage < totalPages) fetchReports(currentPage + 1);
    });
    nextLi.appendChild(nextA);
    paginationControls.appendChild(nextLi);
}

function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return str.toString().replace(/[&<>\"'`]/g, function (match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '`': '&#x60;'
        }[match];
    });
}

function highlightSearchTerm(text, searchTerm) {
    if (!searchTerm || searchTerm.length < 2) return escapeHTML(text);
    
    const escapedText = escapeHTML(text);
    const escapedSearchTerm = escapeHTML(searchTerm);
    const regex = new RegExp(`(${escapedSearchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escapedText.replace(regex, '<span class="search-highlight">$1</span>');
}

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