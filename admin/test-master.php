<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle delete request
if(isset($_POST['delete_test'])) {
    $test_id = $_POST['test_id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
        $stmt->execute([$test_id]);
        
        // Log activity
        $activity = "Test deleted: ID $test_id";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
        
        header("Location: test-master.php?success=2");
        exit();
    } catch(PDOException $e) {
        $error = "Error deleting test: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_test'])) {
    $test_id = $_POST['test_id'] ?? 0;
    $test_name = $_POST['test_name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $description = $_POST['description'] ?? '';
    $normal_range = $_POST['normal_range'] ?? '';
    $sample_type = $_POST['sample_type'] ?? '';
    $preparation = $_POST['preparation'] ?? '';
    $reporting_time = $_POST['reporting_time'] ?? '';
    $status = $_POST['status'] ?? 1; // Get status value
    
    if(!empty($test_name) && !empty($category_id) && !empty($price)) {
        try {
            $stmt = $conn->prepare("
                UPDATE tests 
                SET test_name = ?, category_id = ?, price = ?, description = ?,
                    normal_range = ?, sample_type = ?, preparation = ?, reporting_time = ?,
                    status = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $test_name, $category_id, $price, $description, $normal_range,
                $sample_type, $preparation, $reporting_time, $status, $test_id
            ]);
            
            // Log activity
            $activity = "Test updated: $test_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-master.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating test: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Handle form submission for new test
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_test']) && !isset($_POST['delete_test'])) {
    $test_name = $_POST['test_name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $description = $_POST['description'] ?? '';
    $normal_range = $_POST['normal_range'] ?? '';
    $sample_type = $_POST['sample_type'] ?? '';
    $preparation = $_POST['preparation'] ?? '';
    $reporting_time = $_POST['reporting_time'] ?? '';
    // Status defaults to 1 (active) in the DB, so no need to explicitly set it here for new tests
    
    if(!empty($test_name) && !empty($category_id) && !empty($price)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tests (test_name, category_id, price, description, normal_range, 
                                 sample_type, preparation, reporting_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $test_name, $category_id, $price, $description, $normal_range, 
                $sample_type, $preparation, $reporting_time
            ]);
            
            // Log activity
            $activity = "New test added: $test_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-master.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding test: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all tests with category names
$tests = $conn->query("
    SELECT t.*, c.category_name as category_name 
    FROM tests t 
    LEFT JOIN test_categories c ON t.category_id = c.id 
    ORDER BY t.test_name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$categories = $conn->query("SELECT id, category_name FROM test_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Tests</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus"></i> Add New Test
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
            switch($_GET['success']) {
                case 1:
                    echo "Test added successfully!";
                    break;
                case 2:
                    echo "Test deleted successfully!";
                    break;
                case 3:
                    echo "Test updated successfully!";
                    break;
            }
        ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Tests Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Test Details</th>
                        <th>Price</th>
                        <th>Sample</th>
                        <th>Reporting Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tests)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-vial fa-2x mb-2"></i>
                                    <p>No tests found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($tests as $test): 
                            $status = $test['status'] ?? 1; // Default to active (1) if missing
                        ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $test['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($test['test_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($test['category_name']); ?></small>
                                </td>
                                <td>₹<?php echo number_format($test['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($test['sample_type'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($test['reporting_time'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status == 1 ? 'success' : 'danger'; ?>">
                                        <?php echo $status == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-test" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTestModal"
                                                data-test='<?php echo htmlspecialchars(json_encode($test)); ?>'
                                                title="View Test">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-test" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTestModal"
                                                data-test='<?php echo htmlspecialchars(json_encode($test)); ?>'
                                                title="Edit Test">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this test?')">
                                            <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                            <input type="hidden" name="delete_test" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Test">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<!-- Add Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="test_name" name="test_name" required>
                            <div class="invalid-feedback">Please enter the test name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter a valid price.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="sample_type" name="sample_type">
                        </div>
                        <div class="col-md-6">
                            <label for="normal_range" class="form-label">Normal Range</label>
                            <input type="text" class="form-control" id="normal_range" name="normal_range">
                        </div>
                        <div class="col-md-6">
                            <label for="reporting_time" class="form-label">Reporting Time (e.g., 24 hours)</label>
                            <input type="text" class="form-control" id="reporting_time" name="reporting_time">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="preparation" class="form-label">Preparation Instructions</label>
                            <textarea class="form-control" id="preparation" name="preparation" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Test Modal -->
<div class="modal fade" id="editTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="edit_test" value="1">
                <input type="hidden" name="test_id" id="edit_test_id">
                <div class="modal-body">
                    <div class="row g-3">
                         <div class="col-md-6">
                            <label for="edit_test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="edit_test_name" name="test_name" required>
                            <div class="invalid-feedback">Please enter the test name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_category_id" class="form-label">Category *</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter a valid price.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="edit_sample_type" name="sample_type">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_normal_range" class="form-label">Normal Range</label>
                            <input type="text" class="form-control" id="edit_normal_range" name="normal_range">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_reporting_time" class="form-label">Reporting Time (e.g., 24 hours)</label>
                            <input type="text" class="form-control" id="edit_reporting_time" name="reporting_time">
                        </div>
                        <div class="col-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="edit_preparation" class="form-label">Preparation Instructions</label>
                            <textarea class="form-control" id="edit_preparation" name="preparation" rows="2"></textarea>
                        </div>
                        <div class="col-md-6"> <!-- Added Status Field -->
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
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

<!-- View Test Modal -->
<div class="modal fade" id="viewTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Test Name</dt>
                                    <dd class="col-sm-8" id="view-test-name">-</dd>
                                    <dt class="col-sm-4">Category</dt>
                                    <dd class="col-sm-8" id="view-test-category">-</dd>
                                    <dt class="col-sm-4">Price</dt>
                                    <dd class="col-sm-8" id="view-test-price">-</dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-test-status">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Technical Details</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Sample Type</dt>
                                    <dd class="col-sm-8" id="view-test-sample">-</dd>
                                    <dt class="col-sm-4">Normal Range</dt>
                                    <dd class="col-sm-8" id="view-test-range">-</dd>
                                    <dt class="col-sm-4">Reporting Time</dt>
                                    <dd class="col-sm-8" id="view-test-reporting">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Additional Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-3">Description</dt>
                                    <dd class="col-sm-9" id="view-test-description">-</dd>
                                    <dt class="col-sm-3">Preparation</dt>
                                    <dd class="col-sm-9" id="view-test-preparation">-</dd>
                                </dl>
                            </div>
                        </div>
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
    // Initialize Modals
    const addTestModalEl = document.getElementById('addTestModal');
    const editTestModalEl = document.getElementById('editTestModal');
    const viewTestModalEl = document.getElementById('viewTestModal');
    const addTestModal = new bootstrap.Modal(addTestModalEl);
    const editTestModal = new bootstrap.Modal(editTestModalEl);
    const viewTestModal = new bootstrap.Modal(viewTestModalEl);

    // Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Reset Add form when modal is opened
    addTestModalEl.addEventListener('show.bs.modal', function () {
        const form = addTestModalEl.querySelector('form');
        form.reset();
        form.classList.remove('was-validated');
    });

    // Handle View Button Clicks
    document.querySelectorAll('.view-test').forEach(button => {
        button.addEventListener('click', function() {
            const testData = JSON.parse(this.dataset.test);
            const status = testData.status ?? 1;
            document.getElementById('view-test-name').textContent = testData.test_name || '-';
            document.getElementById('view-test-category').textContent = testData.category_name || '-';
            document.getElementById('view-test-price').textContent = testData.price ? `₹${parseFloat(testData.price).toFixed(2)}` : '-';
            document.getElementById('view-test-status').innerHTML = `<span class="badge bg-${status == 1 ? 'success' : 'danger'}">${status == 1 ? 'Active' : 'Inactive'}</span>`;
            document.getElementById('view-test-sample').textContent = testData.sample_type || '-';
            document.getElementById('view-test-range').textContent = testData.normal_range || '-';
            document.getElementById('view-test-reporting').textContent = testData.reporting_time || '-';
            document.getElementById('view-test-description').textContent = testData.description || '-';
            document.getElementById('view-test-preparation').textContent = testData.preparation || '-';
            // viewTestModal.show(); // Modal is shown via data-bs-toggle
        });
    });

    // Handle Edit Button Clicks
    document.querySelectorAll('.edit-test').forEach(button => {
        button.addEventListener('click', function() {
            const testData = JSON.parse(this.dataset.test);
            const status = testData.status ?? 1;
            
            // Populate edit form
            document.getElementById('edit_test_id').value = testData.id;
            document.getElementById('edit_test_name').value = testData.test_name || '';
            document.getElementById('edit_category_id').value = testData.category_id || '';
            document.getElementById('edit_price').value = testData.price || '';
            document.getElementById('edit_description').value = testData.description || '';
            document.getElementById('edit_normal_range').value = testData.normal_range || '';
            document.getElementById('edit_sample_type').value = testData.sample_type || '';
            document.getElementById('edit_preparation').value = testData.preparation || '';
            document.getElementById('edit_reporting_time').value = testData.reporting_time || '';
            document.getElementById('edit_status').value = status; // Set status value
            
            // Reset validation state if needed
            const form = editTestModalEl.querySelector('form');
            form.classList.remove('was-validated');
            
            // editTestModal.show(); // Modal is shown via data-bs-toggle
        });
    });
});
</script>

<?php include '../inc/footer.php'; ?>