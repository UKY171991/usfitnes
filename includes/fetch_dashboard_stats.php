<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => SESSION_LIFETIME
]);

// Check if user is logged in and has valid session
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

// Check if branch_id exists
if (!isset($_SESSION['branch_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Branch not selected']));
}

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    $db = Database::getInstance();
    $branch_id = $_SESSION['branch_id'];

    // Validate branch_id
    $stmt = $db->query(
        "SELECT branch_id FROM branches WHERE branch_id = :branch_id",
        ['branch_id' => $branch_id]
    );
    if (!$stmt->fetch()) {
        throw new Exception('Invalid branch');
    }

    // Initialize stats array
    $stats = [
        'patients' => 0,
        'pending_tests' => 0,
        'today_reports' => 0,
        'monthly_revenue' => 0,
        'monthly_stats' => array_fill(0, 6, 0),
        'category_stats' => [],
        'category_labels' => []
    ];

    // Total Patients
    $stmt = $db->query(
        "SELECT COUNT(*) FROM patients WHERE branch_id = :branch_id",
        ['branch_id' => $branch_id]
    );
    $stats['patients'] = (int)$stmt->fetchColumn();

    // Pending Tests
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_requests WHERE branch_id = :branch_id AND status = 'pending'",
        ['branch_id' => $branch_id]
    );
    $stats['pending_tests'] = (int)$stmt->fetchColumn();

    // Today's Reports
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_results WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()",
        ['branch_id' => $branch_id]
    );
    $stats['today_reports'] = (int)$stmt->fetchColumn();

    // Monthly Revenue
    $stmt = $db->query(
        "SELECT COALESCE(SUM(amount), 0) FROM payments 
        WHERE branch_id = :branch_id AND MONTH(payment_date) = MONTH(CURRENT_DATE())",
        ['branch_id' => $branch_id]
    );
    $stats['monthly_revenue'] = (float)$stmt->fetchColumn();

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
    while ($row = $stmt->fetch()) {
        $month_index = ((int)$row['month'] - 1) % 6;
        $stats['monthly_stats'][$month_index] = (int)$row['count'];
    }

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
    while ($row = $stmt->fetch()) {
        $stats['category_labels'][] = $row['name'];
        $stats['category_stats'][] = (int)$row['count'];
    }

    // Return the stats with proper JSON encoding
    echo json_encode($stats, JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch dashboard stats',
        'message' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
} 