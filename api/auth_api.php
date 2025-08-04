<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            if (isset($_POST['action']) || isset($input['action'])) {
                $action = $_POST['action'] ?? $input['action'];
                switch ($action) {
                    case 'login':
                        handleLogin($pdo, $input);
                        break;
                    case 'register':
                        handleRegister($pdo, $input);
                        break;
                    case 'logout':
                        handleLogout();
                        break;
                    case 'check_session':
                        checkSession();
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid action']);
                        break;
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Action parameter required']);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleLogin($pdo, $input) {
    // Validate required fields
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        return;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password cannot be empty']);
        return;
    }
    try {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT id, username, password, name, user_type FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['role'] = $user['user_type']; // Ensure both 'role' and 'user_type' are set for compatibility
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['name'],
                    'role' => $user['user_type']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleRegister($pdo, $input) {
    // Validate required fields
    $requiredFields = ['username', 'password', 'full_name', 'email'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    $full_name = trim($input['full_name']);
    $email = trim($input['email']);
    $role = isset($input['role']) ? $input['role'] : 'user';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        return;
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            return;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }
        
        // Hash password and insert user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, email, user_type)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$username, $password_hash, $full_name, $email, $role]);
        $userId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'data' => ['user_id' => $userId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleLogout() {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout successful']);
}

function checkSession() {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'data' => [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}
?>
