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
    
    // Get recent activities from multiple tables
    $query = "
        (SELECT 'patient_added' as activity_type, 
                CONCAT('New patient: ', first_name, ' ', last_name) as message,
                'fas fa-user-plus' as icon,
                'success' as color,
                created_at as activity_time
         FROM patients 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY created_at DESC 
         LIMIT 3)
        
        UNION ALL
        
        (SELECT 'test_ordered' as activity_type,
                CONCAT('Test ordered: ', test_type) as message,
                'fas fa-flask' as icon,
                'info' as color,
                created_at as activity_time
         FROM test_orders 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY created_at DESC 
         LIMIT 3)
        
        UNION ALL
        
        (SELECT 'result_completed' as activity_type,
                'Test result completed' as message,
                'fas fa-check-circle' as icon,
                'success' as color,
                updated_at as activity_time
         FROM test_orders 
         WHERE status = 'completed' 
         AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY updated_at DESC 
         LIMIT 3)
        
        ORDER BY activity_time DESC 
        LIMIT 10
    ";
    
    $stmt = $conn->query($query);
    $activities = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'message' => htmlspecialchars($row['message']),
            'icon' => $row['icon'],
            'color' => $row['color'],
            'time' => timeAgo($row['activity_time'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $activities
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j', strtotime($datetime));
}
?>
