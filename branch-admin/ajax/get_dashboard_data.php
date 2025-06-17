<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

// Helper functions for fetching statistics
function getBranchTotalPatients($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchTotalReports($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchTotalRevenue($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchAvailableTests($conn, $branch_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM branch_tests WHERE branch_id = ? AND status = 1");
    $stmt->execute([$branch_id]);
    return $stmt->fetchColumn() ?? 0;
}

function getBranchNewPatientsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ?");
        $stmt->execute([$branch_id]);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchCompletedReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'completed'");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'completed' AND DATE(r.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchPendingReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'pending'");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COUNT(r.id) FROM reports r JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND r.status = 'pending' AND DATE(r.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

function getBranchRevenueForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time = false) {
    if ($is_all_time) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ?");
        $stmt->execute([$branch_id]);
    } else {
        if (empty($start_date) || empty($end_date)) { return 0; } // Basic guard
        $stmt = $conn->prepare("SELECT COALESCE(SUM(py.paid_amount), 0) FROM payments py JOIN reports r ON py.report_id = r.id JOIN patients p ON r.patient_id = p.id WHERE p.branch_id = ? AND DATE(py.created_at) BETWEEN ? AND ?");
        $stmt->execute([$branch_id, $start_date, $end_date]);
    }
    return $stmt->fetchColumn() ?? 0;
}

try {
    $branch_id = $_SESSION['branch_id'];
    $response = ['success' => true];

    // Log request for debugging
    error_log("Dashboard data request for branch ID: " . $branch_id);

    // Get date range filter
    $date_range = $_GET['date_range'] ?? 'today';
    $custom_start = $_GET['start_date'] ?? '';
    $custom_end = $_GET['end_date'] ?? '';

    $is_all_time_filter = ($date_range === 'all');

    // Set date range based on filter
    if ($is_all_time_filter) {
        $start_date = null; 
        $end_date = null;   
    } else {
        switch($date_range) {
            case 'week':
                $start_date = date('Y-m-d', strtotime('-7 days'));
                $end_date = date('Y-m-d');
                break;
            case 'month':
                $start_date = date('Y-m-01');
                $end_date = date('Y-m-d');
                break;
            case 'custom':
                if (empty($custom_start) || empty($custom_end) || strtotime($custom_start) > strtotime($custom_end)) {
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d');
                } else {
                    $start_date = $custom_start;
                    $end_date = $custom_end;
                }
                break;
            case 'today':
            default:
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
                break;
        }
    }

    // Get branch details
    $branch_stmt = $conn->prepare("SELECT * FROM branches WHERE id = ?");
    $branch_stmt->execute([$branch_id]);
    $branch = $branch_stmt->fetch(PDO::FETCH_ASSOC);
    $response['branch'] = $branch;

    // Basic statistics for the branch
    $response['stats'] = [
        'total_patients' => getBranchTotalPatients($conn, $branch_id),
        'total_reports' => getBranchTotalReports($conn, $branch_id),
        'total_revenue' => getBranchTotalRevenue($conn, $branch_id),
        'available_tests' => getBranchAvailableTests($conn, $branch_id)
    ];

    // Period specific statistics
    $response['period_stats'] = [
        'new_patients' => getBranchNewPatientsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'completed_reports' => getBranchCompletedReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'pending_reports' => getBranchPendingReportsForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter),
        'period_revenue' => getBranchRevenueForPeriod($conn, $branch_id, $start_date, $end_date, $is_all_time_filter)
    ];

    // Popular tests in this branch (respecting date filter for report counts and revenue)
    $sql_popular_tests = "
        SELECT
            t.test_name,
            COUNT(DISTINCT r.id) AS report_count,
            COALESCE(SUM(py.paid_amount), 0) AS revenue
        FROM tests t
        INNER JOIN branch_tests bt ON t.id = bt.test_id AND bt.status = 1 -- Active tests in branch
        LEFT JOIN reports r ON r.test_id = t.id
    ";
    // Conditionally add date filter for reports
    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_popular_tests .= " AND DATE(r.created_at) BETWEEN :start_date AND :end_date ";
    }
    $sql_popular_tests .= "
        LEFT JOIN patients p ON r.patient_id = p.id AND p.branch_id = bt.branch_id -- Report's patient must be in the same branch
        LEFT JOIN payments py ON py.report_id = r.id
        WHERE bt.branch_id = :branch_id
        GROUP BY t.id, t.test_name
        ORDER BY report_count DESC, revenue DESC
        LIMIT 5
    ";
    $params_popular_tests = [':branch_id' => $branch_id];
    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $params_popular_tests[':start_date'] = $start_date;
        $params_popular_tests[':end_date'] = $end_date;
    }
    $popular_tests_stmt = $conn->prepare($sql_popular_tests);
    $popular_tests_stmt->execute($params_popular_tests);
    $response['popular_tests'] = $popular_tests_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent payments
    $sql_recent_payments = "
        SELECT 
            py.id,
            py.invoice_no,
            py.paid_amount,
            py.discount,
            py.total_amount,
            py.payment_mode,
            py.payment_mode as payment_method, -- Using both column names for backward compatibility
            py.transaction_id,
            py.payment_date,
            py.created_at,
            p.name as patient_name,
            t.test_name
        FROM payments py
        JOIN reports r ON py.report_id = r.id
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        WHERE p.branch_id = ?";
    $params_recent_payments = [$branch_id];

    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_recent_payments .= " AND DATE(py.created_at) BETWEEN ? AND ?";
        $params_recent_payments[] = $start_date;
        $params_recent_payments[] = $end_date;
    }
    $sql_recent_payments .= " ORDER BY py.created_at DESC LIMIT 5";    $stmt = $conn->prepare($sql_recent_payments);
    $stmt->execute($params_recent_payments);
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add backward compatibility for payment_method/payment_mode
    foreach ($recent_payments as &$payment) {
        if (isset($payment['payment_mode']) && !isset($payment['payment_method'])) {
            $payment['payment_method'] = $payment['payment_mode'];
        } elseif (isset($payment['payment_method']) && !isset($payment['payment_mode'])) {
            $payment['payment_mode'] = $payment['payment_method'];
        }
    }
    
    $response['recent_payments'] = $recent_payments;

    // Recent reports
    $sql_recent_reports = "
        SELECT 
            r.id,
            r.status,
            r.created_at,
            p.name as patient_name,
            t.test_name
        FROM reports r
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        WHERE p.branch_id = ?";
    $params_recent_reports = [$branch_id];

    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_recent_reports .= " AND DATE(r.created_at) BETWEEN ? AND ?";
        $params_recent_reports[] = $start_date;
        $params_recent_reports[] = $end_date;
    }
    $sql_recent_reports .= " ORDER BY r.created_at DESC LIMIT 5";
    $report_stmt = $conn->prepare($sql_recent_reports);
    $report_stmt->execute($params_recent_reports);
    $response['recent_reports'] = $report_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent test results (respecting date filter)
    $sql_recent_results = "
        SELECT
            r.id,
            r.result,
            r.created_at,
            p.name as patient_name,
            t.test_name
        FROM reports r
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        WHERE p.branch_id = :branch_id
        AND r.status = 'completed'
    ";
    $params_recent_results = [':branch_id' => $branch_id];

    if (!$is_all_time_filter && !empty($start_date) && !empty($end_date)) {
        $sql_recent_results .= " AND DATE(r.created_at) BETWEEN :start_date AND :end_date ";
        $params_recent_results[':start_date'] = $start_date;
        $params_recent_results[':end_date'] = $end_date;
    }
    $sql_recent_results .= " ORDER BY r.created_at DESC LIMIT 5 ";

    $stmt_recent_results = $conn->prepare($sql_recent_results);
    $stmt_recent_results->execute($params_recent_results);
    $response['recent_results'] = $stmt_recent_results->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    // Log error details
    error_log("Dashboard data error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
