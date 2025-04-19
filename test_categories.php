<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in and has Admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$successMsg = $errorMsg = '';

try {
    $db = Database::getInstance();
    
    // Handle category deletion if requested
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
        
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId === null) {
            throw new Exception('Invalid category ID');
        }
        
        // Check if category is in use
        $stmt = $db->query(
            "SELECT COUNT(*) as count FROM Tests_Catalog WHERE category_id = :category_id",
            ['category_id' => $categoryId]
        );
        $inUse = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if ($inUse) {
            $errorMsg = "Cannot delete category as it is being used by existing tests.";
        } else {
            $db->query(
                "DELETE FROM Test_Categories WHERE category_id = :category_id",
                ['category_id' => $categoryId]
            );
            $successMsg = "Category deleted successfully.";
        }
    }
    
    // Fetch all categories
    $stmt = $db->query(
        "SELECT tc.category_id, tc.category_name, tc.created_at, 
                COUNT(t.test_id) as test_count
         FROM Test_Categories tc
         LEFT JOIN Tests_Catalog t ON tc.category_id = t.category_id
         GROUP BY tc.category_id
         ORDER BY tc.category_name ASC"
    );
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Test Categories error: " . $e->getMessage());
    $errorMsg = "An error occurred while processing your request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'inc/head.php'; ?>
    <title>Test Categories | Lab Management System</title>
</head>
<body class="layout-fixed">
    <div class="wrapper">
        <?php include 'inc/sidebar.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Test Category Management</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                Add New Category
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?php if ($successMsg): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($successMsg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($errorMsg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Tests Count</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['test_count']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning edit-category" 
                                                            data-id="<?php echo htmlspecialchars($category['category_id']); ?>"
                                                            data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    
                                                    <?php if ($category['test_count'] == 0): ?>
                                                        <form method="post" class="d-inline delete-form">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                                            <input type="hidden" name="delete_category" value="1">
                                                            <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
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
                <form id="addCategoryForm" method="post" action="includes/process_category.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="category_name" required>
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
                <form id="editCategoryForm" method="post" action="includes/process_category.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="category_id" id="editCategoryId">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
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

    <?php include 'inc/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize edit buttons
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                document.getElementById('editCategoryId').value = id;
                document.getElementById('editCategoryName').value = name;
                
                new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
            });
        });
        
        // Initialize delete confirmations
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this category?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Form validation and submission
        const forms = document.querySelectorAll('#addCategoryForm, #editCategoryForm');
        forms.forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'An error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request');
                }
            });
        });
    });
    </script>
</body>
</html>