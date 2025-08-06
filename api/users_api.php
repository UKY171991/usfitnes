<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try to use working config first, fallback to regular config
if (file_exists('../config_working.php')) {
    require_once '../config_working.php';
} else {
    require_once '../config.php';
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Function to send JSON response
function sendResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Check database connection
    if (!isset($pdo) || !$pdo instanceof PDO) {
        sendResponse(false, 'Database connection failed');
    }
    
    // Check if user is logged in and has admin role (for user management)
    if ($action !== 'login' && $action !== 'register') {
        if (!isLoggedIn()) {
            sendResponse(false, 'Authentication required');
        }
        if (getUserRole() !== 'admin' && $action !== 'profile') {
            sendResponse(false, 'Admin access required');
        }
    }
    
    switch ($method) {
        case 'GET':
            handleGet($action);
            break;
        case 'POST':
            handlePost($action);
            break;
        case 'PUT':
            handlePut($action);
            break;
        case 'DELETE':
            handleDelete($action);
            break;
        default:
            sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Users API Error: ' . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleGet($action) {
    global $pdo;
    
    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, role, status, last_login, created_at 
                                      FROM users WHERE status != 'deleted' ORDER BY created_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll();
                
                // Format dates
                foreach ($users as &$user) {
                    $user['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                    $user['last_login_formatted'] = $user['last_login'] ? 
                        date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never';
                    $user['created_at_formatted'] = date('Y-m-d', strtotime($user['created_at']));
                }
                
                sendResponse(true, 'Users retrieved successfully', $users);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving users: ' . $e->getMessage());
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'User ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, role, status, last_login, created_at 
                                      FROM users WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $user['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                    sendResponse(true, 'User retrieved successfully', $user);
                } else {
                    sendResponse(false, 'User not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving user: ' . $e->getMessage());
            }
            break;
            
        case 'profile':
            $user_id = $_SESSION['user_id'] ?? 0;
            if (!$user_id) {
                sendResponse(false, 'Not logged in');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, role, status, last_login, created_at 
                                      FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $user['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                    sendResponse(true, 'Profile retrieved successfully', $user);
                } else {
                    sendResponse(false, 'User not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving profile: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePost($action) {
    global $pdo;
    
    switch ($action) {
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                sendResponse(false, 'Username and password are required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, password, first_name, last_name, role, status 
                                      FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                    
                    unset($user['password']); // Remove password from response
                    sendResponse(true, 'Login successful', $user);
                } else {
                    sendResponse(false, 'Invalid username or password');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error during login: ' . $e->getMessage());
            }
            break;
            
        case 'create':
            $required_fields = ['username', 'email', 'password', 'first_name', 'last_name'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $role = $_POST['role'] ?? 'staff';
            
            // Validate email
            if (!validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            // Validate password strength
            if (strlen($password) < 6) {
                sendResponse(false, 'Password must be at least 6 characters long');
            }
            
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND status != 'deleted'");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'Username already exists');
                }
                
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status != 'deleted'");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'Email address already exists');
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $role]);
                
                $id = $pdo->lastInsertId();
                sendResponse(true, 'User created successfully', ['id' => $id]);
                
            } catch (Exception $e) {
                sendResponse(false, 'Error creating user: ' . $e->getMessage());
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'User ID is required');
            }
            
            $required_fields = ['username', 'email', 'first_name', 'last_name'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $role = $_POST['role'] ?? 'staff';
            $status = $_POST['status'] ?? 'active';
            
            // Validate email
            if (!validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            try {
                // Check if user exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'User not found');
                }
                
                // Check if username already exists for another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ? AND status != 'deleted'");
                $stmt->execute([$username, $id]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'Username already exists');
                }
                
                // Check if email already exists for another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND status != 'deleted'");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'Email address already exists');
                }
                
                // Update user
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, 
                                      role = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$username, $email, $first_name, $last_name, $role, $status, $id]);
                
                sendResponse(true, 'User updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating user: ' . $e->getMessage());
            }
            break;
            
        case 'change_password':
            $id = $_POST['id'] ?? $_SESSION['user_id'] ?? 0;
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (!$id) {
                sendResponse(false, 'User ID is required');
            }
            
            if (empty($new_password)) {
                sendResponse(false, 'New password is required');
            }
            
            if ($new_password !== $confirm_password) {
                sendResponse(false, 'New password and confirmation do not match');
            }
            
            if (strlen($new_password) < 6) {
                sendResponse(false, 'Password must be at least 6 characters long');
            }
            
            try {
                // Get current user
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    sendResponse(false, 'User not found');
                }
                
                // Verify current password (skip for admin changing other user's password)
                if ($id == $_SESSION['user_id'] && !password_verify($current_password, $user['password'])) {
                    sendResponse(false, 'Current password is incorrect');
                }
                
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hashed_password, $id]);
                
                sendResponse(true, 'Password changed successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error changing password: ' . $e->getMessage());
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'User ID is required');
            }
            
            // Prevent deletion of current user
            if ($id == $_SESSION['user_id']) {
                sendResponse(false, 'Cannot delete your own account');
            }
            
            try {
                // Check if user exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'User not found');
                }
                
                // Soft delete - just change status
                $stmt = $pdo->prepare("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                sendResponse(true, 'User deleted successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error deleting user: ' . $e->getMessage());
            }
            break;
            
        case 'logout':
            session_destroy();
            sendResponse(true, 'Logged out successfully');
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePut($action) {
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost($action);
}

function handleDelete($action) {
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost('delete');
}
?>
