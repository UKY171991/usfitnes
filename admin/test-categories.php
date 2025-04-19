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
    
    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("
                UPDATE test_categories 
                SET category_name = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $category_id]);
            
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

// Get all categories
$categories = $conn->query("SELECT * FROM test_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

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

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Tests Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $category): 
                // Get test count for this category
                $test_count = $conn->prepare("SELECT COUNT(*) FROM tests WHERE category_id = ?");
                $test_count->execute([$category['id']]);
                $count = $test_count->fetchColumn();
            ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td><?php echo $count; ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editCategory(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $category['id'],
                                'category_name' => $category['category_name'],
                                'description' => $category['description']
                            ])); 
                        ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <input type="hidden" name="delete_category" value="1">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category? All tests in this category will also be deleted.')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="edit_category" value="1">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description"></textarea>
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
function editCategory(categoryData) {
    // Populate the edit modal with category data
    document.getElementById('edit_category_id').value = categoryData.id;
    document.getElementById('edit_category_name').value = categoryData.category_name;
    document.getElementById('edit_description').value = categoryData.description || '';
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>

<?php include '../inc/footer.php'; ?> 