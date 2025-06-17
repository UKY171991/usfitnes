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
                    // Try to get text for more detailed error, then throw
                    return response.text().then(text => {
                        console.error('Server response (text):', text);
                        throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 500)}`);
                    });
                }
                return response.json().catch(jsonError => {
                    console.error('Failed to parse JSON:', jsonError);
                    // If JSON parsing fails, try to get original text response
                    return response.text().then(text => {
                        console.error('Server response (text) that failed JSON parsing:', text);
                        throw new Error('Failed to parse JSON response from server. Body: ' + text.substring(0,500));
                    });
                });
            })
            .then(data => {
                if (!data) {
                    showError('Received no data or invalid data structure from server.');
                    console.error('Received no data or invalid data structure:', data);
                    return; 
                }
                if (data.success) {
                    updateDashboard(data);
                } else {
                    showError(data.message || 'An error occurred while loading dashboard data.');
                    console.error('Server returned success=false:', data.message);
                }
            }).catch(error => {
                console.error('Error loading dashboard data:', error);
                const errorMessage = (error && error.message) ? String(error.message) : 'Unknown error during fetch';
                showError(`Dashboard data loading failed: ${errorMessage}`);
                
                // Clear loading spinners in tables in case of error
                document.querySelectorAll('tbody').forEach(tbody => {
                    const spinner = tbody.querySelector('.spinner-border');
                    if (spinner) {
                        const colspan = spinner.closest('td')?.colSpan || 6; // Default colspan
                        tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">Failed to load data. Please try refreshing.</td></tr>`;
                    }
                });
            })
            .finally(() => {
                console.log('Finally block executing: Attempting to hide loading indicators.');
                const loadingOverlay = document.getElementById('dashboard-loading');
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                    console.log('Main dashboard loading overlay hidden.');
                } else {
                    console.error('Main dashboard loading overlay (dashboard-loading) not found in finally block!');
                }
                
                const filterSpinner = document.getElementById('filter-spinner');
                if (filterSpinner) {
                    filterSpinner.classList.add('d-none');
                    console.log('Filter button spinner hidden.');
                } else {
                    console.error('Filter button spinner (filter-spinner) not found in finally block!');
                }
            });
    }

    // Function to update dashboard with received data
    function updateDashboard(data) {
        console.log('Updating dashboard with data:', data); // Log received data
        // Update branch info
        if (data.branch && data.branch.branch_name) {
            const branchNameElement = document.querySelector('p.text-muted > span#branch-name-loading') 
                                   || document.querySelector('p.text-muted'); // Handle both initial and updated states
            if (branchNameElement) {
                branchNameElement.textContent = data.branch.branch_name;
                if(branchNameElement.id === 'branch-name-loading') branchNameElement.id = 'branch-name-display'; // Optional: change id after loading
            }
        } else {
            const branchNameElement = document.querySelector('p.text-muted > span#branch-name-loading') 
                                   || document.querySelector('p.text-muted');
            if (branchNameElement) {
                branchNameElement.innerHTML = '<span class="text-danger">Branch information not available.</span>';
            }
        }

        // Update period stats
        const periodStats = data.period_stats || {};
        const newPatientsEl = document.getElementById('new-patients-count');
        if (newPatientsEl) newPatientsEl.innerHTML = periodStats.new_patients ?? '0';
        
        const completedReportsEl = document.getElementById('completed-reports-count');
        if (completedReportsEl) completedReportsEl.innerHTML = periodStats.completed_reports ?? '0';
        
        const pendingReportsEl = document.getElementById('pending-reports-count');
        if (pendingReportsEl) pendingReportsEl.innerHTML = periodStats.pending_reports ?? '0';
        
        const periodRevenueEl = document.getElementById('period-revenue-count');
        if (periodRevenueEl) periodRevenueEl.innerHTML = `Rs ${parseFloat(periodStats.period_revenue ?? 0).toFixed(2)}`;

        // Update overall stats
        const stats = data.stats || {};
        const totalPatientsEl = document.getElementById('total-patients-count');
        if (totalPatientsEl) totalPatientsEl.innerHTML = stats.total_patients ?? '0';

        const totalReportsEl = document.getElementById('total-reports-count');
        if (totalReportsEl) totalReportsEl.innerHTML = stats.total_reports ?? '0';

        const availableTestsEl = document.getElementById('available-tests-count');
        if (availableTestsEl) availableTestsEl.innerHTML = stats.available_tests ?? '0';

        const totalRevenueEl = document.getElementById('total-revenue-count');
        if (totalRevenueEl) totalRevenueEl.innerHTML = `Rs ${parseFloat(stats.total_revenue ?? 0).toFixed(2)}`;

        // Update popular tests table
        const popularTestsTbody = document.getElementById('popular-tests-tbody');
        if (popularTestsTbody) {
            if (data.popular_tests && data.popular_tests.length > 0) {
                popularTestsTbody.innerHTML = data.popular_tests.map(test => `
                    <tr>
                        <td>${escapeHtml(test.test_name)}</td>
                        <td>${test.report_count}</td>
                        <td>Rs ${parseFloat(test.revenue).toFixed(2)}</td>
                    </tr>
                `).join('');
            } else {
                popularTestsTbody.innerHTML = '<tr><td colspan="3" class="text-center">No popular tests data available.</td></tr>';
            }
        }

        // Update recent reports table
        const recentReportsTbody = document.getElementById('recent-reports-tbody');
        if (recentReportsTbody) {
            if (data.recent_reports && data.recent_reports.length > 0) {
                recentReportsTbody.innerHTML = data.recent_reports.map(report => `
                    <tr>
                        <td>${escapeHtml(report.patient_name)}</td>
                        <td>${escapeHtml(report.test_name)}</td>
                        <td><span class="badge bg-${getReportStatusClass(report.status)}">${escapeHtml(report.status)}</span></td>
                        <td>${new Date(report.created_at).toLocaleDateString()}</td>
                    </tr>
                `).join('');
            } else {
                recentReportsTbody.innerHTML = '<tr><td colspan="4" class="text-center">No recent reports available.</td></tr>';
            }
        }
        
        // Update recent payments table
        const recentPaymentsTbody = document.getElementById('recent-payments-tbody');
        if (recentPaymentsTbody) {
            if (data.recent_payments && data.recent_payments.length > 0) {
                recentPaymentsTbody.innerHTML = data.recent_payments.map(payment => `
                    <tr>
                        <td>${escapeHtml(payment.invoice_no)}</td>
                        <td>${escapeHtml(payment.patient_name)}</td>
                        <td>${escapeHtml(payment.test_name)}</td>
                        <td>Rs ${parseFloat(payment.paid_amount).toFixed(2)}</td>
                        <td>${escapeHtml(payment.payment_method || payment.payment_mode)}</td>
                        <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                    </tr>
                `).join('');
            } else {
                recentPaymentsTbody.innerHTML = '<tr><td colspan="6" class="text-center">No recent payments available.</td></tr>';
            }
        }

        // Update recent test results table
        const recentResultsTbody = document.getElementById('recent-results-tbody');
        if (recentResultsTbody) {
            if (data.recent_results && data.recent_results.length > 0) {
                recentResultsTbody.innerHTML = data.recent_results.map(result => `
                    <tr>
                        <td>${escapeHtml(result.id)}</td>
                        <td>${escapeHtml(result.patient_name)}</td>
                        <td>${escapeHtml(result.test_name)}</td>
                        <td>${escapeHtml(result.result) || 'N/A'}</td>
                        <td>${new Date(result.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-info view-report-btn" data-report-id="${result.id}">View</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                recentResultsTbody.innerHTML = '<tr><td colspan="6" class="text-center">No recent test results available.</td></tr>';
            }
        }
        console.log('Dashboard update complete.');
    }

    // Helper function to escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return String(unsafe)
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Helper function to get bootstrap class based on report status
    function getReportStatusClass(status) {
        switch (status ? status.toLowerCase() : '') {
            case 'completed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }

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

    // Function to view report details
    function viewReport(reportId) {
        const reportContent = document.getElementById('viewReportContent');
        const printViewedReportBtn = document.getElementById('printViewedReport');
        reportContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading report...</span></div></div>';
        
        // Store report ID for printing
        printViewedReportBtn.dataset.reportId = reportId;

        fetch(`ajax/get-report.php?report_id=${reportId}`)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! Status: ${response.status}, Body: ${text}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.html) {
                    reportContent.innerHTML = data.html;
                } else {
                    reportContent.innerHTML = `<div class="alert alert-danger">${data.message || 'Could not load report details.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching report:', error);
                reportContent.innerHTML = `<div class="alert alert-danger">Error loading report: ${error.message}</div>`;
            });
    }

    // Event delegation for view report buttons (if they are added dynamically)
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('view-report-btn')) {
            const reportId = event.target.dataset.reportId;
            if (reportId) {
                const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
                viewReport(reportId);
                viewReportModal.show();
            }
        }
    });

    document.getElementById('printViewedReport')?.addEventListener('click', function() {
        const reportId = this.dataset.reportId;
        if (reportId) {
            // Option 1: Open print dialog for the modal content (might not be perfectly formatted)
            // const reportContent = document.getElementById('viewReportContent').innerHTML;
            // const printWindow = window.open('', '_blank');
            // printWindow.document.write('<html><head><title>Print Report</title>');
            // // Add any necessary stylesheets for printing if available
            // // printWindow.document.write('<link rel="stylesheet" href="path/to/print.css">');
            // printWindow.document.write('</head><body>');
            // printWindow.document.write(reportContent);
            // printWindow.document.write('</body></html>');
            // printWindow.document.close();
            // printWindow.print();

            // Option 2: Redirect to a dedicated print page (better for formatting)
            window.open(`print-report.php?id=${reportId}`, '_blank');
        }
    });

    // Auto-refresh dashboard every 60 seconds
    setInterval(loadDashboardData, 60000);
    
    // Add debug button if in development mode
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const scriptElem = document.createElement('script');
        scriptElem.src = 'fix_dashboard.js';
        document.body.appendChild(scriptElem);
    }
});
</script>

<?php include '../inc/footer.php'; ?>
