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
    error_log('Results API Error: ' . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleGet($action) {
    global $pdo;
    
    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("
                    SELECT r.*, 
                           p.first_name, p.last_name, p.phone,
                           o.order_number, o.test_type as order_test_type
                    FROM test_results r 
                    LEFT JOIN patients p ON r.patient_id = p.id 
                    LEFT JOIN test_orders o ON r.order_id = o.id 
                    WHERE r.status != 'deleted' 
                    ORDER BY r.result_date DESC
                ");
                $stmt->execute();
                $results = $stmt->fetchAll();
                
                // Format the data
                foreach ($results as &$result) {
                    $result['patient_name'] = $result['first_name'] . ' ' . $result['last_name'];
                    $result['result_date_formatted'] = date('Y-m-d H:i', strtotime($result['result_date']));
                }
                
                sendResponse(true, 'Test results retrieved successfully', $results);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving test results: ' . $e->getMessage());
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Result ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("
                    SELECT r.*, 
                           p.first_name, p.last_name, p.phone, p.email,
                           o.order_number, o.test_type as order_test_type,
                           u.first_name as reviewer_first_name, u.last_name as reviewer_last_name
                    FROM test_results r 
                    LEFT JOIN patients p ON r.patient_id = p.id 
                    LEFT JOIN test_orders o ON r.order_id = o.id 
                    LEFT JOIN users u ON r.reviewed_by = u.id
                    WHERE r.id = ? AND r.status != 'deleted'
                ");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $result['patient_name'] = $result['first_name'] . ' ' . $result['last_name'];
                    $result['reviewer_name'] = $result['reviewer_first_name'] ? 
                        $result['reviewer_first_name'] . ' ' . $result['reviewer_last_name'] : null;
                    sendResponse(true, 'Test result retrieved successfully', $result);
                } else {
                    sendResponse(false, 'Test result not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving test result: ' . $e->getMessage());
            }
            break;
            
        case 'by_patient':
            $patient_id = $_GET['patient_id'] ?? 0;
            if (!$patient_id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("
                    SELECT r.*, o.order_number, o.test_type as order_test_type
                    FROM test_results r 
                    LEFT JOIN test_orders o ON r.order_id = o.id 
                    WHERE r.patient_id = ? AND r.status != 'deleted' 
                    ORDER BY r.result_date DESC
                ");
                $stmt->execute([$patient_id]);
                $results = $stmt->fetchAll();
                
                sendResponse(true, 'Patient test results retrieved successfully', $results);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving patient results: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePost($action) {
    global $pdo;
    
    switch ($action) {
        case 'create':
            $required_fields = ['patient_id', 'test_name', 'result_value'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $patient_id = (int)$_POST['patient_id'];
            $order_id = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
            $test_name = sanitizeInput($_POST['test_name']);
            $result_value = sanitizeInput($_POST['result_value']);
            $reference_range = !empty($_POST['reference_range']) ? sanitizeInput($_POST['reference_range']) : null;
            $unit = !empty($_POST['unit']) ? sanitizeInput($_POST['unit']) : null;
            $notes = !empty($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;
            
            try {
                // Verify patient exists
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$patient_id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Patient not found');
                }
                
                // Verify order exists if provided
                if ($order_id) {
                    $stmt = $pdo->prepare("SELECT id FROM test_orders WHERE id = ? AND patient_id = ?");
                    $stmt->execute([$order_id, $patient_id]);
                    if (!$stmt->fetch()) {
                        sendResponse(false, 'Test order not found or does not belong to this patient');
                    }
                }
                
                // Insert new result
                $stmt = $pdo->prepare("INSERT INTO test_results (order_id, patient_id, test_name, result_value, reference_range, unit, notes, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
                $stmt->execute([$order_id, $patient_id, $test_name, $result_value, $reference_range, $unit, $notes]);
                
                $id = $pdo->lastInsertId();
                sendResponse(true, 'Test result created successfully', ['id' => $id]);
                
            } catch (Exception $e) {
                sendResponse(false, 'Error creating test result: ' . $e->getMessage());
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Result ID is required');
            }
            
            $required_fields = ['test_name', 'result_value'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $test_name = sanitizeInput($_POST['test_name']);
            $result_value = sanitizeInput($_POST['result_value']);
            $reference_range = !empty($_POST['reference_range']) ? sanitizeInput($_POST['reference_range']) : null;
            $unit = !empty($_POST['unit']) ? sanitizeInput($_POST['unit']) : null;
            $notes = !empty($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;
            $status = !empty($_POST['status']) ? $_POST['status'] : 'completed';
            
            try {
                // Check if result exists
                $stmt = $pdo->prepare("SELECT id FROM test_results WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Test result not found');
                }
                
                // Update result
                $stmt = $pdo->prepare("UPDATE test_results SET test_name = ?, result_value = ?, reference_range = ?, 
                                      unit = ?, notes = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$test_name, $result_value, $reference_range, $unit, $notes, $status, $id]);
                
                sendResponse(true, 'Test result updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating test result: ' . $e->getMessage());
            }
            break;
            
        case 'review':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Result ID is required');
            }
            
            $reviewed_by = $_SESSION['user_id'] ?? 1; // Default to user ID 1 if not logged in
            
            try {
                // Check if result exists
                $stmt = $pdo->prepare("SELECT id FROM test_results WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Test result not found');
                }
                
                // Update review status
                $stmt = $pdo->prepare("UPDATE test_results SET status = 'reviewed', reviewed_by = ?, reviewed_date = NOW(), updated_at = NOW() WHERE id = ?");
                $stmt->execute([$reviewed_by, $id]);
                
                sendResponse(true, 'Test result reviewed successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error reviewing test result: ' . $e->getMessage());
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Result ID is required');
            }
            
            try {
                // Check if result exists
                $stmt = $pdo->prepare("SELECT id FROM test_results WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Test result not found');
                }
                
                // Soft delete - just change status
                $stmt = $pdo->prepare("UPDATE test_results SET status = 'deleted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                sendResponse(true, 'Test result deleted successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error deleting test result: ' . $e->getMessage());
            }
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
