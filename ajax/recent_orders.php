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

try {
    $conn = getDbConnection();
    
    $query = "
        SELECT 
            to.id,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            to.test_type,
            CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
            to.status,
            to.created_at,
            CASE 
                WHEN to.status = 'pending' THEN '<span class=\"badge badge-warning\">Pending</span>'
                WHEN to.status = 'in_progress' THEN '<span class=\"badge badge-info\">In Progress</span>'
                WHEN to.status = 'completed' THEN '<span class=\"badge badge-success\">Completed</span>'
                WHEN to.status = 'cancelled' THEN '<span class=\"badge badge-danger\">Cancelled</span>'
                ELSE '<span class=\"badge badge-secondary\">Unknown</span>'
            END as status_badge
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        WHERE to.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY to.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $conn->query($query);
    $orders = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders[] = [
            'id' => (int)$row['id'],
            'patient_name' => htmlspecialchars($row['patient_name'] ?? 'Unknown Patient'),
            'test_type' => htmlspecialchars($row['test_type'] ?? 'N/A'),
            'doctor_name' => htmlspecialchars($row['doctor_name'] ?? 'Unknown Doctor'),
            'status' => $row['status'],
            'status_badge' => $row['status_badge'],
            'created_date' => date('M j, Y', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $orders
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
