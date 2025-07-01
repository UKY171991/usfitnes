<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        $stmt = $pdo->prepare('SELECT * FROM test_orders WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
        }
        return;
    }
    $draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
    $start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
    $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = "WHERE order_id LIKE ? OR patient_name LIKE ? OR test_name LIKE ?";
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam, $searchParam];
    }
    $recordsTotal = (int)$pdo->query('SELECT COUNT(*) FROM test_orders')->fetchColumn();
    $sqlCount = "SELECT COUNT(*) FROM test_orders $where";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $recordsFiltered = (int)$stmt->fetchColumn();
    $sql = "SELECT * FROM test_orders $where ORDER BY order_date DESC LIMIT $length OFFSET $start";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
}

function handlePost($pdo, $input) {
    if (empty($input)) $input = $_POST;
    $required = ['order_id', 'patient_name', 'test_name', 'priority', 'status', 'order_date'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    $stmt = $pdo->prepare('INSERT INTO test_orders (order_id, patient_name, test_name, priority, status, order_date) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $input['order_id'],
        $input['patient_name'],
        $input['test_name'],
        $input['priority'],
        $input['status'],
        $input['order_date']
    ]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Order added successfully', 'data' => ['id' => $id]]);
}

function handlePut($pdo, $input) {
    if (empty($input)) $input = $_POST;
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    $stmt = $pdo->prepare('UPDATE test_orders SET order_id=?, patient_name=?, test_name=?, priority=?, status=?, order_date=? WHERE id=?');
    $stmt->execute([
        $input['order_id'],
        $input['patient_name'],
        $input['test_name'],
        $input['priority'],
        $input['status'],
        $input['order_date'],
        $input['id']
    ]);
    echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
}

function handleDelete($pdo, $input) {
    if (empty($input)) $input = $_POST;
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    $stmt = $pdo->prepare('DELETE FROM test_orders WHERE id = ?');
    $stmt->execute([$input['id']]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
}
?>
