<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Base query to fetch test requests with related information
    $query = "
        SELECT 
            tr.request_id,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            tc.test_name,
            tr.ordered_by,
            tr.request_date,
            tr.status,
            tr.priority,
            tr.user_id,
            (tr.status != 'Completed' AND :user_role = 'Admin') as can_delete
        FROM Test_Requests tr
        JOIN Patients p ON tr.patient_id = p.patient_id
        JOIN Tests_Catalog tc ON tr.test_id = tc.test_id
        ORDER BY tr.request_date DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        'user_role' => $_SESSION['role'] ?? 'User'
    ]);
    
    $requests = $stmt->fetchAll();
    
    // Format dates and ensure boolean values are correct
    foreach ($requests as &$request) {
        $request['can_delete'] = (bool)$request['can_delete'];
        $request['request_date'] = date('Y-m-d H:i:s', strtotime($request['request_date']));
    }
    
    echo json_encode($requests);
    
} catch (Exception $e) {
    error_log("Error fetching test requests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch test requests']);
} 