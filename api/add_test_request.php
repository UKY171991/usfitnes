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

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Validate required fields
    $required_fields = ['patient_id', 'test_id', 'ordered_by'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Sanitize input
    $patient_id = filter_var($_POST['patient_id'], FILTER_SANITIZE_NUMBER_INT);
    $test_id = filter_var($_POST['test_id'], FILTER_SANITIZE_NUMBER_INT);
    $ordered_by = filter_var($_POST['ordered_by'], FILTER_SANITIZE_STRING);
    $priority = filter_var($_POST['priority'] ?? 'Normal', FILTER_SANITIZE_STRING);
    $branch_id = $_SESSION['branch_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    // Validate numeric fields
    if (!is_numeric($patient_id) || !is_numeric($test_id)) {
        throw new Exception('Invalid patient ID or test ID');
    }

    // Validate priority
    if (!in_array($priority, ['Normal', 'Urgent'])) {
        $priority = 'Normal';
    }

    // Insert test request
    $sql = "INSERT INTO Test_Requests (patient_id, test_id, user_id, ordered_by, status, priority, branch_id, request_date) 
            VALUES (:patient_id, :test_id, :user_id, :ordered_by, 'Pending', :priority, :branch_id, NOW())";
    
    $params = [
        'patient_id' => $patient_id,
        'test_id' => $test_id,
        'user_id' => $user_id,
        'ordered_by' => $ordered_by,
        'priority' => $priority,
        'branch_id' => $branch_id
    ];

    $stmt = $db->query($sql, $params);
    
    echo json_encode(['success' => true, 'message' => 'Test request added successfully']);

} catch (Exception $e) {
    error_log("Add Test Request Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 