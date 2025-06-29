<?php
// user_add_api.php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$full_name = $_POST['full_name'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$user_type = $_POST['user_type'] ?? '';
$phone = $_POST['phone'] ?? '';
$department = $_POST['department'] ?? '';
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if (!$full_name || !$username || !$email || !$user_type || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
    exit;
}

try {
    // Check for duplicate username or email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        throw new Exception('Username or email already exists');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, username, email, user_type, phone, department, status, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$full_name, $username, $email, $user_type, $phone, $department, $status, $password_hash]);

    echo json_encode(['success' => true, 'message' => 'User added successfully', 'user_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
