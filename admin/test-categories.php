<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle delete request
if(isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Delete associated tests first
        $stmt = $conn->prepare("DELETE FROM tests WHERE category_id = ?");
        $stmt->execute([$category_id]);
        
        // Then delete the category
        $stmt = $conn->prepare("DELETE FROM test_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        // Log activity
        $activity = "Test category deleted: ID $category_id";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
        
        // Commit transaction
        $conn->commit();
        
        header("Location: test-categories.php?success=2");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = "Error deleting category: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    $name = $_POST['category_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 0; // Get status value (default to 0 - inactive)
    
    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("
                UPDATE test_categories 
                SET category_name = ?, description = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $status, $category_id]);
            
            // Log activity
            $activity = "Test category updated: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-categories.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating category: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a category name";
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['category_name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO test_categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            
            // Log activity
            $activity = "New test category added: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-categories.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a category name";
    }
}

// Get all categories with test counts
$stmt = $conn->query("
    SELECT 
        tc.*, 
        COUNT(t.id) as test_count 
    FROM test_categories tc
    LEFT JOIN tests t ON tc.id = t.category_id
    GROUP BY tc.id
    ORDER BY tc.category_name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Test Categories</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Category added successfully!</div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Test Categories Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Category Details</th>
                        <th>Tests</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-flask fa-2x mb-2"></i>
                                    <p>No test categories found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($categories as $category): 
                            $status = $category['status'] ?? 'inactive'; // Default to inactive if missing
                            $test_count = $category['test_count'] ?? 0; // Default to 0 if missing
                        ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $category['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($category['description'] ?: 'No description'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $test_count; ?> Tests
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $status == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-category" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewCategoryModal"
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                                data-status="<?php echo $status; ?>"
                                                data-test-count="<?php echo $test_count; ?>"
                                                title="View Category">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-category" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#categoryModal"
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                                data-status="<?php echo $status; ?>"
                                                title="Edit Category">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this category? All tests in this category will also be deleted.')"
                                           title="Delete Category">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Category Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Category Name</dt>
                            <dd class="col-sm-8" id="view-category-name">-</dd>
                            
                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8" id="view-category-description">-</dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8" id="view-category-status">-</dd>
                            
                            <dt class="col-sm-4">Tests Count</dt>
                            <dd class="col-sm-8" id="view-category-test-count">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="edit_category" value="1">
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const categoryEditModalEl = document.getElementById('categoryModal');
    const categoryViewModalEl = document.getElementById('viewCategoryModal');
    
    // Initialize Bootstrap Modals
    const categoryEditModal = new bootstrap.Modal(categoryEditModalEl);
    const categoryViewModal = new bootstrap.Modal(categoryViewModalEl); // Assuming view modal exists

    // Handle view category button clicks
    document.querySelectorAll('.view-category').forEach(button => {
        button.addEventListener('click', function() {
            // Update view modal content
            document.getElementById('view-category-name').textContent = this.dataset.name || '-';
            document.getElementById('view-category-description').textContent = this.dataset.description || '-';
            document.getElementById('view-category-status').innerHTML = `
                <span class="badge bg-${this.dataset.status == 'active' ? 'success' : 'danger'}">
                    ${this.dataset.status == 'active' ? 'Active' : 'Inactive'}
                </span>
            `;
            document.getElementById('view-category-test-count').innerHTML = `
                <span class="badge bg-info">${this.dataset.testCount || 0} Tests</span>
            `;
            // Show view modal if needed (assuming it's separate)
            // categoryViewModal.show(); 
        });
    });

    // Handle edit category button clicks
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            // Get references to form elements inside the modal
            const modalTitle = categoryEditModalEl.querySelector('.modal-title');
            const categoryIdInput = categoryEditModalEl.querySelector('#category_id');
            const categoryNameInput = categoryEditModalEl.querySelector('#category_name');
            const descriptionTextarea = categoryEditModalEl.querySelector('#description');
            const statusSelect = categoryEditModalEl.querySelector('#status');

            // Set modal title
            modalTitle.textContent = 'Edit Category';
            
            // Fill form fields using data attributes
            categoryIdInput.value = this.dataset.id || '';
            categoryNameInput.value = this.dataset.name || '';
            descriptionTextarea.value = this.dataset.description || '';
            
            // Set status dropdown value (map 'active'/'inactive' string to 1/0)
            const statusValue = (this.dataset.status === 'active') ? '1' : '0';
            statusSelect.value = statusValue;
            
            // Show the modal
            categoryEditModal.show();
        });
    });
});
</script>

<?php include '../inc/footer.php'; ?> 