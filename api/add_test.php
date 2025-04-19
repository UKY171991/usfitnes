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
    $required_fields = ['category_id', 'test_name', 'test_code', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Sanitize input
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $test_name = filter_var($_POST['test_name'], FILTER_SANITIZE_STRING);
    $test_code = filter_var($_POST['test_code'], FILTER_SANITIZE_STRING);
    $parameters = filter_var($_POST['parameters'] ?? '', FILTER_SANITIZE_STRING);
    $reference_range = filter_var($_POST['reference_range'] ?? '', FILTER_SANITIZE_STRING);
    $normal_range = filter_var($_POST['normal_range'] ?? '', FILTER_SANITIZE_STRING);
    $unit = filter_var($_POST['unit'] ?? '', FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $branch_id = $_SESSION['branch_id'] ?? null;

    // Validate numeric fields
    if (!is_numeric($category_id) || !is_numeric($price)) {
        throw new Exception('Invalid category ID or price');
    }

    // Check if test code already exists
    $exists = $db->query(
        "SELECT COUNT(*) FROM Tests_Catalog WHERE test_code = :code AND branch_id = :branch_id",
        ['code' => $test_code, 'branch_id' => $branch_id]
    )->fetchColumn();

    if ($exists) {
        throw new Exception('Test code already exists');
    }

    // Insert test
    $sql = "INSERT INTO Tests_Catalog (category_id, test_name, test_code, parameters, reference_range, 
            normal_range, unit, price, branch_id, created_at) 
            VALUES (:category_id, :test_name, :test_code, :parameters, :reference_range, 
            :normal_range, :unit, :price, :branch_id, NOW())";
    
    $params = [
        'category_id' => $category_id,
        'test_name' => $test_name,
        'test_code' => $test_code,
        'parameters' => $parameters,
        'reference_range' => $reference_range,
        'normal_range' => $normal_range,
        'unit' => $unit,
        'price' => $price,
        'branch_id' => $branch_id
    ];

    $stmt = $db->query($sql, $params);
    
    echo json_encode(['success' => true, 'message' => 'Test added successfully']);

} catch (Exception $e) {
    error_log("Add Test Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 