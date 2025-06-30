<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch($action) {
        case 'get_users':
            $stmt = $pdo->query("
                SELECT u.id, u.username, u.email, u.full_name, u.user_type, u.created_at,
                       'active' as status, 
                       NULL as phone,
                       NULL as department,
                       NULL as last_login,
                       DATE_FORMAT(u.created_at, '%M %d, %Y') as formatted_date
                FROM users u 
                ORDER BY u.created_at DESC
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;
            
        case 'get_user':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $pdo->prepare("
                SELECT u.id, u.username, u.email, u.full_name, u.user_type, u.created_at,
                       'active' as status, 
                       NULL as phone,
                       NULL as department,
                       NULL as last_login,
                       DATE_FORMAT(u.created_at, '%M %d, %Y at %h:%i %p') as formatted_date
                FROM users u 
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
            break;
            
        case 'create_user':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $user_type = $_POST['user_type'] ?? 'lab_technician';
            $password = $_POST['password'] ?? '';
            
            if (!$username || !$email || !$full_name || !$password) {
                throw new Exception('All required fields must be filled');
            }
            
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                throw new Exception('Username or email already exists');
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, full_name, user_type, password) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $full_name, $user_type, $password_hash]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_user':
            $id = $_POST['id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $user_type = $_POST['user_type'] ?? 'lab_technician';
            $password = $_POST['password'] ?? '';
            
            if (!$id || !$username || !$email || !$full_name) {
                throw new Exception('All required fields must be filled');
            }
            
            // Check if username or email already exists for other users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Username or email already exists');
            }
            
            // Update with or without password
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, full_name = ?, user_type = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $full_name, $user_type, $password_hash, $id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, full_name = ?, user_type = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $full_name, $user_type, $id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            break;
            
        case 'delete_user':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                throw new Exception('User ID is required');
            }
            
            // Don't allow deleting yourself
            if ($id == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }
            
            // Skip checking associated records since patients table doesn't have created_by column
            // In a production system, you would check for any records created by this user
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            break;
            
        case 'change_password':
            $id = $_POST['id'] ?? 0;
            $new_password = $_POST['new_password'] ?? '';
            
            if (!$id || !$new_password) {
                throw new Exception('User ID and new password are required');
            }
            
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$password_hash, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            break;
            
        case 'get_stats':
            // Get user statistics
            $stats = [];
            
            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Active users (assuming all users are active since no status column)
            $stats['active_users'] = $stats['total_users'];
            
            // Users by role
            $stmt = $pdo->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
            $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent users (last 30 days)
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['recent_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
