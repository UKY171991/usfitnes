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
<link rel="stylesheet" href="branch-dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<div class="container-fluid mt-4">
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
    <div id="error-container" class="mb-3"></div>

    <!-- Dashboard loading overlay -->
    <div id="dashboard-loading" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center d-none" style="z-index: 1050;">
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
            <div class="content-card quick-actions-card">
                <div class="card-body d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    <a href="patients.php" class="btn btn-primary"><i class="bi bi-person-plus-fill me-1"></i> New Patient</a>
                    <a href="reports.php" class="btn btn-success"><i class="bi bi-file-earmark-medical-fill me-1"></i> New Report</a>
                    <a href="tests.php" class="btn btn-info text-white"><i class="bi bi-eyedropper me-1"></i> Manage Tests</a>
                    <a href="payments.php" class="btn btn-warning text-dark"><i class="bi bi-cash-coin me-1"></i> Record Payment</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Statistics -->
    <h4 class="mt-4 mb-3">Statistics for Selected Period</h4>
    <div class="dashboard-cards-row" id="periodStatsCardsContainer">
        <?php
        $period_card_data = [
            ['id_base' => 'new-patients', 'label' => 'New Patients', 'icon' => 'bi-person-plus', 'border_class' => 'border-primary'],
            ['id_base' => 'completed-reports', 'label' => 'Completed Reports', 'icon' => 'bi-check2-circle', 'border_class' => 'border-success'],
            ['id_base' => 'pending-reports', 'label' => 'Pending Reports', 'icon' => 'bi-hourglass-split', 'border_class' => 'border-warning'],
            ['id_base' => 'period-revenue', 'label' => 'Revenue', 'icon' => 'bi-currency-rupee', 'border_class' => 'border-info'],
        ];
        foreach ($period_card_data as $card) {
        ?>
        <div class="dashboard-card <?php echo $card['border_class']; ?>" id="<?php echo $card['id_base']; ?>-card">
            <div class="card-content">
                <div class="card-text-content">
                    <div class="card-value" id="<?php echo $card['id_base']; ?>-count">
                        <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                    <div class="card-label"><?php echo $card['label']; ?></div>
                </div>
                <span class="card-icon"><i class="bi <?php echo $card['icon']; ?>"></i></span>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Overall Statistics -->
    <h4 class="mt-5 mb-3">Overall Branch Statistics</h4>
    <div class="dashboard-cards-row" id="overallStatsCardsContainer">
        <?php
        $overall_card_data = [
            ['id_base' => 'total-patients', 'label' => 'Total Patients', 'icon' => 'bi-people-fill', 'border_class' => 'border-primary', 'footer_link' => 'patients.php', 'footer_text' => 'View Patients'],
            ['id_base' => 'total-reports', 'label' => 'Total Reports', 'icon' => 'bi-journal-text', 'border_class' => 'border-success', 'footer_link' => 'reports.php', 'footer_text' => 'View Reports'],
            ['id_base' => 'available-tests', 'label' => 'Available Tests', 'icon' => 'bi-flask', 'border_class' => 'border-warning', 'footer_link' => 'tests.php', 'footer_text' => 'Manage Tests'],
            ['id_base' => 'total-revenue', 'label' => 'Total Revenue', 'icon' => 'bi-wallet2', 'border_class' => 'border-info', 'footer_link' => 'payments.php', 'footer_text' => 'View Payments'],
        ];
        foreach ($overall_card_data as $card) {
        ?>
        <div class="dashboard-card <?php echo $card['border_class']; ?>" id="<?php echo $card['id_base']; ?>-card">
            <div class="card-content">
                <div class="card-text-content">
                    <div class="card-value" id="<?php echo $card['id_base']; ?>-count">
                        <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                    <div class="card-label"><?php echo $card['label']; ?></div>
                </div>
                <span class="card-icon"><i class="bi <?php echo $card['icon']; ?>"></i></span>
            </div>
            <?php if (isset($card['footer_link']) && isset($card['footer_text'])): ?>
            <a class="card-footer" href="<?php echo $card['footer_link']; ?>">
                <?php echo $card['footer_text']; ?> <i class="bi bi-arrow-right-circle ms-1"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php } ?>
    </div>


    <div class="row mt-4">
        <!-- Popular Tests -->
        <div class="col-md-6 mb-4">
            <div class="content-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Popular Tests</h5>
                    <a href="tests.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0"> <!-- Adjusted padding for table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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
            <div class="content-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Reports</h5>
                    <a href="reports.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0"> <!-- Adjusted padding for table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Payments</h5>
                    <a href="payments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0"> <!-- Adjusted padding for table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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
        <div class="content-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Test Results</h5>
                    <a href="reports.php?status=completed" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0"> <!-- Adjusted padding for table -->
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
        const dateRangeSelect = document.getElementById('date_range');
        const dateInputs = document.querySelectorAll('.date-inputs');
        const filterForm = document.getElementById('filter-form');
        const refreshButton = document.getElementById('refresh-dashboard');
        const dashboardLoadingOverlay = document.getElementById('dashboard-loading');
        const filterSpinner = document.getElementById('filter-spinner');
        const errorContainer = document.getElementById('error-container');

        const statElements = {
            newPatients: document.getElementById('new-patients-count'),
            completedReports: document.getElementById('completed-reports-count'),
            pendingReports: document.getElementById('pending-reports-count'),
            periodRevenue: document.getElementById('period-revenue-count'),
            totalPatients: document.getElementById('total-patients-count'),
            totalReports: document.getElementById('total-reports-count'),
            availableTests: document.getElementById('available-tests-count'),
            totalRevenue: document.getElementById('total-revenue-count')
        };

        const tableBodyElements = {
            popularTests: document.getElementById('popular-tests-tbody'),
            recentReports: document.getElementById('recent-reports-tbody'),
            recentPayments: document.getElementById('recent-payments-tbody'),
            recentResults: document.getElementById('recent-results-tbody')
        };

        function escapeHtml(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            return String(unsafe)
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        function getReportStatusClass(status) {
            switch (status ? status.toLowerCase() : '') {
                case 'completed': return 'success';
                case 'pending': return 'warning';
                case 'cancelled': return 'danger';
                default: return 'secondary';
            }
        }

        function showGlobalError(message) {
            if (errorContainer) {
                errorContainer.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${escapeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            }
            console.error("Global error displayed:", message);
        }

        function setElementText(element, text, isCurrency = false) {
            if (element) {
                let displayText = text;
                if (text === null || typeof text === 'undefined') {
                    displayText = '<span class="text-muted small">N/A</span>';
                } else if (isCurrency) {
                    displayText = `Rs ${parseFloat(text).toFixed(2)}`;
                }
                element.innerHTML = displayText;
            }
        }
        
        function setTableError(tbodyElement, colspan, message = "Failed to load data.") {
            if (tbodyElement) {
                tbodyElement.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">${escapeHtml(message)}</td></tr>`;
            }
        }

        function clearAllSpinnersAndSetErrorState(errorMessage) {
            console.warn("Clearing all spinners and setting error state:", errorMessage);
            showGlobalError(errorMessage);

            setElementText(statElements.newPatients, null);
            setElementText(statElements.completedReports, null);
            setElementText(statElements.pendingReports, null);
            setElementText(statElements.periodRevenue, null);
            setElementText(statElements.totalPatients, null);
            setElementText(statElements.totalReports, null);
            setElementText(statElements.availableTests, null);
            setElementText(statElements.totalRevenue, null);

            setTableError(tableBodyElements.popularTests, 3, errorMessage);
            setTableError(tableBodyElements.recentReports, 4, errorMessage);
            setTableError(tableBodyElements.recentPayments, 6, errorMessage);
            setTableError(tableBodyElements.recentResults, 6, errorMessage);
            
            // Also update branch name if it was loading
            const branchNameLoadingEl = document.getElementById('branch-name-loading');
            if (branchNameLoadingEl) {
                branchNameLoadingEl.innerHTML = '<span class="text-danger">Error loading branch</span>';
            }
        }

        function updateDashboardUI(data) {
            console.log('Updating dashboard UI with data:', data);

            // Update branch info
            const branchNameContainer = document.querySelector('p.text-muted');
            if (branchNameContainer) {
                if (data.branch && data.branch.branch_name) {
                    // If the loading span exists, replace its content, otherwise set the container's content
                    const branchNameLoadingEl = document.getElementById('branch-name-loading');
                    if (branchNameLoadingEl) {
                        branchNameLoadingEl.textContent = escapeHtml(data.branch.branch_name);
                        branchNameLoadingEl.id = 'branch-name-display'; // Change ID after loading
                    } else {
                         branchNameContainer.textContent = escapeHtml(data.branch.branch_name);
                    }
                } else {
                     branchNameContainer.innerHTML = '<span class="text-danger">Branch information not available.</span>';
                }
            }


            // Update period stats
            const periodStats = data.period_stats || {};
            setElementText(statElements.newPatients, periodStats.new_patients);
            setElementText(statElements.completedReports, periodStats.completed_reports);
            setElementText(statElements.pendingReports, periodStats.pending_reports);
            setElementText(statElements.periodRevenue, periodStats.period_revenue, true);

            // Update overall stats
            const stats = data.stats || {};
            setElementText(statElements.totalPatients, stats.total_patients);
            setElementText(statElements.totalReports, stats.total_reports);
            setElementText(statElements.availableTests, stats.available_tests);
            setElementText(statElements.totalRevenue, stats.total_revenue, true);

            // Update popular tests table
            if (tableBodyElements.popularTests) {
                if (data.popular_tests && data.popular_tests.length > 0) {
                    tableBodyElements.popularTests.innerHTML = data.popular_tests.map(test => `
                        <tr>
                            <td>${escapeHtml(test.test_name)}</td>
                            <td>${escapeHtml(test.report_count)}</td>
                            <td>Rs ${parseFloat(test.revenue || 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                } else {
                    tableBodyElements.popularTests.innerHTML = '<tr><td colspan="3" class="text-center text-muted small">No popular tests data available for this period.</td></tr>';
                }
            }

            // Update recent reports table
            if (tableBodyElements.recentReports) {
                if (data.recent_reports && data.recent_reports.length > 0) {
                    tableBodyElements.recentReports.innerHTML = data.recent_reports.map(report => `
                        <tr>
                            <td>${escapeHtml(report.patient_name)}</td>
                            <td>${escapeHtml(report.test_name)}</td>
                            <td><span class="badge bg-${getReportStatusClass(report.status)}">${escapeHtml(report.status)}</span></td>
                            <td>${new Date(report.created_at).toLocaleDateString()}</td>
                        </tr>
                    `).join('');
                } else {
                    tableBodyElements.recentReports.innerHTML = '<tr><td colspan="4" class="text-center text-muted small">No recent reports available for this period.</td></tr>';
                }
            }
            
            // Update recent payments table
            if (tableBodyElements.recentPayments) {
                if (data.recent_payments && data.recent_payments.length > 0) {
                    tableBodyElements.recentPayments.innerHTML = data.recent_payments.map(payment => `
                        <tr>
                            <td>${escapeHtml(payment.invoice_no)}</td>
                            <td>${escapeHtml(payment.patient_name)}</td>
                            <td>${escapeHtml(payment.test_name)}</td>
                            <td>Rs ${parseFloat(payment.paid_amount || 0).toFixed(2)}</td>
                            <td>${escapeHtml(payment.payment_method || payment.payment_mode)}</td>
                            <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                        </tr>
                    `).join('');
                } else {
                    tableBodyElements.recentPayments.innerHTML = '<tr><td colspan="6" class="text-center text-muted small">No recent payments available for this period.</td></tr>';
                }
            }

            // Update recent test results table
            if (tableBodyElements.recentResults) {
                if (data.recent_results && data.recent_results.length > 0) {
                    tableBodyElements.recentResults.innerHTML = data.recent_results.map(result => `
                        <tr>
                            <td>${escapeHtml(result.id)}</td>
                            <td>${escapeHtml(result.patient_name)}</td>
                            <td>${escapeHtml(result.test_name)}</td>
                            <td>${escapeHtml(result.result) || '<span class="text-muted small">N/A</span>'}</td>
                            <td>${new Date(result.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-report-btn" data-report-id="${result.id}">View</button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tableBodyElements.recentResults.innerHTML = '<tr><td colspan="6" class="text-center text-muted small">No recent test results available.</td></tr>';
                }
            }
            console.log('Dashboard UI update complete.');
        }

        function loadDashboardData() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            if(dashboardLoadingOverlay) dashboardLoadingOverlay.style.display = 'flex';
            if(filterSpinner) filterSpinner.classList.remove('d-none');
            if(refreshButton) refreshButton.disabled = true;


            // Reset initial state for cards to show individual spinners before new data or error
            Object.values(statElements).forEach(el => {
                if(el) el.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
            });
             Object.values(tableBodyElements).forEach(el => {
                if(el) {
                    const colspan = el.id === 'popular-tests-tbody' ? 3 : el.id === 'recent-reports-tbody' ? 4 : 6;
                    el.innerHTML = `<tr><td colspan="${colspan}" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`;
                }
            });


            return fetch(`ajax/get_dashboard_data.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Server error response (text):', text);
                            throw new Error(`HTTP error! Status: ${response.status}. Response: ${text.substring(0, 500)}`);
                        });
                    }
                    return response.json().catch(jsonError => {
                        console.error('Failed to parse JSON:', jsonError);
                        // Attempt to get the raw response text to understand why JSON parsing failed
                        return response.text().then(text => {
                             console.error('Server response (text) that failed JSON parsing:', text);
                             throw new Error('Failed to parse JSON response from server. Body: ' + text.substring(0,500));
                        });
                    });
                })
                .then(data => {
                    if (data && data.success) {
                        updateDashboardUI(data);
                    } else {
                        const errorMsg = data && data.message ? data.message : 'Received invalid data structure or server indicated an error.';
                        console.error('Dashboard data error:', errorMsg, 'Data:', data);
                        clearAllSpinnersAndSetErrorState(errorMsg);
                    }
                }).catch(error => {
                    console.error('Critical error loading dashboard data:', error);
                    clearAllSpinnersAndSetErrorState(error.message || 'A critical error occurred while fetching dashboard data.');
                })
                .finally(() => {
                    if(dashboardLoadingOverlay) dashboardLoadingOverlay.style.display = 'none';
                    if(filterSpinner) filterSpinner.classList.add('d-none');
                    if(refreshButton) {
                        refreshButton.disabled = false;
                        refreshButton.innerHTML = '<i class="fas fa-sync"></i>';
                    }
                    console.log('Finished loading attempt. Main overlay and button spinners should be reset.');
                });
        }
        
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
                loadDashboardData();
            });
        }

        function toggleDateInputsVisibility() {
            const isCustom = dateRangeSelect.value === 'custom';
            dateInputs.forEach(input => {
                input.style.display = isCustom ? 'block' : 'none';
            });
        }
        
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', toggleDateInputsVisibility);
            toggleDateInputsVisibility(); // Initial call
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadDashboardData();
                
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                const newUrl = window.location.pathname + '?' + params.toString();
                window.history.pushState({}, '', newUrl);
            });
        }
        
        // Initial data load
        loadDashboardData();

        // --- Modal and Print Logic (from previous version, ensure it's still functional) ---
        function viewReport(reportId) {
            const reportContent = document.getElementById('viewReportContent');
            const printViewedReportBtn = document.getElementById('printViewedReport');
            if (!reportContent || !printViewedReportBtn) {
                console.error('Modal elements for viewing report not found.');
                return;
            }
            reportContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading report...</span></div></div>';
            printViewedReportBtn.dataset.reportId = reportId;

            fetch(`ajax/get-report.php?report_id=${reportId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(`HTTP error! Status: ${response.status}, Body: ${text.substring(0, 200)}`); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.html) {
                        reportContent.innerHTML = data.html;
                    } else {
                        reportContent.innerHTML = `<div class="alert alert-danger">${escapeHtml(data.message || 'Could not load report details.')}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching report for modal:', error);
                    reportContent.innerHTML = `<div class="alert alert-danger">Error loading report: ${escapeHtml(error.message)}</div>`;
                });
        }

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('view-report-btn')) {
                const reportId = event.target.dataset.reportId;
                if (reportId) {
                    const viewReportModalElement = document.getElementById('viewReportModal');
                    if (viewReportModalElement) {
                        const viewReportModal = new bootstrap.Modal(viewReportModalElement);
                        viewReport(reportId);
                        viewReportModal.show();
                    } else {
                        console.error('View report modal element not found.');
                    }
                }
            }
        });

        const printViewedReportBtn = document.getElementById('printViewedReport');
        if (printViewedReportBtn) {
            printViewedReportBtn.addEventListener('click', function() {
                const reportId = this.dataset.reportId;
                if (reportId) {
                    window.open(`print-report.php?id=${reportId}`, '_blank');
                }
            });
        }
    });
    </script>

<?php include '../inc/footer.php'; ?>
