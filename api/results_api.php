<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo, $input);
            break;
        case 'PUT':
            handlePut($pdo, $input);
            break;
        case 'DELETE':
            handleDelete($pdo, $input);
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

function handleGet($pdo) {
    if (isset($_GET['id'])) {
        // Get single test result
        $stmt = $pdo->prepare("
            SELECT tr.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   to.test_name, to.test_type
            FROM test_results tr
            LEFT JOIN test_orders to ON tr.order_id = to.order_id
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            WHERE tr.result_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test result not found']);
        }
    } else {
        // Get all test results with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $whereClause = '';
        $params = [];
        
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(to.test_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($status)) {
            $conditions[] = "tr.status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM test_results tr
            LEFT JOIN test_orders to ON tr.order_id = to.order_id
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            $whereClause
        ");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get test results
        $stmt = $pdo->prepare("
            SELECT tr.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   to.test_name, to.test_type
            FROM test_results tr
            LEFT JOIN test_orders to ON tr.order_id = to.order_id
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            $whereClause
            ORDER BY tr.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ]);
    }
}

function handlePost($pdo, $input) {
    // Validate required fields
    $requiredFields = ['order_id', 'result_values'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Validate order exists
    $stmt = $pdo->prepare("SELECT order_id FROM test_orders WHERE order_id = ?");
    $stmt->execute([$input['order_id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        return;
    }
    
    // Check if result already exists for this order
    $stmt = $pdo->prepare("SELECT result_id FROM test_results WHERE order_id = ?");
    $stmt->execute([$input['order_id']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Result already exists for this order']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO test_results (order_id, result_values, reference_ranges, status, notes, technician_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $status = isset($input['status']) ? $input['status'] : 'pending';
        $technician_id = $_SESSION['user_id']; // Current logged-in user
        
        $stmt->execute([
            $input['order_id'],
            trim($input['result_values']),
            !empty($input['reference_ranges']) ? trim($input['reference_ranges']) : null,
            $status,
            !empty($input['notes']) ? trim($input['notes']) : null,
            $technician_id
        ]);
        
        $resultId = $pdo->lastInsertId();
        
        // Update test order status to completed if result is approved
        if ($status === 'approved') {
            $updateStmt = $pdo->prepare("UPDATE test_orders SET status = 'completed' WHERE order_id = ?");
            $updateStmt->execute([$input['order_id']]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Test result created successfully',
            'data' => ['result_id' => $resultId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    if (!isset($input['result_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Result ID is required']);
        return;
    }
    
    // Check if result exists
    $stmt = $pdo->prepare("SELECT order_id FROM test_results WHERE result_id = ?");
    $stmt->execute([$input['result_id']]);
    $result = $stmt->fetch();
    if (!$result) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Test result not found']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE test_results SET 
                result_values = COALESCE(?, result_values),
                reference_ranges = ?,
                status = COALESCE(?, status),
                notes = ?,
                approved_by = ?,
                approved_at = ?
            WHERE result_id = ?
        ");
        
        $approved_by = null;
        $approved_at = null;
        
        // If status is being set to approved, record approval details
        if (isset($input['status']) && $input['status'] === 'approved') {
            $approved_by = $_SESSION['user_id'];
            $approved_at = date('Y-m-d H:i:s');
            
            // Update test order status to completed
            $updateStmt = $pdo->prepare("UPDATE test_orders SET status = 'completed' WHERE order_id = ?");
            $updateStmt->execute([$result['order_id']]);
        }
        
        $stmt->execute([
            isset($input['result_values']) ? trim($input['result_values']) : null,
            isset($input['reference_ranges']) ? trim($input['reference_ranges']) : null,
            $input['status'] ?? null,
            isset($input['notes']) ? trim($input['notes']) : null,
            $approved_by,
            $approved_at,
            $input['result_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Test result updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    if (!isset($input['result_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Result ID is required']);
        return;
    }
    
    try {
        // Get the order_id before deleting
        $stmt = $pdo->prepare("SELECT order_id FROM test_results WHERE result_id = ?");
        $stmt->execute([$input['result_id']]);
        $result = $stmt->fetch();
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test result not found']);
            return;
        }
        
        // Delete the result
        $stmt = $pdo->prepare("DELETE FROM test_results WHERE result_id = ?");
        $stmt->execute([$input['result_id']]);
        
        if ($stmt->rowCount() > 0) {
            // Update test order status back to pending
            $updateStmt = $pdo->prepare("UPDATE test_orders SET status = 'pending' WHERE order_id = ?");
            $updateStmt->execute([$result['order_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Test result deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test result not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
