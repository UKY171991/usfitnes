<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO test_categories (name, description) VALUES (?, ?)");
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
$categories = $conn->query("SELECT * FROM test_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

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
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td><?php echo $count; ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editCategory(<?php echo $category['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
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
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
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

<script>
function editCategory(id) {
    // Implement edit functionality
    alert('Edit functionality will be implemented');
}

function deleteCategory(id) {
    if(confirm('Are you sure you want to delete this category? All tests in this category will also be deleted.')) {
        // Implement delete functionality
        alert('Delete functionality will be implemented');
    }
}
</script>

<?php include '../inc/footer.php'; ?> 