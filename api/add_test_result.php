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

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !in_array($_SESSION['role'], ['Admin', 'Technician'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Validate required fields
    $required_fields = ['request_id', 'result_value'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Sanitize input
    $request_id = filter_var($_POST['request_id'], FILTER_SANITIZE_NUMBER_INT);
    $result_value = filter_var($_POST['result_value'], FILTER_SANITIZE_STRING);
    $comments = filter_var($_POST['comments'] ?? '', FILTER_SANITIZE_STRING);
    $recorded_by = $_SESSION['user_id'];

    // Validate numeric fields
    if (!is_numeric($request_id)) {
        throw new Exception('Invalid request ID');
    }

    // Start transaction
    $db->beginTransaction();

    try {
        // Insert test result
        $sql = "INSERT INTO Test_Results (request_id, result_value, comments, recorded_by, recorded_at) 
                VALUES (:request_id, :result_value, :comments, :recorded_by, NOW())";
        
        $params = [
            'request_id' => $request_id,
            'result_value' => $result_value,
            'comments' => $comments,
            'recorded_by' => $recorded_by
        ];

        $stmt = $db->query($sql, $params);

        // Update test request status
        $sql = "UPDATE Test_Requests SET status = 'Completed' WHERE request_id = :request_id";
        $db->query($sql, ['request_id' => $request_id]);

        // If there are sub-test results
        if (isset($_POST['sub_tests']) && is_array($_POST['sub_tests'])) {
            $result_id = $db->lastInsertId();
            
            foreach ($_POST['sub_tests'] as $sub_test) {
                if (empty($sub_test['name']) || empty($sub_test['value'])) {
                    continue;
                }

                $sql = "INSERT INTO test_result_details (result_id, sub_test_name, result_value, unit, normal_range) 
                        VALUES (:result_id, :name, :value, :unit, :range)";
                
                $db->query($sql, [
                    'result_id' => $result_id,
                    'name' => filter_var($sub_test['name'], FILTER_SANITIZE_STRING),
                    'value' => filter_var($sub_test['value'], FILTER_SANITIZE_STRING),
                    'unit' => filter_var($sub_test['unit'] ?? '', FILTER_SANITIZE_STRING),
                    'range' => filter_var($sub_test['range'] ?? '', FILTER_SANITIZE_STRING)
                ]);
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Test result added successfully']);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Add Test Result Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 