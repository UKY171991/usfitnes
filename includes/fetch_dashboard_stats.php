<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get total patients count
    $stmt = $db->query("SELECT COUNT(*) as total FROM Patients");
    $total_patients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get pending tests count
    $stmt = $db->query("SELECT COUNT(*) as total FROM Test_Requests WHERE status = 'Pending'");
    $pending_tests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get today's reports count
    $stmt = $db->query(
        "SELECT COUNT(*) as total FROM Test_Results WHERE DATE(recorded_at) = CURDATE()"
    );
    $today_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get monthly revenue
    $stmt = $db->query(
        "SELECT COALESCE(SUM(tc.price), 0) as total 
         FROM Test_Requests tr 
         JOIN tests_catalog_old tc ON tr.test_id = tc.test_id 
         WHERE MONTH(tr.request_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(tr.request_date) = YEAR(CURRENT_DATE())"
    );
    $monthly_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get recent test requests
    $stmt = $db->query(
        "SELECT tr.request_id, p.first_name, p.last_name, tc.test_name, tr.status, tr.request_date 
         FROM Test_Requests tr 
         JOIN Patients p ON tr.patient_id = p.patient_id 
         JOIN tests_catalog_old tc ON tr.test_id = tc.test_id 
         ORDER BY tr.request_date DESC LIMIT 5"
    );
    $recent_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format recent requests for display
    foreach ($recent_requests as &$request) {
        $request['patient_name'] = htmlspecialchars($request['first_name'] . ' ' . $request['last_name']);
        $request['test_name'] = htmlspecialchars($request['test_name']);
        $request['status_class'] = $request['status'] === 'Completed' ? 'success' : 
            ($request['status'] === 'Pending' ? 'warning' : 'info');
        $request['date_formatted'] = date('M d, Y', strtotime($request['request_date']));
        unset($request['first_name'], $request['last_name']);
    }
    
    // Set cache control headers
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-Type: application/json');
    
    // Return the data
    echo json_encode([
        'success' => true,
        'total_patients' => number_format($total_patients),
        'pending_tests' => number_format($pending_tests),
        'today_reports' => number_format($today_reports),
        'monthly_revenue' => '$' . number_format($monthly_revenue, 2),
        'recent_requests' => $recent_requests
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch dashboard statistics'
    ]);
}
?>