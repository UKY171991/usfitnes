<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $response = ['success' => true, 'data' => []];
    
    // Get patients count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients WHERE status = 'active'");
    $response['data']['patients'] = $stmt->fetch()['count'];
    
    // Get doctors count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
    $response['data']['doctors'] = $stmt->fetch()['count'];
    
    // Get equipment count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM equipment WHERE status IN ('active', 'maintenance')");
    $response['data']['equipment'] = $stmt->fetch()['count'];
    
    // Get today's orders count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE DATE(created_at) = CURDATE()");
    $response['data']['orders'] = $stmt->fetch()['count'];
    
    // Get pending results count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'in_progress'");
    $response['data']['pending_results'] = $stmt->fetch()['count'];
    
    // Get this month's revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as revenue FROM payments WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $response['data']['monthly_revenue'] = number_format($stmt->fetch()['revenue'], 2);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Failed to load dashboard counts'
    ];
    error_log("Dashboard counts error: " . $e->getMessage());
}

echo json_encode($response);
?>
