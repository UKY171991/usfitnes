<?php
// API endpoint for getting dashboard counts and statistics
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (optional, depending on your security requirements)
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit();
// }

// Include database configuration
include_once '../config.php';

try {
    // Initialize response array
    $response = [];
    
    // Get total patients count
    $patients_query = "SELECT COUNT(*) as count FROM patients WHERE status = 'active'";
    $patients_result = mysqli_query($conn, $patients_query);
    $response['patients'] = $patients_result ? mysqli_fetch_assoc($patients_result)['count'] : 0;
    
    // Get pending test orders count
    $pending_orders_query = "SELECT COUNT(*) as count FROM test_orders WHERE status = 'pending'";
    $pending_orders_result = mysqli_query($conn, $pending_orders_query);
    $response['pending_orders'] = $pending_orders_result ? mysqli_fetch_assoc($pending_orders_result)['count'] : 0;
    
    // Get completed tests count (this month)
    $completed_tests_query = "SELECT COUNT(*) as count FROM test_orders 
                              WHERE status = 'completed' 
                              AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                              AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $completed_tests_result = mysqli_query($conn, $completed_tests_query);
    $response['completed_tests'] = $completed_tests_result ? mysqli_fetch_assoc($completed_tests_result)['count'] : 0;
    
    // Get doctors count
    $doctors_query = "SELECT COUNT(*) as count FROM doctors WHERE status = 'active'";
    $doctors_result = mysqli_query($conn, $doctors_query);
    $response['doctors'] = $doctors_result ? mysqli_fetch_assoc($doctors_result)['count'] : 0;
    
    // Get equipment count
    $equipment_query = "SELECT COUNT(*) as count FROM equipment WHERE status = 'active'";
    $equipment_result = mysqli_query($conn, $equipment_query);
    $response['equipment'] = $equipment_result ? mysqli_fetch_assoc($equipment_result)['count'] : 0;
    
    // Calculate monthly revenue (this month)
    $revenue_query = "SELECT SUM(amount) as total FROM payments 
                      WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                      AND YEAR(created_at) = YEAR(CURRENT_DATE())
                      AND status = 'paid'";
    $revenue_result = mysqli_query($conn, $revenue_query);
    $response['revenue'] = $revenue_result ? (mysqli_fetch_assoc($revenue_result)['total'] ?? 0) : 0;
    
    // Get test statistics for charts
    $test_stats_query = "SELECT 
                            test_type,
                            COUNT(*) as count
                         FROM test_orders 
                         WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())
                         GROUP BY test_type 
                         ORDER BY count DESC 
                         LIMIT 5";
    $test_stats_result = mysqli_query($conn, $test_stats_query);
    $test_stats = [];
    if ($test_stats_result) {
        while ($row = mysqli_fetch_assoc($test_stats_result)) {
            $test_stats[] = $row;
        }
    }
    $response['test_statistics'] = $test_stats;
    
    // Get recent activity count
    $recent_activity_query = "SELECT COUNT(*) as count FROM test_orders 
                              WHERE DATE(created_at) = CURRENT_DATE()";
    $recent_activity_result = mysqli_query($conn, $recent_activity_query);
    $response['today_orders'] = $recent_activity_result ? mysqli_fetch_assoc($recent_activity_result)['count'] : 0;
    
    // Add timestamp
    $response['timestamp'] = date('Y-m-d H:i:s');
    $response['success'] = true;
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Close database connection if exists
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>
