<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
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
    try {
        // Get all test orders with patient and test details
        $stmt = $pdo->query("
            SELECT 
                to.*,
                p.full_name as patient_name,
                d.first_name as doctor_first_name,
                d.last_name as doctor_last_name
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN doctors d ON to.doctor_id = d.id
            ORDER BY to.order_date DESC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get order items for each order
        foreach ($orders as &$order) {
            $stmt = $pdo->prepare("
                SELECT 
                    toi.*,
                    t.test_name,
                    t.test_code
                FROM test_order_items toi
                LEFT JOIN tests t ON toi.test_id = t.id
                WHERE toi.order_id = ?
            ");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format doctor name
            if ($order['doctor_first_name'] && $order['doctor_last_name']) {
                $order['doctor_name'] = $order['doctor_first_name'] . ' ' . $order['doctor_last_name'];
            } else {
                $order['doctor_name'] = 'N/A';
            }
        }
        
        echo json_encode(['success' => true, 'data' => $orders]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePost($pdo, $input) {
    if (empty($input)) $input = $_POST;
    
    // Required fields for simplified order
    $required = ['patient_id', 'order_date', 'priority', 'test_ids'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Generate order ID
        $order_id = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calculate total amount
        $test_ids = is_array($input['test_ids']) ? $input['test_ids'] : explode(',', $input['test_ids']);
        $total_amount = 0;
        
        // Get test prices
        $placeholders = str_repeat('?,', count($test_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price FROM tests WHERE id IN ($placeholders)");
        $stmt->execute($test_ids);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($tests as $test) {
            $total_amount += $test['price'];
        }
        
        // Insert test order
        $stmt = $pdo->prepare("
            INSERT INTO test_orders (order_id, patient_id, doctor_id, order_date, priority, status, total_amount, instructions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $order_id,
            $input['patient_id'],
            $input['doctor_id'] ?? null,
            $input['order_date'],
            $input['priority'],
            'Pending',
            $total_amount,
            $input['instructions'] ?? ''
        ]);
        
        $order_db_id = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("
            INSERT INTO test_order_items (order_id, test_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($tests as $test) {
            $stmt->execute([
                $order_db_id,
                $test['id'],
                1, // Default quantity
                $test['price']
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test order created successfully',
            'data' => ['order_id' => $order_id, 'id' => $order_db_id]
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    if (empty($input)) $input = $_POST;
    
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    try {
        // Update order status
        $stmt = $pdo->prepare("
            UPDATE test_orders SET 
                status = ?, 
                instructions = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['status'] ?? 'Pending',
            $input['instructions'] ?? '',
            $input['id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    if (empty($input)) $input = $_POST;
    
    $order_id = $input['id'] ?? null;
    if (!$order_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    try {
        // Check if order has results
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_results WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $results_count = $stmt->fetchColumn();
        
        if ($results_count > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete order with existing results']);
            return;
        }
        
        // Delete order (items will be deleted automatically due to foreign key)
        $stmt = $pdo->prepare("DELETE FROM test_orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
