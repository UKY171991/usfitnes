<?php
/**
 * Users API - AdminLTE3 AJAX Handler
 */

// Get the correct path to config.php
$config_path = dirname(__DIR__) . '/config.php';
require_once $config_path;

header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            echo json_encode(getUsersList());
            break;
            
        case 'add_form':
            echo getUserForm();
            break;
            
        case 'edit_form':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getUserForm($id);
            break;
            
        case 'view':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getUserView($id);
            break;
            
        case 'save':
            echo json_encode(saveUser());
            break;
            
        case 'delete':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo json_encode(deleteUser($id));
            break;
            
        default:
            echo json_encode(getUsersList());
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getUsersList() {
    try {
        $conn = getDatabaseConnection();
        
        $draw = (int)($_POST['draw'] ?? 1);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 25);
        $search = $_POST['search']['value'] ?? '';
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR name LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE {$where}");
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        $sql = "SELECT id, username, name, email, user_type, created_at FROM users WHERE {$where} ORDER BY id DESC LIMIT {$start}, {$length}";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
        
    } catch (Exception $e) {
        return [
            'draw' => 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ];
    }
}

function getUserForm($id = 0) {
    $user = null;
    $title = 'Add New User';
    
    if ($id > 0) {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = 'Edit User';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= $title ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <form id="userForm" action="api/users_api.php" method="POST" onsubmit="return saveUser()">
        <div class="modal-body">
            <input type="hidden" name="action" value="save">
            <?php if ($user): ?>
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" class="form-control" name="username" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>User Type</label>
                        <select class="form-control" name="user_type">
                            <option value="admin" <?= ($user && $user['user_type'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="lab_technician" <?= ($user && $user['user_type'] == 'lab_technician') ? 'selected' : '' ?>>Lab Technician</option>
                            <option value="receptionist" <?= ($user && $user['user_type'] == 'receptionist') ? 'selected' : '' ?>>Receptionist</option>
                            <option value="doctor" <?= ($user && $user['user_type'] == 'doctor') ? 'selected' : '' ?>>Doctor</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <?php if (!$user): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save User</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

function getUserView($id) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return '<div class="modal-body">User not found.</div>';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title">User Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <table class="table table-striped">
            <tr><th width="150">ID:</th><td><?= $user['id'] ?></td></tr>
            <tr><th>Username:</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
            <tr><th>Name:</th><td><?= htmlspecialchars($user['name']) ?></td></tr>
            <tr><th>Email:</th><td><?= htmlspecialchars($user['email'] ?: 'N/A') ?></td></tr>
            <tr><th>Phone:</th><td><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></td></tr>
            <tr><th>User Type:</th><td><span class="badge badge-info"><?= strtoupper(str_replace('_', ' ', $user['user_type'])) ?></span></td></tr>
            <tr><th>Created:</th><td><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></td></tr>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="editUser(<?= $user['id'] ?>)">Edit</button>
    </div>
    <?php
    return ob_get_clean();
}

function saveUser() {
    try {
        $conn = getDatabaseConnection();
        
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $user_type = $_POST['user_type'] ?? 'lab_technician';
        $password = trim($_POST['password'] ?? '');
        
        if (empty($username)) {
            throw new Exception('Username is required');
        }
        
        if (empty($name)) {
            throw new Exception('Full name is required');
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Check username uniqueness
        if ($id > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $id]);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Username already exists');
        }
        
        if ($id > 0) {
            $sql = "UPDATE users SET username = ?, name = ?, email = ?, phone = ?, user_type = ? WHERE id = ?";
            $params = [$username, $name, $email, $phone, $user_type, $id];
            $message = 'User updated successfully';
        } else {
            if (empty($password)) {
                throw new Exception('Password is required for new users');
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, name, email, phone, user_type, password) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$username, $name, $email, $phone, $user_type, $hashedPassword];
            $message = 'User created successfully';
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return [
            'success' => true,
            'message' => $message
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function deleteUser($id) {
    try {
        $conn = getDatabaseConnection();
        
        // Don't allow deletion of admin user
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $userType = $stmt->fetchColumn();
        
        if ($userType === 'admin') {
            throw new Exception('Cannot delete admin user');
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>