<?php
// user_edit_api.php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$full_name = $_POST['full_name'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$user_type = $_POST['user_type'] ?? '';
$phone = $_POST['phone'] ?? '';
$department = $_POST['department'] ?? '';
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if (!$id || !$full_name || !$username || !$email || !$user_type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
    exit;
}

try {
    // Check for duplicate username or email (excluding current user)
    $stmt = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?');
    $stmt->execute([$username, $email, $id]);
    if ($stmt->fetch()) {
        throw new Exception('Username or email already exists');
    }

    // Update user
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, username = ?, email = ?, user_type = ?, phone = ?, department = ?, status = ?, password = ? WHERE id = ?');
        $stmt->execute([$full_name, $username, $email, $user_type, $phone, $department, $status, $password_hash, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, username = ?, email = ?, user_type = ?, phone = ?, department = ?, status = ? WHERE id = ?');
        $stmt->execute([$full_name, $username, $email, $user_type, $phone, $department, $status, $id]);
    }

    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
