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
    
    if(!empty($test_name) && !empty($category_id) && !empty($price)) {
        try {
            $stmt = $conn->prepare("
                UPDATE tests 
                SET test_name = ?, category_id = ?, price = ?, description = ?,
                    normal_range = ?, sample_type = ?, preparation = ?, reporting_time = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $test_name, $category_id, $price, $description, $normal_range,
                $sample_type, $preparation, $reporting_time, $test_id
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

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Sample Type</th>
                <th>Reporting Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($tests as $test): ?>
                <tr>
                    <td><?php echo $test['id']; ?></td>
                    <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                    <td><?php echo htmlspecialchars($test['category_name']); ?></td>
                    <td>₹<?php echo number_format($test['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($test['sample_type']); ?></td>
                    <td><?php echo htmlspecialchars($test['reporting_time']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editTest(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $test['id'],
                                'test_name' => $test['test_name'],
                                'category_id' => $test['category_id'],
                                'price' => $test['price'],
                                'description' => $test['description'],
                                'normal_range' => $test['normal_range'],
                                'sample_type' => $test['sample_type'],
                                'preparation' => $test['preparation'],
                                'reporting_time' => $test['reporting_time']
                            ])); 
                        ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                            <input type="hidden" name="delete_test" value="1">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this test?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="test_name" name="test_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="sample_type" name="sample_type">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="normal_range" class="form-label">Normal Range</label>
                            <input type="text" class="form-control" id="normal_range" name="normal_range">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reporting_time" class="form-label">Reporting Time</label>
                            <input type="text" class="form-control" id="reporting_time" name="reporting_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="preparation" class="form-label">Preparation Instructions</label>
                        <textarea class="form-control" id="preparation" name="preparation" rows="3"></textarea>
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
            <form method="POST" action="">
                <input type="hidden" name="edit_test" value="1">
                <input type="hidden" name="test_id" id="edit_test_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="edit_test_name" name="test_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Category *</label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="edit_sample_type" name="sample_type">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_normal_range" class="form-label">Normal Range</label>
                            <input type="text" class="form-control" id="edit_normal_range" name="normal_range">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_reporting_time" class="form-label">Reporting Time</label>
                            <input type="text" class="form-control" id="edit_reporting_time" name="reporting_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_preparation" class="form-label">Preparation Instructions</label>
                        <textarea class="form-control" id="edit_preparation" name="preparation" rows="3"></textarea>
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
function editTest(testData) {
    // Populate the edit modal with test data
    document.getElementById('edit_test_id').value = testData.id;
    document.getElementById('edit_test_name').value = testData.test_name;
    document.getElementById('edit_category_id').value = testData.category_id;
    document.getElementById('edit_price').value = testData.price;
    document.getElementById('edit_description').value = testData.description || '';
    document.getElementById('edit_normal_range').value = testData.normal_range || '';
    document.getElementById('edit_sample_type').value = testData.sample_type || '';
    document.getElementById('edit_preparation').value = testData.preparation || '';
    document.getElementById('edit_reporting_time').value = testData.reporting_time || '';
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('editTestModal')).show();
}
</script>

<?php include '../inc/footer.php'; ?> 