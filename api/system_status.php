<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $response = [
        'success' => true,
        'notifications' => 0,
        'online_users' => 1,
        'db_status' => 'OK',
        'recent_notifications' => []
    ];
    
    // Get notification count (if you have a notifications table)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $response['notifications'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Ignore if table doesn't exist
    }
    
    // Get online users count (simplified)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
        $response['online_users'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Ignore if table doesn't exist
    }
    
    // Test database connection
    $pdo->query("SELECT 1");
    $response['db_status'] = 'OK';
    
    // Get recent notifications (if available)
    try {
        $stmt = $pdo->prepare("SELECT action as message, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $notifications = $stmt->fetchAll();
        
        $recent = [];
        foreach ($notifications as $notification) {
            $recent[] = [
                'message' => $notification['message'],
                'time' => date('H:i', strtotime($notification['created_at'])),
                'icon' => 'info-circle'
            ];
        }
        $response['recent_notifications'] = $recent;
    } catch (Exception $e) {
        // Ignore if table doesn't exist
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'System status check failed',
        'error' => $e->getMessage()
    ]);
}
?>
