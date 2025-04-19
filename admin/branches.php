<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

$success_msg = '';
$error_msg = '';

// Handle form submission for adding/editing branch
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_id = $_POST['branch_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if (empty($name)) {
        $error_msg = "Branch name is required";
    } else {
        try {
            if (empty($branch_id)) {
                // Add new branch
                $stmt = $conn->prepare("INSERT INTO branches (name, address, contact_number) VALUES (?, ?, ?)");
                $stmt->execute([$name, $address, $contact_number]);
                $success_msg = "Branch added successfully";
            } else {
                // Update existing branch
                $stmt = $conn->prepare("UPDATE branches SET name = ?, address = ?, contact_number = ? WHERE id = ?");
                $stmt->execute([$name, $address, $contact_number, $branch_id]);
                $success_msg = "Branch updated successfully";
            }

            // Log activity
            $activity = empty($branch_id) ? "Added new branch: $name" : "Updated branch: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);

        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// Handle branch deletion
if (isset($_GET['delete'])) {
    try {
        $branch_id = $_GET['delete'];
        
        // Check if branch has any associated users
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE branch_id = ?");
        $stmt->execute([$branch_id]);
        $user_count = $stmt->fetchColumn();

        if ($user_count > 0) {
            $error_msg = "Cannot delete branch: There are users associated with this branch";
        } else {
            // Get branch name for activity log
            $stmt = $conn->prepare("SELECT name FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);
            $branch_name = $stmt->fetchColumn();

            // Delete the branch
            $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);

            // Log activity
            $activity = "Deleted branch: $branch_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);

            $success_msg = "Branch deleted successfully";
        }
    } catch (PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Fetch all branches
try {
    $branches = $conn->query("SELECT * FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Error fetching branches: " . $e->getMessage();
    $branches = [];
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Branches</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#branchModal">
        <i class="fas fa-plus"></i> Add New Branch
    </button>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Branches Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($branches)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No branches found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($branches as $branch): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($branch['name']); ?></td>
                                <td><?php echo htmlspecialchars($branch['address']); ?></td>
                                <td><?php echo htmlspecialchars($branch['contact_number']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-branch" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#branchModal"
                                            data-id="<?php echo $branch['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($branch['name']); ?>"
                                            data-address="<?php echo htmlspecialchars($branch['address']); ?>"
                                            data-contact="<?php echo htmlspecialchars($branch['contact_number']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $branch['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this branch?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="branch_id" id="branch_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Branch Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit branch button clicks
    document.querySelectorAll('.edit-branch').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('branchModal');
            modal.querySelector('.modal-title').textContent = 'Edit Branch';
            modal.querySelector('#branch_id').value = this.dataset.id;
            modal.querySelector('#name').value = this.dataset.name;
            modal.querySelector('#address').value = this.dataset.address;
            modal.querySelector('#contact_number').value = this.dataset.contact;
        });
    });

    // Reset modal on close
    document.getElementById('branchModal').addEventListener('hidden.bs.modal', function() {
        this.querySelector('.modal-title').textContent = 'Add New Branch';
        this.querySelector('form').reset();
        this.querySelector('#branch_id').value = '';
    });
});
</script>

<?php include '../inc/footer.php'; ?> 