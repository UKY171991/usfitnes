<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];

// --- Fetch Master Tests for Dropdown ---
$master_tests_list = [];
try {
    // Fetch default price and reporting time as well
    $tests_stmt = $conn->query("SELECT id, test_name, price, reporting_time FROM tests WHERE status = '1' ORDER BY test_name");
    $master_tests_list = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching master tests for modal: " . $e->getMessage());
    // Set an error message to display if needed
    $page_error_msg = "Could not load master test list.";
}

// Search and filter parameters
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

// Build the base query
$base_query = "
    FROM tests t
    LEFT JOIN test_categories c ON t.category_id = c.id
    LEFT JOIN branch_tests bt ON t.id = bt.test_id AND bt.branch_id = :branch_id
    WHERE 1=1
";

$params = ['branch_id' => $branch_id];

if ($search) {
    $base_query .= " AND (t.test_name LIKE :search OR c.category_name LIKE :search)";
    $params['search'] = "%$search%";
}

if ($category_filter) {
    $base_query .= " AND c.id = :category_id";
    $params['category_id'] = $category_filter;
}

if ($status_filter !== '') {
    if ($status_filter === 'added') {
        $base_query .= " AND bt.id IS NOT NULL";
    } else if ($status_filter === 'not_added') {
        $base_query .= " AND bt.id IS NULL";
    }
}

// Get total count for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) " . $base_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_tests = $count_stmt->fetchColumn();
$total_pages = ceil($total_tests / $per_page);

// Get tests with pagination
$query = "
    SELECT 
        t.id,
        t.test_name,
        t.method,
        t.unit,
        t.normal_range,
        t.sample_type,
        t.preparation,
        c.id as category_id,
        c.category_name,
        bt.price as branch_price,
        bt.reporting_time as branch_reporting_time,
        bt.status as branch_status,
        COALESCE(bt.price, t.price) as effective_price,
        COALESCE(bt.reporting_time, t.reporting_time) as effective_reporting_time,
        CASE WHEN bt.id IS NOT NULL THEN 1 ELSE 0 END as is_branch_test
    " . $base_query . "
    ORDER BY c.category_name, t.test_name
    LIMIT :start, :per_page
";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_stmt = $conn->prepare("SELECT id, category_name FROM test_categories ORDER BY category_name");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group tests by category
$grouped_tests = [];
foreach ($tests as $test) {
    $grouped_tests[$test['category_name']][] = $test;
}

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Tests</h1>
    <button type="button" class="btn btn-primary" id="mainAddUpdateButton" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus"></i> Add/Update Test
    </button>
</div>

<!-- Placeholder for AJAX messages -->
<div id="message-container" class="mb-3"></div> 

<?php if (isset($page_error_msg)): /* Keep page-load errors if needed */ ?>
    <div class="alert alert-warning"><?php echo $page_error_msg; ?></div>
<?php endif; ?>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by test or category name">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="added" <?php echo $status_filter === 'added' ? 'selected' : ''; ?>>Added</option>
                    <option value="not_added" <?php echo $status_filter === 'not_added' ? 'selected' : ''; ?>>Not Added</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tests by Category -->
