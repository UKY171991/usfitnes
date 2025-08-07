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
    
    $stmt = $conn->query("
        SELECT id, CONCAT(first_name, ' ', last_name) as name 
        FROM patients 
        WHERE status = 'active' 
        ORDER BY first_name, last_name
    ");
    
    $patients = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $patients[] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars($row['name'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $patients
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
