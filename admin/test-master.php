<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $description = $_POST['description'] ?? '';
    $normal_range = $_POST['normal_range'] ?? '';
    $sample_type = $_POST['sample_type'] ?? '';
    $preparation = $_POST['preparation'] ?? '';
    $reporting_time = $_POST['reporting_time'] ?? '';
    
    if(!empty($name) && !empty($category_id) && !empty($price)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tests (name, category_id, price, description, normal_range, 
                                 sample_type, preparation, reporting_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name, $category_id, $price, $description, $normal_range, 
                $sample_type, $preparation, $reporting_time
            ]);
            
            // Log activity
            $activity = "New test added: $name";
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
    SELECT t.*, c.name as category_name 
    FROM tests t 
    LEFT JOIN test_categories c ON t.category_id = c.id 
    ORDER BY t.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$categories = $conn->query("SELECT id, name FROM test_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Tests</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus"></i> Add New Test
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Test added successfully!</div>
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
                    <td><?php echo htmlspecialchars($test['name']); ?></td>
                    <td><?php echo htmlspecialchars($test['category_name']); ?></td>
                    <td>₹<?php echo number_format($test['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($test['sample_type']); ?></td>
                    <td><?php echo htmlspecialchars($test['reporting_time']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editTest(<?php echo $test['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTest(<?php echo $test['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
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
                            <label for="name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
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

<script>
function editTest(id) {
    // Implement edit functionality
    alert('Edit functionality will be implemented');
}

function deleteTest(id) {
    if(confirm('Are you sure you want to delete this test?')) {
        // Implement delete functionality
        alert('Delete functionality will be implemented');
    }
}
</script>

<?php include '../inc/footer.php'; ?> 