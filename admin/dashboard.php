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
<link rel="stylesheet" href="dashboard.css"> <!-- Ensure dashboard.css is also linked -->

<div class="container-fluid">
    <h1 class="dashboard-title">Admin Dashboard</h1>

    <!-- Date Filter Form -->
    <form id="filterForm" class="date-filter-form card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date_range" class="form-label">Select Period:</label>
                    <select name="date_range" id="date_range" class="form-select">
                        <option value="today" <?php echo ($date_range == 'today') ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo ($date_range == 'week') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo ($date_range == 'month') ? 'selected' : ''; ?>>This Month</option>
                        <option value="custom" <?php echo ($date_range == 'custom') ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3 date-inputs" style="display: <?php echo ($date_range == 'custom') ? 'block' : 'none'; ?>;">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($custom_start); ?>">
                </div>
                <div class="col-md-3 date-inputs" style="display: <?php echo ($date_range == 'custom') ? 'block' : 'none'; ?>;">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($custom_end); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Overall Stats Cards (These are not updated by AJAX in this example as they are overall) -->
    <div class="dashboard-cards-row">
    <?php
    $overall_queries = [
        [
            'label' => 'Total Branches',
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
            'label' => 'Test Master (All)',
            'query' => "SELECT COUNT(*) FROM tests", // Counts all tests
            'icon' => 'bi-archive', // Different icon
            'border' => 'border-info', // Different border color
            'footer' => 'Manage All Tests <i class="bi bi-arrow-right-circle"></i>',
            'footer_link' => 'test-master.php',
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
            'label' => 'Test Parameters',
            'query' => "SELECT COUNT(*) FROM test_parameters",
            'icon' => 'bi-sliders',
            'border' => 'border-primary',
            'footer' => 'Manage Parameters <i class="bi bi-arrow-right-circle"></i>',
            'footer_link' => 'test-parameters.php',
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
            <div class="card-content">
                <div class="card-text-content">
                    <div class="card-value"><?php echo $value; ?></div>
                    <div class="card-label"><?php echo $meta['label']; ?></div>
                </div>
                <span class="card-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
            </div>
            <a class="card-footer" href="<?php echo $meta['footer_link']; ?>"><?php echo $meta['footer']; ?></a>
        </div>
    <?php } ?>
    </div>

    <!-- Period Specific Stats - This section will be updated by AJAX -->
    <div id="periodSpecificStats">
        <h2 class="page-title mt-5 mb-3">Statistics for Selected Period</h2>
        <div class="row g-3 mb-4" id="periodStatsCardsContainer">
        <?php
        $period_queries = [
            'New Patients' => [
                'id' => 'new-patients-stat',
                'icon' => 'bi-person-plus',
                'border' => 'primary',
                'text' => 'primary',
                'value' => $period_stats['new_patients'] ?? 0,
                'format' => 'number',
            ],
            'Completed Reports' => [
                'id' => 'completed-reports-stat',
                'icon' => 'bi-file-earmark-check',
                'border' => 'success',
                'text' => 'success',
                'value' => $period_stats['completed_reports'] ?? 0,
                'format' => 'number',
            ],
            'Pending Reports' => [
                'id' => 'pending-reports-stat',
                'icon' => 'bi-hourglass-split',
                'border' => 'warning',
                'text' => 'warning',
                'value' => $period_stats['pending_reports'] ?? 0,
                'format' => 'number',
            ],
            'Revenue' => [
                'id' => 'revenue-stat',
                'icon' => 'bi-currency-rupee',
                'border' => 'info',
                'text' => 'info',
                'value' => $period_stats['period_revenue'] ?? 0,
                'format' => 'currency',
            ],
        ];

        foreach ($period_queries as $title => $meta) {
            $value_display = $meta['value'];
            if ($meta['format'] === 'currency') {
                $value_display = '₹' . number_format($value_display, 2);
            } else {
                $value_display = number_format($value_display);
            }
            ?>
            <div class="col-md-3">
                <div class="card card-stats border-<?php echo $meta['border']; ?> h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="icon text-<?php echo $meta['text']; ?> me-3"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
                        <div>
                            <h2 class="display-4" id="<?php echo $meta['id']; ?>"><?php echo $value_display; ?></h2>
                            <h6 class="card-title"><?php echo $title; ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    </div>

    <!-- Data Tables in Cards - These sections will be updated by AJAX -->
    <div class="row" id="dataTablesSection">
        <!-- Recent Payments -->
        <div class="col-md-6 mb-4">
            <div class="card card-table">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Payments (Selected Period)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Test</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recentPaymentsTableBody">
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
            <div class="card card-table">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activities (Selected Period)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivitiesTableBody">
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

    <!-- Small Quick Stats Cards Row (These are not updated by AJAX in this example) -->
    <h2 class="page-title mt-4 mb-3">Quick Overview (Overall)</h2>
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
            <div class="card-content">
                <div class="card-text-content">
                    <div class="card-value"><?php echo $meta['value']; ?></div>
                    <div class="card-label"><?php echo $meta['label']; ?></div>
                </div>
                <span class="card-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></span>
            </div>
        </div>
    <?php } ?>
    </div>

</div> <!-- End .container-fluid -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeSelect = document.getElementById('date_range');
    const dateInputs = document.querySelectorAll('.date-inputs');
    const filterForm = document.getElementById('filterForm');

    function toggleDateInputsVisibility() {
        const isCustom = dateRangeSelect.value === 'custom';
        dateInputs.forEach(input => {
            input.style.display = isCustom ? 'block' : 'none';
        });
    }

    dateRangeSelect.addEventListener('change', toggleDateInputsVisibility);

    // Initial call to set visibility based on pre-selected value (e.g., on page load)
    toggleDateInputsVisibility();

    filterForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const pair of formData) {
            params.append(pair[0], pair[1]);
        }

        // Show loading indicators or disable form
        const applyFilterButton = filterForm.querySelector('button[type="submit"]');
        const originalButtonText = applyFilterButton.innerHTML;
        applyFilterButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        applyFilterButton.disabled = true;

        fetch('ajax/get_dashboard_data.php', {
            method: 'POST',
            body: formData // Send form data directly
        })
        .then(response => {
            if (!response.ok) {
                // Try to get error message from response if available
                return response.json().then(errData => {
                    throw new Error(errData.error || `HTTP error! status: ${response.status}`);
                }).catch(() => {
                    // Fallback if response is not JSON or no error message
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error from server:', data.error);
                alert('Error loading data: ' + data.error + (data.debug_error ? '\\nDebug: ' + data.debug_error : ''));
                // Optionally update UI to show error message
                updatePeriodStatsOnError();
                updateRecentPaymentsOnError();
                updateRecentActivitiesOnError();
                return;
            }
            // Update period-specific stats cards
            updatePeriodStats(data.period_stats);
            // Update recent payments table
            updateRecentPaymentsTable(data.recent_payments);
            // Update recent activities table
            updateRecentActivitiesTable(data.activities);
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to load dashboard data. Please check the console for more details. Error: ' + error.message);
            // Update UI to show a generic error or clear data
            updatePeriodStatsOnError();
            updateRecentPaymentsOnError();
            updateRecentActivitiesOnError();
        })
        .finally(() => {
            // Re-enable button and restore text
            applyFilterButton.innerHTML = originalButtonText;
            applyFilterButton.disabled = false;
        });
    });

    function updatePeriodStats(stats) {
        if (!stats) {
            updatePeriodStatsOnError();
            return;
        }
        document.getElementById('new-patients-stat').textContent = stats.new_patients !== undefined ? Number(stats.new_patients).toLocaleString() : 'N/A';
        document.getElementById('completed-reports-stat').textContent = stats.completed_reports !== undefined ? Number(stats.completed_reports).toLocaleString() : 'N/A';
        document.getElementById('pending-reports-stat').textContent = stats.pending_reports !== undefined ? Number(stats.pending_reports).toLocaleString() : 'N/A';
        document.getElementById('revenue-stat').textContent = stats.period_revenue !== undefined ? '₹' + Number(stats.period_revenue).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/A';
    }

    function updateRecentPaymentsTable(payments) {
        const tbody = document.getElementById('recentPaymentsTableBody');
        tbody.innerHTML = ''; // Clear existing rows
        if (payments && payments.length > 0) {
            payments.forEach(payment => {
                const row = tbody.insertRow();
                row.insertCell().textContent = payment.patient_name || 'N/A';
                row.insertCell().textContent = payment.test_name || 'N/A';
                row.insertCell().textContent = payment.paid_amount ? '₹' + Number(payment.paid_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/A';
                row.insertCell().textContent = payment.created_at ? new Date(payment.created_at).toLocaleString() : 'N/A';
            });
        } else {
            const row = tbody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 4;
            cell.textContent = 'No recent payments for the selected period.';
            cell.classList.add('text-center');
        }
    }

    function updateRecentActivitiesTable(activities) {
        const tbody = document.getElementById('recentActivitiesTableBody');
        tbody.innerHTML = ''; // Clear existing rows
        if (activities && activities.length > 0) {
            activities.forEach(activity => {
                const row = tbody.insertRow();
                row.insertCell().textContent = activity.description || 'N/A';
                row.insertCell().textContent = activity.user_name || 'Unknown User';
                row.insertCell().textContent = activity.created_at ? new Date(activity.created_at).toLocaleString() : 'N/A';
            });
        } else {
            const row = tbody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 3;
            cell.textContent = 'No recent activities for the selected period.';
            cell.classList.add('text-center');
        }
    }

    function updatePeriodStatsOnError() {
        document.getElementById('new-patients-stat').textContent = 'Error';
        document.getElementById('completed-reports-stat').textContent = 'Error';
        document.getElementById('pending-reports-stat').textContent = 'Error';
        document.getElementById('revenue-stat').textContent = 'Error';
    }

    function updateRecentPaymentsOnError() {
        const tbody = document.getElementById('recentPaymentsTableBody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Error loading payments.</td></tr>';
    }

    function updateRecentActivitiesOnError() {
        const tbody = document.getElementById('recentActivitiesTableBody');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Error loading activities.</td></tr>';
    }

    // Trigger initial data load for "Today" when page loads, if desired,
    // or ensure the PHP part correctly loads initial data for "Today".
    // For a full AJAX driven approach, you might trigger a click or directly call fetch here.
    // Example: filterForm.dispatchEvent(new Event('submit')); // If you want to load via AJAX on page load
});
</script>

<?php include '../inc/footer.php'; ?>
