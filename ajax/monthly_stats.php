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
    
    // Get monthly statistics for the past 12 months
    $query = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            MONTHNAME(created_at) as month_name,
            COUNT(*) as test_count
        FROM test_orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), MONTHNAME(created_at)
        ORDER BY month ASC
    ";
    
    $stmt = $conn->query($query);
    
    $labels = [];
    $values = [];
    
    // Initialize with empty data for all 12 months if no data exists
    $currentDate = new DateTime();
    $currentDate->modify('-11 months'); // Go back 11 months to include current month
    
    for ($i = 0; $i < 12; $i++) {
        $monthKey = $currentDate->format('Y-m');
        $monthName = $currentDate->format('M');
        
        $labels[] = $monthName;
        $values[] = 0; // Default to 0
        
        $currentDate->modify('+1 month');
    }
    
    // Fill in actual data
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monthDate = DateTime::createFromFormat('Y-m', $row['month']);
        if ($monthDate) {
            $monthName = $monthDate->format('M');
            $index = array_search($monthName, $labels);
            if ($index !== false) {
                $values[$index] = (int)$row['test_count'];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'values' => $values
        ]
    ]);
    
} catch (Exception $e) {
    // Return empty data on error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'values' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        ]
    ]);
}
?>
