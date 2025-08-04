<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    $notifications = [];
    
    // Get new orders count (orders created in last 24 hours)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'pending'");
    $notifications['new_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get urgent orders count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE priority = 'urgent' AND status IN ('pending', 'processing')");
    $notifications['urgent_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending reports count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'completed' AND report_status = 'pending'");
    $notifications['pending_reports'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get overdue orders count (orders older than expected completion time)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status IN ('pending', 'processing') AND expected_completion < NOW()");
    $notifications['overdue_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // System status check
    $system_status = 'OK';
    $system_issues = [];
    
    // Check for system alerts
    if ($notifications['overdue_orders'] > 5) {
        $system_status = 'WARNING';
        $system_issues[] = 'Multiple overdue orders';
    }
    
    if ($notifications['urgent_orders'] > 10) {
        $system_status = 'CRITICAL';
        $system_issues[] = 'High urgent orders count';
    }
    
    // Check equipment status (if equipment table exists)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM equipment WHERE status = 'maintenance'");
        $maintenance_equipment = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($maintenance_equipment > 0) {
            $notifications['equipment_maintenance'] = $maintenance_equipment;
            if ($system_status === 'OK') {
                $system_status = 'WARNING';
            }
            $system_issues[] = 'Equipment under maintenance';
        }
    } catch (PDOException $e) {
        // Equipment table might not exist, ignore
    }
    
    $notifications['system_status'] = $system_status;
    $notifications['system_issues'] = $system_issues;
    
    echo json_encode([
        'success' => true,
        'data' => $notifications
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
