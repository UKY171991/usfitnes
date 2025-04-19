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

// Verify CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();

    // Get request details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $stmt = $db->prepare("
            SELECT r.*, p.first_name, p.last_name, t.test_name
            FROM Test_Requests r
            JOIN Patients p ON r.patient_id = p.patient_id
            JOIN Tests_Catalog t ON r.test_id = t.test_id
            WHERE r.request_id = ?
        ");
        $stmt->execute([$_GET['request_id']]);
        $request = $stmt->fetch();
        
        if ($request) {
            echo json_encode($request);
        } else {
            echo json_encode(['error' => 'Request not found']);
        }
        exit;
    }

    // Delete request
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Check if user has admin role
        if ($_SESSION['role'] !== 'Admin') {
            throw new Exception('Only administrators can delete test requests');
        }

        // Check if request exists and is not completed
        $stmt = $db->prepare("SELECT status FROM Test_Requests WHERE request_id = ?");
        $stmt->execute([$_POST['request_id']]);
        $request = $stmt->fetch();

        if (!$request) {
            throw new Exception('Request not found');
        }

        if ($request['status'] === 'Completed') {
            throw new Exception('Cannot delete completed test requests');
        }

        // Delete the request
        $stmt = $db->prepare("DELETE FROM Test_Requests WHERE request_id = ?");
        $stmt->execute([$_POST['request_id']]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Request deleted successfully']);
        exit;
    }

    // Add new request
    if (!isset($_POST['action'])) {
        // Validate required fields
        $required_fields = ['patient_id', 'test_id', 'ordered_by', 'priority'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Insert new request
        $stmt = $db->prepare("
            INSERT INTO Test_Requests (
                patient_id, test_id, ordered_by, priority,
                status, user_id, request_date
            ) VALUES (
                ?, ?, ?, ?,
                'Pending', ?, NOW()
            )
        ");

        $stmt->execute([
            $_POST['patient_id'],
            $_POST['test_id'],
            $_POST['ordered_by'],
            $_POST['priority'],
            $_SESSION['user_id']
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Test request added successfully']);
        exit;
    }

    // Update request
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Validate required fields
        if (!isset($_POST['request_id']) || !isset($_POST['status']) || !isset($_POST['priority'])) {
            throw new Exception('Missing required fields for update');
        }

        // Update request
        $stmt = $db->prepare("
            UPDATE Test_Requests 
            SET status = ?, 
                priority = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE request_id = ?
        ");

        $stmt->execute([
            $_POST['status'],
            $_POST['priority'],
            $_SESSION['user_id'],
            $_POST['request_id']
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Test request updated successfully']);
        exit;
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Error in process_request.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
} 