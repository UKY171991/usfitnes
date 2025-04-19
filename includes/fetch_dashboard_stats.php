<?php
require_once 'db_connect.php';

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    $db = Database::getInstance();
    $branch_id = $_SESSION['branch_id'];

    // Initialize stats array
    $stats = [];

    // Total Patients (with caching)
    $cache_key = "patients_count_{$branch_id}";
    $stats['patients'] = apcu_exists($cache_key) 
        ? apcu_fetch($cache_key) 
        : $db->query(
            "SELECT COUNT(*) FROM patients WHERE branch_id = :branch_id",
            ['branch_id' => $branch_id]
        )->fetchColumn();
    
    if (!apcu_exists($cache_key)) {
        apcu_store($cache_key, $stats['patients'], 300); // Cache for 5 minutes
    }

    // Pending Tests
    $stats['pending_tests'] = $db->query(
        "SELECT COUNT(*) FROM test_requests 
         WHERE branch_id = :branch_id AND status = 'pending'",
        ['branch_id' => $branch_id]
    )->fetchColumn();

    // Today's Reports
    $stats['today_reports'] = $db->query(
        "SELECT COUNT(*) FROM test_results 
         WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()",
        ['branch_id' => $branch_id]
    )->fetchColumn();

    // Monthly Revenue
    $stats['monthly_revenue'] = $db->query(
        "SELECT COALESCE(SUM(amount), 0) FROM payments 
         WHERE branch_id = :branch_id AND MONTH(payment_date) = MONTH(CURRENT_DATE())",
        ['branch_id' => $branch_id]
    )->fetchColumn();

    // Monthly Statistics for Chart
    $monthly_stats = $db->query(
        "SELECT 
            MONTH(created_at) as month,
            COUNT(*) as count
         FROM test_requests 
         WHERE branch_id = :branch_id 
         AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
         GROUP BY MONTH(created_at)
         ORDER BY month DESC
         LIMIT 6",
        ['branch_id' => $branch_id]
    )->fetchAll();

    $stats['monthly_stats'] = array_column($monthly_stats, 'count');

    // Test Categories Distribution
    $category_stats = $db->query(
        "SELECT 
            tc.name,
            COUNT(tr.request_id) as count
         FROM test_categories tc
         LEFT JOIN test_requests tr ON tc.category_id = tr.category_id
         WHERE tc.branch_id = :branch_id
         GROUP BY tc.category_id
         ORDER BY count DESC
         LIMIT 5",
        ['branch_id' => $branch_id]
    )->fetchAll();

    $stats['category_labels'] = array_column($category_stats, 'name');
    $stats['category_counts'] = array_column($category_stats, 'count');

    // Recent Activities
    $recent_activities = $db->query(
        "SELECT * FROM (
            SELECT 
                'test_request' as type,
                tr.request_id as id,
                p.first_name,
                p.last_name,
                tr.created_at,
                tr.status
            FROM test_requests tr
            JOIN patients p ON tr.patient_id = p.patient_id
            WHERE tr.branch_id = :branch_id
            UNION ALL
            SELECT 
                'test_result' as type,
                tr.result_id as id,
                p.first_name,
                p.last_name,
                tr.created_at,
                tr.status
            FROM test_results tr
            JOIN patients p ON tr.patient_id = p.patient_id
            WHERE tr.branch_id = :branch_id
        ) activities 
        ORDER BY created_at DESC 
        LIMIT 10",
        ['branch_id' => $branch_id]
    )->fetchAll();

    $stats['recent_activities'] = array_map(function($activity) {
        return [
            'type' => $activity['type'],
            'name' => htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']),
            'time' => date('M d, H:i', strtotime($activity['created_at'])),
            'status' => htmlspecialchars($activity['status'])
        ];
    }, $recent_activities);

    // Set cache control headers
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-Type: application/json');
    
    echo json_encode($stats);

} catch (Exception $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Failed to fetch dashboard statistics',
        'message' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}