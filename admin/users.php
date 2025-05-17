<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle delete request
if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Log activity
        $activity = "User deleted: ID $user_id";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
        
        header("Location: users.php?success=2");
        exit();
    } catch(PDOException $e) {
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch_id = $_POST['branch_id'] ?? null;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $password = $_POST['password'] ?? '';

    if(!empty($name) && !empty($role)) {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, role = ?, branch_id = ?, status = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $role, $branch_id, $status, $hashed_password, $user_id]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, role = ?, branch_id = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $role, $branch_id, $status, $user_id]);
            }
            // Log activity
            $activity = "User updated: ID $user_id";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            header("Location: users.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Handle form submission (add user)
if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['name']) && isset($_POST['role'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch_id = $_POST['branch_id'] ?? null;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    if(!empty($username) && !empty($password) && !empty($name) && !empty($role)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (username, password, name, email, phone, role, branch_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $hashed_password, $name, $email, $phone, $role, $branch_id, $status]);
            // Log activity
            $activity = "New user added: $username";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            header("Location: users.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all users with branch names
$users = $conn->query("
    SELECT u.*, b.branch_name as branch_name 
    FROM users u 
    LEFT JOIN branches b ON u.branch_id = b.id 
    ORDER BY u.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for dropdown
$branches = $conn->query("SELECT id, branch_name as name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus"></i> Add New User
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
            switch($_GET['success']) {
                case 1:
                    echo "User added successfully!";
                    break;
                case 2:
                    echo "User deleted successfully!";
                    break;
                case 3:
                    echo "User updated successfully!";
                    break;
            }
        ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php // DEBUG: Show users array for troubleshooting
// if (isset($_GET['debug'])) { echo '<pre>'; print_r($users); echo '</pre>'; }
?>
<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Sr. No.</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>No users found</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php $sr = 1; foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $sr++; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-<?php echo getRoleBadgeColor($user['role']); ?>"><?php echo formatRole($user['role']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['branch_name'] ?? '-'); ?></td>
                                <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                <td><span class="badge bg-<?php echo $user['status'] ? 'success' : 'danger'; ?>"><?php echo $user['status'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-user" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewUserModal"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                data-branch="<?php echo htmlspecialchars($user['branch_name']); ?>"
                                                data-status="<?php echo $user['status']; ?>"
                                                data-last-login="<?php echo $user['last_login']; ?>"
                                                title="View User">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-user" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                data-branch="<?php echo $user['branch_id']; ?>"
                                                data-status="<?php echo $user['status']; ?>"
                                                title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this user?')"
                                           title="Delete User">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="master_admin">Master Admin</option>
                            <option value="admin">Admin</option>
                            <option value="branch_admin">Branch Admin</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="technician">Technician</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-control" id="branch_id" name="branch_id">
                            <option value="">Select Branch</option>
                            <?php foreach($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required readonly>
                        <div class="form-text">Username cannot be changed</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <div class="form-text">Leave blank to keep current password</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback">Please enter full name</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                        <div class="invalid-feedback">Please enter a valid email</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role *</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="master_admin">Master Admin</option>
                            <option value="admin">Admin</option>
                            <option value="branch_admin">Branch Admin</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="technician">Technician</option>
                        </select>
                        <div class="invalid-feedback">Please select a role</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_branch_id" class="form-label">Branch</label>
                        <select class="form-control" id="edit_branch_id" name="branch_id">
                            <option value="">Select Branch</option>
                            <?php foreach($branches as $branch): ?>
                                <option value="<?php echo $branch['id']; ?>">
                                    <?php echo htmlspecialchars($branch['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-control" id="edit_status" name="status">
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8" id="view-user-name">-</dd>
                                    
                                    <dt class="col-sm-4">Username</dt>
                                    <dd class="col-sm-8" id="view-user-username">-</dd>
                                    
                                    <dt class="col-sm-4">Role</dt>
                                    <dd class="col-sm-8" id="view-user-role">-</dd>
                                    
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-user-status">-</dd>
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
                                    <dd class="col-sm-8" id="view-user-phone">-</dd>
                                    
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8" id="view-user-email">-</dd>
                                    
                                    <dt class="col-sm-4">Branch</dt>
                                    <dd class="col-sm-8" id="view-user-branch">-</dd>
                                    
                                    <dt class="col-sm-4">Last Login</dt>
                                    <dd class="col-sm-8" id="view-user-last-login">-</dd>
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

    // Handle view user button clicks
    document.querySelectorAll('.view-user').forEach(button => {
        button.addEventListener('click', function() {
            // Update view modal content
            document.getElementById('view-user-name').textContent = this.dataset.name || '-';
            document.getElementById('view-user-username').textContent = this.dataset.username || '-';
            document.getElementById('view-user-role').innerHTML = `
                <span class="badge bg-${getRoleBadgeColor(this.dataset.role)}">
                    ${formatRole(this.dataset.role)}
                </span>
            `;
            document.getElementById('view-user-status').innerHTML = `
                <span class="badge bg-${this.dataset.status == 1 ? 'success' : 'danger'}">
                    ${this.dataset.status == 1 ? 'Active' : 'Inactive'}
                </span>
            `;
            document.getElementById('view-user-phone').textContent = this.dataset.phone || '-';
            document.getElementById('view-user-email').textContent = this.dataset.email || '-';
            document.getElementById('view-user-branch').textContent = this.dataset.branch || '-';
            document.getElementById('view-user-last-login').textContent = this.dataset.lastLogin || 'Never';
        });
    });

    // Handle edit user button clicks
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editUserModal');
            modal.querySelector('.modal-title').textContent = 'Edit User';
            
            // Fill form fields
            document.getElementById('edit_user_id').value = this.dataset.id;
            document.getElementById('edit_username').value = this.dataset.username;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_email').value = this.dataset.email || '';
            document.getElementById('edit_phone').value = this.dataset.phone || '';
            document.getElementById('edit_role').value = this.dataset.role;
            document.getElementById('edit_branch_id').value = this.dataset.branch || '';
            document.getElementById('edit_status').value = this.dataset.status;
            
            // Clear password field
            document.getElementById('edit_password').value = '';
            
            // Show the modal
            const editModal = new bootstrap.Modal(modal);
            editModal.show();
        });
    });

    // Helper function to get role badge color
    function getRoleBadgeColor(role) {
        return {
            'master_admin': 'danger',
            'branch_admin': 'primary',
            'receptionist': 'info',
            'technician': 'warning'
        }[role] || 'secondary';
    }

    // Helper function to format role display
    function formatRole(role) {
        return role.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }
});
</script>

<?php include '../inc/footer.php'; ?>