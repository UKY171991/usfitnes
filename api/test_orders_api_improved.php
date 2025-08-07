<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listOrders();
            break;
        case 'get':
            getOrder();
            break;
        case 'add':
        case 'create':
            addOrder();
            break;
        case 'update':
            updateOrder();
            break;
        case 'update_status':
            updateOrderStatus();
            break;
        case 'delete':
            deleteOrder();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Test Orders API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function listOrders() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            to.id, to.order_id, to.patient_id, to.doctor_id, 
            to.test_ids, to.test_names, to.priority, to.status, 
            to.order_date, to.notes, to.created_at,
            p.first_name as patient_first_name, p.last_name as patient_last_name,
            d.first_name as doctor_first_name, d.last_name as doctor_last_name,
            d.specialization as doctor_specialization
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        WHERE to.status != 'deleted'
        ORDER BY to.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'total' => count($orders)
    ]);
}

function getOrder() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        throw new Exception('Order ID is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM test_orders 
        WHERE id = ? AND status != 'deleted'
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $order
    ]);
}

function addOrder() {
    global $pdo;
    
    // Validate required fields
    $required = ['patient_id', 'doctor_id', 'test_ids', 'order_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $test_ids = is_array($_POST['test_ids']) ? implode(',', $_POST['test_ids']) : $_POST['test_ids'];
    $test_names = is_array($_POST['test_ids']) ? implode(',', $_POST['test_ids']) : $_POST['test_ids'];
    $priority = trim($_POST['priority']) ?: 'normal';
    $order_date = $_POST['order_date'];
    $notes = trim($_POST['notes']) ?: null;
    
    // Validate patient exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status = 'active'");
    $stmt->execute([$patient_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Selected patient not found');
    }
    
    // Validate doctor exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->execute([$doctor_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Selected doctor not found');
    }
    
    // Generate unique order ID
    $order_id = generateOrderId();
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO test_orders (
            order_id, patient_id, doctor_id, test_ids, test_names,
            priority, status, order_date, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $order_id,
        $patient_id,
        $doctor_id,
        $test_ids,
        $test_names,
        $priority,
        $order_date,
        $notes
    ]);
    
    if (!$result) {
        throw new Exception('Failed to create order');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Test order created successfully',
        'order_id' => $order_id,
        'id' => $pdo->lastInsertId()
    ]);
}

function updateOrder() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Order ID is required');
    }
    
    // Validate required fields
    $required = ['patient_id', 'doctor_id', 'test_ids', 'order_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $test_ids = is_array($_POST['test_ids']) ? implode(',', $_POST['test_ids']) : $_POST['test_ids'];
    $test_names = is_array($_POST['test_ids']) ? implode(',', $_POST['test_ids']) : $_POST['test_ids'];
    $priority = trim($_POST['priority']) ?: 'normal';
    $order_date = $_POST['order_date'];
    $notes = trim($_POST['notes']) ?: null;
    
    // Check if order exists
    $stmt = $pdo->prepare("SELECT id FROM test_orders WHERE id = ? AND status != 'deleted'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Order not found');
    }
    
    // Update order
    $stmt = $pdo->prepare("
        UPDATE test_orders SET 
            patient_id = ?, doctor_id = ?, test_ids = ?, test_names = ?,
            priority = ?, order_date = ?, notes = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $patient_id,
        $doctor_id,
        $test_ids,
        $test_names,
        $priority,
        $order_date,
        $notes,
        $id
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update order');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully'
    ]);
}

function updateOrderStatus() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!$id || !$status) {
        throw new Exception('Order ID and status are required');
    }
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Check if order exists
    $stmt = $pdo->prepare("SELECT id FROM test_orders WHERE id = ? AND status != 'deleted'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Order not found');
    }
    
    // Update status
    $stmt = $pdo->prepare("UPDATE test_orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$status, $id]);
    
    if (!$result) {
        throw new Exception('Failed to update status');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully'
    ]);
}

function deleteOrder() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Order ID is required');
    }
    
    // Check if order exists
    $stmt = $pdo->prepare("SELECT id FROM test_orders WHERE id = ? AND status != 'deleted'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Order not found');
    }
    
    // Soft delete
    $stmt = $pdo->prepare("UPDATE test_orders SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to delete order');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order deleted successfully'
    ]);
}

function generateOrderId() {
    global $pdo;
    
    // Try to get the last order ID number
    $stmt = $pdo->query("
        SELECT order_id FROM test_orders 
        WHERE order_id LIKE 'TO%' 
        ORDER BY CAST(SUBSTRING(order_id, 3) AS UNSIGNED) DESC 
        LIMIT 1
    ");
    
    $lastId = $stmt->fetchColumn();
    
    if ($lastId && preg_match('/TO(\d+)/', $lastId, $matches)) {
        $nextNumber = intval($matches[1]) + 1;
    } else {
        $nextNumber = 1;
    }
    
    $newId = 'TO' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    
    // Ensure uniqueness
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $pdo->prepare("SELECT id FROM test_orders WHERE order_id = ?");
        $stmt->execute([$newId]);
        if (!$stmt->fetch()) {
            return $newId;
        }
        $nextNumber++;
        $newId = 'TO' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $attempts++;
    }
    
    // Fallback to random if sequential fails
    return 'TO' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}
?>
