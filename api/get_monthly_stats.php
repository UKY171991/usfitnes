<?php
// API endpoint for getting monthly test statistics
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include_once '../config.php';

try {
    // Initialize response array
    $response = [];
    
    // Get monthly test statistics for the last 12 months
    $query = "SELECT 
                DATE_FORMAT(created_at, '%b') as month_name,
                DATE_FORMAT(created_at, '%Y-%m') as month_key,
                COUNT(*) as count
              FROM test_orders
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month_key";
    
    $result = mysqli_query($conn, $query);
    
    $labels = [];
    $values = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['month_name'];
            $values[] = (int)$row['count'];
        }
    }
    
    // If no data, provide default months
    if (empty($labels)) {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $values = array_fill(0, 12, 0);
    }
    
    $response = [
        'success' => true,
        'labels' => $labels,
        'values' => $values,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'labels' => ['No Data'],
        'values' => [0]
    ]);
} finally {
    // Close database connection if exists
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>
