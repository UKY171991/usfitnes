<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

$success_msg = '';
$error_msg = '';

// Function to generate unique branch code
function generateBranchCode($conn) {
    // Get the last branch code number
    $stmt = $conn->query("SELECT branch_code FROM branches WHERE branch_code LIKE 'BR%' ORDER BY id DESC LIMIT 1");
    $lastCode = $stmt->fetchColumn();
    
    if ($lastCode) {
        // Extract the number from the last code and increment it
        $number = intval(substr($lastCode, 2)) + 1;
    } else {
        // Start with 1 if no existing codes
        $number = 1;
    }
    
    // Generate new code with leading zeros (e.g., BR001, BR002)
    return 'BR' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Handle form submission for adding/editing branch
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_id = $_POST['branch_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name)) {
        $error_msg = "Branch name is required";
    } else {
        try {
            if (empty($branch_id)) {
                // Generate new branch code for new branches
                $branch_code = generateBranchCode($conn);
                
                // Add new branch
                $stmt = $conn->prepare("INSERT INTO branches (branch_code, branch_name, address, city, state, pincode, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$branch_code, $name, $address, $city, $state, $pincode, $phone, $email]);
                $success_msg = "Branch added successfully";
            } else {
                // Update existing branch - don't update branch_code
                $stmt = $conn->prepare("UPDATE branches SET branch_name = ?, address = ?, city = ?, state = ?, pincode = ?, phone = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $address, $city, $state, $pincode, $phone, $email, $branch_id]);
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
            $stmt = $conn->prepare("SELECT branch_name FROM branches WHERE id = ?");
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
    $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
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
                        <th>Branch Code</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Pincode</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($branches)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No branches found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($branches as $branch): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($branch['branch_code'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($branch['branch_name']); ?></td>
                                <td><?php echo htmlspecialchars($branch['address']); ?></td>
                                <td><?php echo htmlspecialchars($branch['city'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($branch['state'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($branch['pincode'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($branch['phone']); ?></td>
                                <td><?php echo htmlspecialchars($branch['email'] ?: '-'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-branch" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#branchModal"
                                            data-id="<?php echo $branch['id']; ?>"
                                            data-code="<?php echo htmlspecialchars($branch['branch_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($branch['branch_name']); ?>"
                                            data-address="<?php echo htmlspecialchars($branch['address']); ?>"
                                            data-city="<?php echo htmlspecialchars($branch['city']); ?>"
                                            data-state="<?php echo htmlspecialchars($branch['state']); ?>"
                                            data-pincode="<?php echo htmlspecialchars($branch['pincode']); ?>"
                                            data-phone="<?php echo htmlspecialchars($branch['phone']); ?>"
                                            data-email="<?php echo htmlspecialchars($branch['email']); ?>">
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
                    <?php if (!empty($_GET['edit'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Branch Code</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($branch['branch_code']); ?>" readonly>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Branch Name</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="pincode" class="form-label">Pincode</label>
                        <input type="text" class="form-control" id="pincode" name="pincode" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" maxlength="15">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="100">
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
            
            // Show branch code in readonly mode for existing branches
            const branchCodeHtml = `
                <div class="mb-3">
                    <label class="form-label">Branch Code</label>
                    <input type="text" class="form-control" value="${this.dataset.code}" readonly>
                </div>`;
            
            // Insert branch code field at the start of the form
            const firstField = modal.querySelector('.modal-body .mb-3');
            firstField.insertAdjacentHTML('beforebegin', branchCodeHtml);
            
            modal.querySelector('#name').value = this.dataset.name;
            modal.querySelector('#address').value = this.dataset.address;
            modal.querySelector('#city').value = this.dataset.city;
            modal.querySelector('#state').value = this.dataset.state;
            modal.querySelector('#pincode').value = this.dataset.pincode;
            modal.querySelector('#phone').value = this.dataset.phone;
            modal.querySelector('#email').value = this.dataset.email;
        });
    });

    // Reset form when adding new branch
    document.querySelector('[data-bs-target="#branchModal"]').addEventListener('click', function() {
        const modal = document.getElementById('branchModal');
        modal.querySelector('.modal-title').textContent = 'Add New Branch';
        modal.querySelector('form').reset();
        modal.querySelector('#branch_id').value = '';
        
        // Remove branch code field if it exists
        const branchCodeField = modal.querySelector('.modal-body .mb-3:first-child');
        if (branchCodeField && branchCodeField.querySelector('label').textContent === 'Branch Code') {
            branchCodeField.remove();
        }
    });
});
</script>

<?php include '../inc/footer.php'; ?> 