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
    
    // Get total patients
    $stmt = $conn->query("SELECT COUNT(*) as count FROM patients WHERE status = 'active'");
    $totalPatients = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get today's tests
    $stmt = $conn->query("SELECT COUNT(*) as count FROM test_orders WHERE DATE(created_at) = CURDATE()");
    $todaysTests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending results
    $stmt = $conn->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'pending'");
    $pendingResults = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total doctors
    $stmt = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
    $totalDoctors = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $response = [
        'success' => true,
        'data' => [
            'total_patients' => (int)$totalPatients,
            'todays_tests' => (int)$todaysTests,
            'pending_results' => (int)$pendingResults,
            'total_doctors' => (int)$totalDoctors
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
