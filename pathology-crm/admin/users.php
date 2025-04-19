<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

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
    SELECT u.*, b.name as branch_name 
    FROM users u 
    LEFT JOIN branches b ON u.branch_id = b.id 
    ORDER BY u.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for dropdown
$branches = $conn->query("SELECT id, name FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus"></i> Add New User
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">User added successfully!</div>
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
                        <button class="btn btn-sm btn-info" onclick="editUser(<?php echo $user['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
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

<script>
function editUser(id) {
    // Implement edit functionality
    alert('Edit functionality will be implemented');
}

function deleteUser(id) {
    if(confirm('Are you sure you want to delete this user?')) {
        // Implement delete functionality
        alert('Delete functionality will be implemented');
    }
}
</script>

<?php include '../inc/footer.php'; ?> 