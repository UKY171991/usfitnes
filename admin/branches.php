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
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Branch Code</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($branches)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <p>No branches found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($branches as $branch): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($branch['branch_code'] ?: '-'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($branch['branch_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($branch['email'] ?: 'No email'); ?></small>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($branch['phone']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($branch['email'] ?: 'No email'); ?></small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($branch['address']); ?>">
                                        <?php echo htmlspecialchars($branch['address']); ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php 
                                        $location = array_filter([
                                            $branch['city'],
                                            $branch['state'],
                                            $branch['pincode']
                                        ]);
                                        echo htmlspecialchars(implode(', ', $location) ?: 'No location details');
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $branch['status'] ? 'success' : 'danger'; ?>">
                                        <?php echo $branch['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-branch" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewBranchModal"
                                                data-id="<?php echo $branch['id']; ?>"
                                                data-code="<?php echo htmlspecialchars($branch['branch_code']); ?>"
                                                data-name="<?php echo htmlspecialchars($branch['branch_name']); ?>"
                                                data-address="<?php echo htmlspecialchars($branch['address']); ?>"
                                                data-city="<?php echo htmlspecialchars($branch['city']); ?>"
                                                data-state="<?php echo htmlspecialchars($branch['state']); ?>"
                                                data-pincode="<?php echo htmlspecialchars($branch['pincode']); ?>"
                                                data-phone="<?php echo htmlspecialchars($branch['phone']); ?>"
                                                data-email="<?php echo htmlspecialchars($branch['email']); ?>"
                                                data-status="<?php echo $branch['status']; ?>"
                                                title="View Branch">
                                            <i class="fas fa-eye"></i>
                                        </button>
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
                                                data-email="<?php echo htmlspecialchars($branch['email']); ?>"
                                                title="Edit Branch">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $branch['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this branch?')"
                                           title="Delete Branch">
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

<!-- View Branch Modal -->
<div class="modal fade" id="viewBranchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Branch Code</dt>
                                    <dd class="col-sm-8" id="view-branch-code">-</dd>
                                    
                                    <dt class="col-sm-4">Branch Name</dt>
                                    <dd class="col-sm-8" id="view-branch-name">-</dd>
                                    
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-branch-status">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Contact Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8" id="view-branch-phone">-</dd>
                                    
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8" id="view-branch-email">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Location Details</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-2">Address</dt>
                                    <dd class="col-sm-10" id="view-branch-address">-</dd>
                                    
                                    <dt class="col-sm-2">City</dt>
                                    <dd class="col-sm-4" id="view-branch-city">-</dd>
                                    
                                    <dt class="col-sm-2">State</dt>
                                    <dd class="col-sm-4" id="view-branch-state">-</dd>
                                    
                                    <dt class="col-sm-2">Pincode</dt>
                                    <dd class="col-sm-4" id="view-branch-pincode">-</dd>
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

<!-- Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="branch_id" id="branch_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Branch Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                            <div class="invalid-feedback">Please enter branch name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required maxlength="15">
                            <div class="invalid-feedback">Please enter phone number</div>
                        </div>
                        <div class="col-md-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" maxlength="10">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100">
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
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
    // Form validation
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

    // Handle view branch button clicks
    document.querySelectorAll('.view-branch').forEach(button => {
        button.addEventListener('click', function() {
            // Update view modal content
            document.getElementById('view-branch-code').textContent = this.dataset.code || '-';
            document.getElementById('view-branch-name').textContent = this.dataset.name || '-';
            document.getElementById('view-branch-status').innerHTML = `
                <span class="badge bg-${this.dataset.status == 1 ? 'success' : 'danger'}">
                    ${this.dataset.status == 1 ? 'Active' : 'Inactive'}
                </span>
            `;
            document.getElementById('view-branch-phone').textContent = this.dataset.phone || '-';
            document.getElementById('view-branch-email').textContent = this.dataset.email || '-';
            document.getElementById('view-branch-address').textContent = this.dataset.address || '-';
            document.getElementById('view-branch-city').textContent = this.dataset.city || '-';
            document.getElementById('view-branch-state').textContent = this.dataset.state || '-';
            document.getElementById('view-branch-pincode').textContent = this.dataset.pincode || '-';
        });
    });

    // Handle edit branch button clicks
    document.querySelectorAll('.edit-branch').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('branchModal');
            modal.querySelector('.modal-title').textContent = 'Edit Branch';
            modal.querySelector('#branch_id').value = this.dataset.id;
            
            // Show branch code in readonly mode for existing branches
            const branchCodeHtml = `
                <div class="col-md-6">
                    <label class="form-label">Branch Code</label>
                    <input type="text" class="form-control" value="${this.dataset.code}" readonly>
                </div>`;
            
            // Insert branch code field at the start of the form
            const firstField = modal.querySelector('.modal-body .row');
            firstField.insertAdjacentHTML('afterbegin', branchCodeHtml);
            
            // Fill form fields
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
        modal.querySelector('form').classList.remove('was-validated');
        
        // Remove branch code field if it exists
        const branchCodeField = modal.querySelector('.modal-body .row .col-md-6:first-child');
        if (branchCodeField && branchCodeField.querySelector('label').textContent === 'Branch Code') {
            branchCodeField.remove();
        }
    });
});
</script>

<?php include '../inc/footer.php'; ?> 