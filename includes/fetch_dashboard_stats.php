<?php
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => SESSION_LIFETIME
]);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    $db = Database::getInstance();
    $branch_id = $_SESSION['branch_id'];

    // Fetch current stats
    $stats = [];

    // Total Patients
    $stmt = $db->query(
        "SELECT COUNT(*) FROM patients WHERE branch_id = :branch_id",
        ['branch_id' => $branch_id]
    );
    $stats['patients'] = $stmt->fetchColumn();

    // Pending Tests
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_requests WHERE branch_id = :branch_id AND status = 'pending'",
        ['branch_id' => $branch_id]
    );
    $stats['pending_tests'] = $stmt->fetchColumn();

    // Today's Reports
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_results WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()",
        ['branch_id' => $branch_id]
    );
    $stats['today_reports'] = $stmt->fetchColumn();

    // Monthly Revenue
    $stmt = $db->query(
        "SELECT COALESCE(SUM(amount), 0) FROM payments 
        WHERE branch_id = :branch_id AND MONTH(payment_date) = MONTH(CURRENT_DATE())",
        ['branch_id' => $branch_id]
    );
    $stats['monthly_revenue'] = $stmt->fetchColumn();

    // Monthly Test Statistics
    $stmt = $db->query(
        "SELECT 
            MONTH(created_at) as month,
            COUNT(*) as count
        FROM test_requests 
        WHERE branch_id = :branch_id 
        AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY MONTH(created_at)
        ORDER BY month",
        ['branch_id' => $branch_id]
    );
    $monthly_stats = array_fill(0, 6, 0);
    while ($row = $stmt->fetch()) {
        $monthly_stats[$row['month'] - 1] = (int)$row['count'];
    }
    $stats['monthly_stats'] = $monthly_stats;

    // Test Categories Distribution
    $stmt = $db->query(
        "SELECT 
            tc.name,
            COUNT(*) as count
        FROM test_requests tr
        JOIN test_categories tc ON tr.category_id = tc.category_id
        WHERE tr.branch_id = :branch_id
        GROUP BY tc.name
        ORDER BY count DESC
        LIMIT 5",
        ['branch_id' => $branch_id]
    );
    $category_stats = [];
    while ($row = $stmt->fetch()) {
        $category_stats[] = (int)$row['count'];
    }
    $stats['category_stats'] = $category_stats;

    // Return the stats
    echo json_encode($stats);

} catch (Exception $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch dashboard stats']);
} 