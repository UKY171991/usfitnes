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
        // Get single test order
        $stmt = $pdo->prepare("
            SELECT to.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            LEFT JOIN doctors d ON to.doctor_id = d.doctor_id
            WHERE to.order_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test order not found']);
        }
    } else {
        // Get all test orders with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $whereClause = '';
        $params = [];
        
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(test_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($status)) {
            $conditions[] = "to.status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            $whereClause
        ");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get test orders
        $stmt = $pdo->prepare("
            SELECT to.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.patient_id
            LEFT JOIN doctors d ON to.doctor_id = d.doctor_id
            $whereClause
            ORDER BY to.order_date DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $orders,
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
    $requiredFields = ['patient_id', 'test_name', 'test_type'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Validate patient exists
    $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
    $stmt->execute([$input['patient_id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
        return;
    }
    
    // Validate doctor exists if provided
    if (!empty($input['doctor_id'])) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$input['doctor_id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid doctor ID']);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO test_orders (patient_id, doctor_id, test_name, test_type, test_cost, total_amount, status, priority, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $test_cost = isset($input['test_cost']) ? (float)$input['test_cost'] : 0.00;
        $total_amount = isset($input['total_amount']) ? (float)$input['total_amount'] : $test_cost;
        $status = isset($input['status']) ? $input['status'] : 'pending';
        $priority = isset($input['priority']) ? $input['priority'] : 'normal';
        
        $stmt->execute([
            $input['patient_id'],
            !empty($input['doctor_id']) ? $input['doctor_id'] : null,
            trim($input['test_name']),
            trim($input['test_type']),
            $test_cost,
            $total_amount,
            $status,
            $priority,
            !empty($input['notes']) ? trim($input['notes']) : null
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test order created successfully',
            'data' => ['order_id' => $orderId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    if (!isset($input['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    // Check if order exists
    $stmt = $pdo->prepare("SELECT order_id FROM test_orders WHERE order_id = ?");
    $stmt->execute([$input['order_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Test order not found']);
        return;
    }
    
    // Validate patient exists if provided
    if (isset($input['patient_id'])) {
        $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
        $stmt->execute([$input['patient_id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
            return;
        }
    }
    
    // Validate doctor exists if provided
    if (!empty($input['doctor_id'])) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$input['doctor_id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid doctor ID']);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE test_orders SET 
                patient_id = COALESCE(?, patient_id),
                doctor_id = ?,
                test_name = COALESCE(?, test_name),
                test_type = COALESCE(?, test_type),
                test_cost = COALESCE(?, test_cost),
                total_amount = COALESCE(?, total_amount),
                status = COALESCE(?, status),
                priority = COALESCE(?, priority),
                notes = ?
            WHERE order_id = ?
        ");
        
        $stmt->execute([
            $input['patient_id'] ?? null,
            !empty($input['doctor_id']) ? $input['doctor_id'] : null,
            isset($input['test_name']) ? trim($input['test_name']) : null,
            isset($input['test_type']) ? trim($input['test_type']) : null,
            isset($input['test_cost']) ? (float)$input['test_cost'] : null,
            isset($input['total_amount']) ? (float)$input['total_amount'] : null,
            $input['status'] ?? null,
            $input['priority'] ?? null,
            isset($input['notes']) ? trim($input['notes']) : null,
            $input['order_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Test order updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    if (!isset($input['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    try {
        // Check if order has results
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_results WHERE order_id = ?");
        $stmt->execute([$input['order_id']]);
        $resultCount = $stmt->fetchColumn();
        
        if ($resultCount > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete test order with existing results']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM test_orders WHERE order_id = ?");
        $stmt->execute([$input['order_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Test order deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test order not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
