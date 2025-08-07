<?php
require_once '../config.php';
require_once '../includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['patient_id', 'doctor_id', 'test_type', 'urgency'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Verify patient and doctor exist
    $stmt = $conn->prepare("SELECT id FROM patients WHERE id = ? AND status = 'active'");
    $stmt->execute([$_POST['patient_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid patient selected']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->execute([$_POST['doctor_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid doctor selected']);
        exit;
    }
    
    $data = [
        'patient_id' => (int)$_POST['patient_id'],
        'doctor_id' => (int)$_POST['doctor_id'],
        'test_type' => trim($_POST['test_type']),
        'urgency' => trim($_POST['urgency']),
        'notes' => trim($_POST['notes'] ?? ''),
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => $_SESSION['user_id'],
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['user_id']
    ];
    
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    
    if ($order_id > 0) {
        // Update existing order
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if ($key !== 'created_at' && $key !== 'created_by') { // Don't update creation fields
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        $values[] = $order_id;
        
        $sql = "UPDATE test_orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        
        $message = 'Test order updated successfully';
    } else {
        // Insert new order
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO test_orders ($fields) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        
        $order_id = $conn->lastInsertId();
        $message = 'Test order created successfully';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