<div class="accordion" id="testsAccordion">
    <?php if (empty($grouped_tests)): ?>
        <div class="alert alert-info">No tests found matching your criteria.</div>
    <?php else: ?>
        <?php foreach ($grouped_tests as $category => $category_tests): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#category<?php echo md5($category); ?>">
                    <?php echo htmlspecialchars($category); ?> 
                    <span class="badge bg-primary ms-2"><?php echo count($category_tests); ?></span>
                </button>
            </h2>
            <div id="category<?php echo md5($category); ?>" class="accordion-collapse collapse" data-bs-parent="#testsAccordion">
                <div class="accordion-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Method</th>
                                    <th>Sample Type</th>
                                    <th>Price</th>
                                    <th>Reporting Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category_tests as $test): ?>
                                <tr class="<?php echo $test['is_branch_test'] ? 'table-info' : ''; ?>">
                                    <td>
                                        <?php echo htmlspecialchars($test['test_name']); ?>
                                        <?php if ($test['is_branch_test']): ?>
                                            <span class="badge bg-info">Custom</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($test['method'] ?: 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($test['sample_type'] ?: 'Not specified'); ?></td>
                                    <td>₹<?php echo number_format($test['effective_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($test['effective_reporting_time'] ?: 'Not set'); ?></td>
                                    <td>
                                        <?php if ($test['is_branch_test']): ?>
                                            <span class="badge bg-<?php echo $test['branch_status'] ? 'success' : 'danger'; ?>">
                                                <?php echo $test['branch_status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Added</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary edit-test" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addTestModal"
                                                    data-test='<?php echo htmlspecialchars(json_encode([
                                                        'id' => $test['id'],
                                                        'test_name' => $test['test_name'],
                                                        'price' => $test['branch_price'] ?? $test['effective_price'],
                                                        'reporting_time' => $test['branch_reporting_time'] ?? '',
                                                        'status' => $test['branch_status'] ?? 1
                                                    ])); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#testDetailsModal"
                                                    data-test='<?php echo htmlspecialchars(json_encode([
                                                        'test_name' => $test['test_name'],
                                                        'method' => $test['method'],
                                                        'unit' => $test['unit'],
                                                        'normal_range' => $test['normal_range'],
                                                        'sample_type' => $test['sample_type'],
                                                        'preparation' => $test['preparation']
                                                    ])); ?>'>
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
            </li>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Add/Update Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Update Test Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchTestForm" method="POST">
                <div class="modal-body">
                    <!-- Test Selection Dropdown -->
                    <div class="mb-3">
                        <label for="modal_test_id" class="form-label">Select Test *</label>
                        <select class="form-select" id="modal_test_id" name="test_id" required>
                            <option value="" data-price="" data-reporting-time="">-- Select a Master Test --</option>
                            <?php if (empty($master_tests_list)): ?>
                                <option value="" disabled>No active master tests found.</option>
                            <?php else: ?>
                                <?php foreach ($master_tests_list as $master_test): ?>
                                    <option 
                                        value="<?php echo $master_test['id']; ?>"
                                        data-price="<?php echo htmlspecialchars($master_test['price'] ?? ''); ?>"
                                        data-reporting-time="<?php echo htmlspecialchars($master_test['reporting_time'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($master_test['test_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select a test.</div>
                    </div>
                    
                    <!-- Price Input -->
                    <div class="mb-3">
                        <label for="modal_price" class="form-label">Branch Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="modal_price" name="price" required min="0" step="0.01" placeholder="Enter branch-specific price">
                        </div>
                        <div class="invalid-feedback">Please enter a valid positive price.</div>
                    </div>

                    <!-- Reporting Time Input -->
                    <div class="mb-3">
                        <label for="modal_reporting_time" class="form-label">Branch Reporting Time</label>
                        <input type="text" class="form-control" id="modal_reporting_time" name="reporting_time" maxlength="50" placeholder="e.g., 24 Hours, Same Day">
                         <small class="text-muted">Leave blank to use default time.</small>
                    </div>

                    <!-- Status Checkbox -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="modal_status" name="status" value="1" checked>
                        <label class="form-check-label" for="modal_status">Make Active for this Branch</label>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Details Modal -->
