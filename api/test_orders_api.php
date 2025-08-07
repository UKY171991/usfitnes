<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized access', null, ['code' => 401]);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            jsonResponse(false, 'Method not allowed', null, ['code' => 405]);
    }
} catch (Exception $e) {
    error_log("Test Orders API Error: " . $e->getMessage());
    jsonResponse(false, 'Internal server error', null, ['code' => 500]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
        // Get single test order with items
        $stmt = $pdo->prepare("
            SELECT to.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.patient_id,
                   d.name as doctor_name
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN doctors d ON to.doctor_id = d.id
            WHERE to.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $itemsStmt = $pdo->prepare("
                SELECT toi.*, t.name as test_name, t.test_code
                FROM test_order_items toi
                LEFT JOIN tests t ON toi.test_id = t.id
                WHERE toi.test_order_id = ?
            ");
            $itemsStmt->execute([$_GET['id']]);
            $order['items'] = $itemsStmt->fetchAll();
            
            jsonResponse(true, 'Test order retrieved successfully', $order);
        } else {
            jsonResponse(false, 'Test order not found', null, ['code' => 404]);
        }
    } else {
        // Get all test orders with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
        
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (to.order_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR d.name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 4, $searchTerm);
        }
        
        if (!empty($status)) {
            $whereClause .= " AND to.status = ?";
            $params[] = $status;
        }
        
        if ($patient_id > 0) {
            $whereClause .= " AND to.patient_id = ?";
            $params[] = $patient_id;
        }
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN doctors d ON to.doctor_id = d.id
            $whereClause
        ");
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get test orders
        $stmt = $pdo->prepare("
            SELECT to.id, to.order_number, to.status, to.priority, to.total_amount, 
                   to.discount, to.order_date, to.created_at,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.patient_id,
                   d.name as doctor_name,
                   (SELECT COUNT(*) FROM test_order_items WHERE test_order_id = to.id) as test_count
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN doctors d ON to.doctor_id = d.id
            $whereClause 
            ORDER BY to.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        $response = [
            'orders' => $orders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
        jsonResponse(true, 'Test orders retrieved successfully', $response);
    }
}

function handlePost() {
    global $pdo;
    
    // Validate required fields
    $required = ['patient_id'];
    $errors = validateInput($_POST, $required);
    
    if (!empty($errors)) {
        jsonResponse(false, implode(', ', $errors), null, ['code' => 422]);
    }
    
    // Validate tests
    if (empty($_POST['tests'])) {
        jsonResponse(false, 'At least one test is required', null, ['code' => 422]);
    }
    
    $tests = json_decode($_POST['tests'], true);
    if (!is_array($tests) || empty($tests)) {
        jsonResponse(false, 'Invalid tests data', null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($_POST);
    
    // Generate order number
    $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if order number already exists
    while (true) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE order_number = ?");
        $checkStmt->execute([$orderNumber]);
        if ($checkStmt->fetchColumn() == 0) break;
        $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total amount
        $testIds = implode(',', array_map('intval', $tests));
        $testsStmt = $pdo->prepare("SELECT id, price FROM tests WHERE id IN ($testIds)");
        $testsStmt->execute();
        $testPrices = $testsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $totalAmount = array_sum($testPrices);
        $discount = floatval($data['discount'] ?? 0);
        $finalAmount = max(0, $totalAmount - $discount);
        
        // Insert test order
        $stmt = $pdo->prepare("
            INSERT INTO test_orders (
                order_number, patient_id, doctor_id, priority, status, 
                total_amount, discount, notes, order_date
            ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderNumber,
            $data['patient_id'],
            $data['doctor_id'] ?: null,
            $data['priority'] ?? 'normal',
            $finalAmount,
            $discount,
            $data['notes'] ?? null,
            $data['order_date'] ?? date('Y-m-d H:i:s')
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert test order items
        $itemStmt = $pdo->prepare("
            INSERT INTO test_order_items (test_order_id, test_id, quantity, price) 
            VALUES (?, ?, 1, ?)
        ");
        
        foreach ($tests as $testId) {
            if (isset($testPrices[$testId])) {
                $itemStmt->execute([$orderId, $testId, $testPrices[$testId]]);
            }
        }
        
        $pdo->commit();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Test Order Created', "Created test order: $orderNumber");
        
        jsonResponse(true, 'Test order created successfully', ['id' => $orderId, 'order_number' => $orderNumber]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function handlePut() {
    global $pdo;
    
    // Get PUT data
    parse_str(file_get_contents("php://input"), $putData);
    
    if (empty($putData['id'])) {
        jsonResponse(false, 'Test order ID is required', null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($putData);
    $id = $data['id'];
    
    // Check if test order exists
    $checkStmt = $pdo->prepare("SELECT order_number, status FROM test_orders WHERE id = ?");
    $checkStmt->execute([$id]);
    $existingOrder = $checkStmt->fetch();
    
    if (!$existingOrder) {
        jsonResponse(false, 'Test order not found', null, ['code' => 404]);
    }
    
    // Check if order can be modified
    if (in_array($existingOrder['status'], ['completed', 'cancelled'])) {
        jsonResponse(false, 'Cannot modify completed or cancelled orders', null, ['code' => 422]);
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['status', 'priority', 'notes', 'discount'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        jsonResponse(false, 'No fields to update', null, ['code' => 422]);
    }
    
    $params[] = $id;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE test_orders 
            SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute($params);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Test Order Updated', "Updated test order: {$existingOrder['order_number']}");
        
        jsonResponse(true, 'Test order updated successfully');
        
    } catch (PDOException $e) {
        throw $e;
    }
}

function handleDelete() {
    global $pdo;
    
    // Get DELETE data
    parse_str(file_get_contents("php://input"), $deleteData);
    
    if (empty($deleteData['id'])) {
        jsonResponse(false, 'Test order ID is required', null, ['code' => 422]);
    }
    
    $id = (int)$deleteData['id'];
    
    // Check if test order exists
    $checkStmt = $pdo->prepare("SELECT order_number, status FROM test_orders WHERE id = ?");
    $checkStmt->execute([$id]);
    $order = $checkStmt->fetch();
    
    if (!$order) {
        jsonResponse(false, 'Test order not found', null, ['code' => 404]);
    }
    
    // Check if order can be cancelled
    if (in_array($order['status'], ['completed', 'cancelled'])) {
        jsonResponse(false, 'Cannot cancel completed or already cancelled orders', null, ['code' => 422]);
    }
    
    try {
        // Update status to cancelled instead of deleting
        $stmt = $pdo->prepare("UPDATE test_orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Test Order Cancelled', "Cancelled test order: {$order['order_number']}");
        
        jsonResponse(true, 'Test order cancelled successfully');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Error cancelling test order', null, ['code' => 500]);
    }
}
?>