<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];

// Handle form submission for adding/updating branch test
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $test_id = $_POST['test_id'] ?? '';
    $price = trim($_POST['price'] ?? '');
    $reporting_time = trim($_POST['reporting_time'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    $errors = [];
    if (empty($test_id)) {
        $errors[] = "Test selection is required";
    }
    if (empty($price)) {
        $errors[] = "Price is required";
    } elseif (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a valid positive number";
    }
    if (strlen($reporting_time) > 50) {
        $errors[] = "Reporting time must be less than 50 characters";
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Check if branch test already exists
            $stmt = $conn->prepare("SELECT id FROM branch_tests WHERE branch_id = ? AND test_id = ?");
            $stmt->execute([$branch_id, $test_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing branch test
                $stmt = $conn->prepare("
                    UPDATE branch_tests 
                    SET price = ?, reporting_time = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE branch_id = ? AND test_id = ?
                ");
                $stmt->execute([$price, $reporting_time, $status, $branch_id, $test_id]);
                $success_msg = "Test updated successfully";
            } else {
                // Add new branch test
                $stmt = $conn->prepare("
                    INSERT INTO branch_tests (branch_id, test_id, price, reporting_time, status) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$branch_id, $test_id, $price, $reporting_time, $status]);
                $success_msg = "Test added successfully";
            }

            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $error_msg = implode("<br>", $errors);
    }
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
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus"></i> Add/Update Test
    </button>
</div>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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

<!-- Add/Edit Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Update Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="testForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="test_id" id="test_id">
                    <div class="mb-3">
                        <label class="form-label">Test Name</label>
                        <input type="text" class="form-control" id="test_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="invalid-feedback">Please enter a valid price.</div>
                    </div>
                    <div class="mb-3">
                        <label for="reporting_time" class="form-label">Reporting Time</label>
                        <input type="text" class="form-control" id="reporting_time" name="reporting_time" 
                               placeholder="e.g., 24 hours, Same day, etc." maxlength="50">
                        <div class="form-text">Maximum 50 characters</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" checked>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveButton">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Save Changes
                    </button>
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
    // Form validation
    const form = document.getElementById('testForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            const submitButton = document.getElementById('saveButton');
            const spinner = submitButton.querySelector('.spinner-border');
            submitButton.disabled = true;
            spinner.classList.remove('d-none');
        }
        form.classList.add('was-validated');
    });

    // Handle edit test button clicks
    document.querySelectorAll('.edit-test').forEach(button => {
        button.addEventListener('click', function() {
            const testData = JSON.parse(this.dataset.test);
            document.getElementById('test_id').value = testData.id;
            document.getElementById('test_name').value = testData.test_name;
            document.getElementById('price').value = testData.price;
            document.getElementById('reporting_time').value = testData.reporting_time;
            document.getElementById('status').checked = testData.status == 1;
            
            // Reset validation state
            form.classList.remove('was-validated');
            document.getElementById('saveButton').disabled = false;
            document.querySelector('#saveButton .spinner-border').classList.add('d-none');
        });
    });

    // Handle test details button clicks
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

    // Auto-submit form on filter change
    document.getElementById('category').addEventListener('change', function() {
        this.form.submit();
    });
    document.getElementById('status').addEventListener('change', function() {
        this.form.submit();
    });
});
</script>

<?php include '../inc/footer.php'; ?> 