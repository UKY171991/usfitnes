<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if(!empty($name) && !empty($address)) {
        try {
            $stmt = $conn->prepare("INSERT INTO branches (name, address, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $address, $phone, $email]);
            
            // Log activity
            $activity = "New branch added: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: branches.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding branch: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all branches
$branches = $conn->query("SELECT * FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Branches</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
        <i class="fas fa-plus"></i> Add New Branch
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Branch added successfully!</div>
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
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($branches as $branch): ?>
                <tr>
                    <td><?php echo $branch['id']; ?></td>
                    <td><?php echo htmlspecialchars($branch['name']); ?></td>
                    <td><?php echo htmlspecialchars($branch['address']); ?></td>
                    <td><?php echo htmlspecialchars($branch['phone']); ?></td>
                    <td><?php echo htmlspecialchars($branch['email']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editBranch(<?php echo $branch['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteBranch(<?php echo $branch['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Branch Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBranch(id) {
    // Implement edit functionality
    alert('Edit functionality will be implemented');
}

function deleteBranch(id) {
    if(confirm('Are you sure you want to delete this branch?')) {
        // Implement delete functionality
        alert('Delete functionality will be implemented');
    }
}
</script>

<?php include '../inc/footer.php'; ?> 