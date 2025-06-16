<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

// Get branch ID from session
$branch_id = $_SESSION['branch_id'];

// Get date range filter (will be used for initial page load and passed to AJAX)
$date_range = $_GET['date_range'] ?? 'today';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

// Initialize empty variables for template (will be filled via AJAX)
$branch = [];
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
$recent_results = [];

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>        <div class="d-flex align-items-center">
            <h1 class="h2">Branch Dashboard</h1>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="refresh-dashboard" title="Refresh Dashboard">
                <i class="fas fa-sync"></i>
            </button>
        </div>
        <p class="text-muted">
            <?php
            if (isset($branch['branch_name'])) {
                echo htmlspecialchars($branch['branch_name']);
            } else {
                echo '<span id="branch-name-loading">Loading branch information...</span>';
            }
            ?>
        </p>
    </div>
    <form class="row g-3 align-items-center" method="GET" id="filter-form">
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
            <button type="submit" class="btn btn-primary" id="apply-filter-btn">
                <span id="filter-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                Apply
            </button>
        </div>
    </form>
</div>

<!-- Error messages container -->
<div id="error-container"></div>

<!-- Dashboard loading overlay -->
<div id="dashboard-loading" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 1050; display: none;">
    <div class="text-center">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5>Loading Dashboard Data...</h5>
    </div>
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
                        <h6 class="card-subtitle mb-2 text-muted">New Patients</h6>                        <h2 class="card-title mb-0" id="new-patients-count">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </h2>
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
                        <h2 class="card-title mb-0" id="completed-reports-count">
                            <div class="spinner-border spinner-border-sm text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </h2>
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
                        <h2 class="card-title mb-0" id="pending-reports-count">
                            <div class="spinner-border spinner-border-sm text-warning" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </h2>
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
                        <h2 class="card-title mb-0" id="period-revenue-count">
                            <div class="spinner-border spinner-border-sm text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </h2>
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
                <div class="display-4" id="total-patients-count">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Reports</h5>
                <div class="display-4" id="total-reports-count">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Available Tests</h5>
                <div class="display-4" id="available-tests-count">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <div class="display-4" id="total-revenue-count">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
                        <tbody id="popular-tests-tbody">
                            <tr>
                                <td colspan="3" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
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
                        <tbody id="recent-reports-tbody">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
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
                        <thead>                    <tr>
                                <th>Invoice</th>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-payments-tbody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
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
                    <tbody id="recent-results-tbody">
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
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
    const filterForm = document.getElementById('filter-form');
    const refreshButton = document.getElementById('refresh-dashboard');
    
    // Initialize dashboard data
    loadDashboardData();
    
    // Add event listener for refresh button
    refreshButton.addEventListener('click', function() {
        refreshButton.disabled = true;
        refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        loadDashboardData().then(() => {
            refreshButton.disabled = false;
            refreshButton.innerHTML = '<i class="fas fa-sync"></i>';
        });
    });

    // Toggle date inputs visibility based on filter selection
    function toggleDateInputs() {
        const isCustom = dateRange.value === 'custom';
        dateInputs.forEach(input => {
            input.style.display = isCustom ? 'block' : 'none';
        });
    }
    
    dateRange.addEventListener('change', toggleDateInputs);
    toggleDateInputs();

    // Handle filter form submission with AJAX
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadDashboardData();
        
        // Update URL with filter params for better UX
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
    });
      // Function to load dashboard data via AJAX
    function loadDashboardData() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // Show loading indicators
        document.getElementById('dashboard-loading').style.display = 'flex';
        document.getElementById('filter-spinner').classList.remove('d-none');
        
        return fetch(`ajax/get_dashboard_data.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateDashboard(data);
                } else {
                    showError(data.message || 'An error occurred while loading dashboard data.');
                }
                return data;
            }).catch(error => {
                console.error('Error loading dashboard data:', error);
                showError(`Dashboard data loading failed: ${error.message || 'Unknown error'}`);
            })
            .finally(() => {
                // Hide loading indicators
                document.getElementById('dashboard-loading').style.display = 'none';
                document.getElementById('filter-spinner').classList.add('d-none');
            });
    }

    // Function to update dashboard with received data
    function updateDashboard(data) {
        // Update branch info
        if (data.branch && data.branch.branch_name) {
            document.querySelector('.text-muted').textContent = data.branch.branch_name;
        } else {
            document.querySelector('.text-muted').innerHTML = '<span class="text-danger">Branch not found</span>';
        }

        // Update period stats
        if (data.period_stats) {
            document.getElementById('new-patients-count').textContent = formatNumber(data.period_stats.new_patients);
            document.getElementById('completed-reports-count').textContent = formatNumber(data.period_stats.completed_reports);
            document.getElementById('pending-reports-count').textContent = formatNumber(data.period_stats.pending_reports);
            document.getElementById('period-revenue-count').textContent = '₹' + formatNumber(data.period_stats.period_revenue, 2);
        }

        // Update overall stats
        if (data.stats) {
            document.getElementById('total-patients-count').textContent = formatNumber(data.stats.total_patients);
            document.getElementById('total-reports-count').textContent = formatNumber(data.stats.total_reports);
            document.getElementById('available-tests-count').textContent = formatNumber(data.stats.available_tests);
            document.getElementById('total-revenue-count').textContent = '₹' + formatNumber(data.stats.total_revenue, 2);
        }

        // Update popular tests
        const popularTestsBody = document.getElementById('popular-tests-tbody');
        popularTestsBody.innerHTML = '';
        
        if (data.popular_tests && data.popular_tests.length > 0) {
            data.popular_tests.forEach(test => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(test.test_name)}</td>
                    <td>${formatNumber(test.report_count)}</td>
                    <td>₹${formatNumber(test.revenue, 2)}</td>
                `;
                popularTestsBody.appendChild(row);
            });
        } else {
            popularTestsBody.innerHTML = '<tr><td colspan="3" class="text-center">No tests found</td></tr>';
        }

        // Update recent reports
        const recentReportsBody = document.getElementById('recent-reports-tbody');
        recentReportsBody.innerHTML = '';
        
        if (data.recent_reports && data.recent_reports.length > 0) {
            data.recent_reports.forEach(report => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(report.patient_name)}</td>
                    <td>${escapeHtml(report.test_name)}</td>
                    <td>
                        <span class="badge bg-${report.status === 'completed' ? 'success' : 'warning'}">
                            ${capitalizeFirstLetter(report.status)}
                        </span>
                    </td>
                    <td>${formatDateTime(report.created_at)}</td>
                `;
                recentReportsBody.appendChild(row);
            });
        } else {
            recentReportsBody.innerHTML = '<tr><td colspan="4" class="text-center">No recent reports</td></tr>';
        }        // Update recent payments
        const recentPaymentsBody = document.getElementById('recent-payments-tbody');
        recentPaymentsBody.innerHTML = '';
          if (data.recent_payments && data.recent_payments.length > 0) {
            data.recent_payments.forEach(payment => {
                const row = document.createElement('tr');
                const paymentDate = payment.payment_date ? formatDate(payment.payment_date) : formatDateTime(payment.created_at);
                row.innerHTML = `
                    <td>
                        <a href="print-payment.php?id=${payment.id}" class="text-primary" target="_blank">
                            ${payment.invoice_no ? escapeHtml(payment.invoice_no) : `#${payment.id}`}
                        </a>
                    </td>
                    <td>${escapeHtml(payment.patient_name)}</td>
                    <td>${escapeHtml(payment.test_name)}</td>
                    <td>
                        ₹${formatNumber(payment.paid_amount, 2)}
                        ${payment.discount > 0 ? `<span class="badge bg-success ms-1">Disc: ₹${formatNumber(payment.discount, 2)}</span>` : ''}
                    </td>
                    <td>${payment.payment_method ? escapeHtml(payment.payment_method) : '-'}</td>
                    <td>${paymentDate}</td>
                `;
                recentPaymentsBody.appendChild(row);
            });
        } else {
            recentPaymentsBody.innerHTML = '<tr><td colspan="6" class="text-center">No recent payments</td></tr>';
        }

        // Update recent test results
        const recentResultsBody = document.getElementById('recent-results-tbody');
        recentResultsBody.innerHTML = '';
        
        if (data.recent_results && data.recent_results.length > 0) {
            data.recent_results.forEach(result => {
                const resultText = result.result ? 
                    (result.result.length > 30 ? escapeHtml(result.result.substring(0, 30)) + '...' : escapeHtml(result.result)) : 
                    '<em>No result yet</em>';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>#${result.id}</td>
                    <td>${escapeHtml(result.patient_name)}</td>
                    <td>${escapeHtml(result.test_name)}</td>
                    <td>${resultText}</td>
                    <td>${formatDate(result.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="javascript:void(0)" class="btn btn-info view-report" data-report-id="${result.id}" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-secondary print-report" data-report-id="${result.id}" title="Print">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>
                `;
                recentResultsBody.appendChild(row);
            });

            // Reattach event listeners for view and print buttons
            attachReportButtonEvents();
        } else {
            recentResultsBody.innerHTML = '<tr><td colspan="6" class="text-center">No completed reports found</td></tr>';
        }
    }

    // Function to attach event listeners to view and print report buttons
    function attachReportButtonEvents() {
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
    }

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
                showError(data.message || 'Report details not found.');
            }
        })
        .catch(error => {
            console.error('Error loading report details:', error);
            showError('Error loading report details. Please try again.');
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
            showError('Please allow popups to print the report');
        }
    }

    // Function to show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert at the error container
        const errorContainer = document.getElementById('error-container');
        errorContainer.appendChild(errorDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
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

    // Helper function to format numbers
    function formatNumber(number, decimals = 0) {
        return Number(number).toLocaleString('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Helper function to format date and time
    function formatDateTime(dateTimeStr) {
        if (!dateTimeStr) return '-';
        const date = new Date(dateTimeStr);
        return date.toLocaleString('en-IN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Helper function to format date only
    function formatDate(dateTimeStr) {
        if (!dateTimeStr) return '-';
        const date = new Date(dateTimeStr);
        return date.toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Helper function to capitalize first letter
    function capitalizeFirstLetter(string) {
        if (!string) return '-';
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Auto-refresh dashboard every 60 seconds
    setInterval(loadDashboardData, 60000);
});
</script>

<?php include '../inc/footer.php'; ?>
