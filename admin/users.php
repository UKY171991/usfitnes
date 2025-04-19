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
    
    if(!empty($name) && !empty($role)) {
        try {
            $stmt = $conn->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, role = ?, branch_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $role, $branch_id, $user_id]);
            
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

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch_id = $_POST['branch_id'] ?? null;
    
    if(!empty($username) && !empty($password) && !empty($name) && !empty($role)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO users (username, password, name, email, phone, role, branch_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $hashed_password, $name, $email, $phone, $role, $branch_id]);
            
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

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Branch</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                    <td><?php echo htmlspecialchars($user['branch_name'] ?? 'N/A'); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editUser(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $user['id'],
                                'name' => $user['name'],
                                'email' => $user['email'],
                                'phone' => $user['phone'],
                                'role' => $user['role'],
                                'branch_id' => $user['branch_id']
                            ])); 
                        ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="delete_user" value="1">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
            <form method="POST" action="">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role *</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="branch_admin">Branch Admin</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="technician">Technician</option>
                        </select>
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
function editUser(userData) {
    // Populate the edit modal with user data
    document.getElementById('edit_user_id').value = userData.id;
    document.getElementById('edit_name').value = userData.name;
    document.getElementById('edit_email').value = userData.email || '';
    document.getElementById('edit_phone').value = userData.phone || '';
    document.getElementById('edit_role').value = userData.role;
    document.getElementById('edit_branch_id').value = userData.branch_id || '';
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>

<?php include '../inc/footer.php'; ?> 