<div class="modal fade" id="testDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Test Name:</strong> <span id="detail_test_name"></span></p>
                        <p><strong>Method:</strong> <span id="detail_method"></span></p>
                        <p><strong>Unit:</strong> <span id="detail_unit"></span></p>
                        <p><strong>Normal Range:</strong> <span id="detail_normal_range"></span></p>
                        <p><strong>Sample Type:</strong> <span id="detail_sample_type"></span></p>
                        <p><strong>Patient Preparation:</strong> <span id="detail_preparation"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addTestModalEl = document.getElementById('addTestModal');
    const addTestModal = bootstrap.Modal.getInstance(addTestModalEl) || new bootstrap.Modal(addTestModalEl);
    const testForm = document.getElementById('branchTestForm'); // Use form ID
    const testSelect = document.getElementById('modal_test_id');
    const priceInput = document.getElementById('modal_price');
    const timeInput = document.getElementById('modal_reporting_time');
    const statusCheckbox = document.getElementById('modal_status');
    const messageContainer = document.getElementById('message-container'); // Message container

    // Helper to show messages
    function showMessage(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        messageContainer.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                        ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                      </div>`;
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
     // Helper function to refresh the page (simple refresh)
    function refreshTestList() {
        // For simplicity, reload the page to see changes.
        // A more advanced implementation would fetch data and rebuild the accordion.
        window.location.reload(); 
    }

    // Reset form only when opened by the main Add/Update button
    addTestModalEl.addEventListener('show.bs.modal', function (event) {
        if (event.relatedTarget && event.relatedTarget.id === 'mainAddUpdateButton') {
            testForm.reset();
            testSelect.disabled = false;
            statusCheckbox.checked = true;
            testForm.classList.remove('was-validated');
        }
    });

    // Handle Edit button clicks
    document.querySelectorAll('.edit-test').forEach(button => {
        button.addEventListener('click', function() {
            const testData = JSON.parse(this.dataset.test);
            testSelect.value = testData.id;
            testSelect.disabled = true;
            priceInput.value = testData.price || '';
            timeInput.value = testData.reporting_time || '';
            statusCheckbox.checked = testData.status == 1;
            testForm.classList.remove('was-validated');
        });
    });

    // Add Change listener to Test Select dropdown
    testSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value === "") {
            priceInput.value = '';
            timeInput.value = '';
             statusCheckbox.checked = true; 
        } else {
            const defaultPrice = selectedOption.dataset.price;
            const defaultTime = selectedOption.dataset.reportingTime;
            priceInput.value = defaultPrice || '';
            timeInput.value = defaultTime || '';
            statusCheckbox.checked = true; 
        }
        testForm.classList.remove('was-validated');
    });
    
    // Handle AJAX form submission
    testForm.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!testForm.checkValidity()) {
            testForm.classList.add('was-validated');
            return; 
        }

        // Re-enable select before creating FormData if it was disabled for edit
        const wasDisabled = testSelect.disabled;
        if (wasDisabled) {
            testSelect.disabled = false;
        }
        
        const formData = new FormData(testForm);
        const submitButton = testForm.querySelector('button[type="submit"]');
        submitButton.disabled = true; // Disable button
        
        // Restore disabled state if needed (for UI consistency)
        if (wasDisabled) {
             testSelect.disabled = true;
        }

        fetch('ajax/update-branch-test.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                addTestModal.hide();
                refreshTestList(); // Reload page to see changes
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An unexpected error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            submitButton.disabled = false; // Re-enable button
             // Re-enable select if it was disabled for edit, regardless of outcome
             if (wasDisabled) {
                 testSelect.disabled = false;
             }
        });
    });
    
    // Test Details Modal Population (keep existing logic)
    document.querySelectorAll('[data-bs-target="#testDetailsModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const testData = JSON.parse(this.dataset.test);
            document.getElementById('detail_test_name').textContent = testData.test_name;
            document.getElementById('detail_method').textContent = testData.method || 'Not specified';
            document.getElementById('detail_unit').textContent = testData.unit || 'Not specified';
            document.getElementById('detail_normal_range').textContent = testData.normal_range || 'Not specified';
            document.getElementById('detail_sample_type').textContent = testData.sample_type || 'Not specified';
            document.getElementById('detail_preparation').textContent = testData.preparation || 'No special preparation required';
        });
    });
    
});
</script>

<?php include '../inc/footer.php'; ?> 