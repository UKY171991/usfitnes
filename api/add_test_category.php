<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in and has Admin role
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Validate required fields
    if (!isset($_POST['category_name']) || empty($_POST['category_name'])) {
        throw new Exception('Category name is required');
    }

    // Sanitize input
    $category_name = filter_var($_POST['category_name'], FILTER_SANITIZE_STRING);
    $branch_id = $_SESSION['branch_id'] ?? null;

    // Check if category already exists
    $exists = $db->query(
        "SELECT COUNT(*) FROM Test_Categories WHERE category_name = :name AND branch_id = :branch_id",
        ['name' => $category_name, 'branch_id' => $branch_id]
    )->fetchColumn();

    if ($exists) {
        throw new Exception('Category already exists');
    }

    // Insert category
    $sql = "INSERT INTO Test_Categories (category_name, branch_id, created_at) 
            VALUES (:name, :branch_id, NOW())";
    
    $stmt = $db->query($sql, [
        'name' => $category_name,
        'branch_id' => $branch_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Test category added successfully']);

} catch (Exception $e) {
    error_log("Add Test Category Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 