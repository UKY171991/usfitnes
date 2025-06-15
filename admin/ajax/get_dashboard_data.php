<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

// Get date range filter from AJAX request
$date_range = $_POST['date_range'] ?? 'today';
$custom_start = $_POST['start_date'] ?? '';
$custom_end = $_POST['end_date'] ?? '';

// Set date range based on filter
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
        $start_date = $custom_start;
        $end_date = $custom_end;
        break;
    default: // today
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
}

$response_data = [];

try {
    // Prepare end date for range comparison (exclusive)
    $end_date_exclusive = date('Y-m-d', strtotime($end_date . ' +1 day'));

    // Period specific statistics
    $period_stats = [];
    $period_stmt_new_patients = $conn->prepare("SELECT COUNT(*) FROM patients WHERE created_at >= ? AND created_at < ?");
    $period_stmt_new_patients->execute([$start_date, $end_date_exclusive]);
    $period_stats['new_patients'] = $period_stmt_new_patients->fetchColumn() ?? 0;

    $period_stmt_completed = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = 'completed' AND created_at >= ? AND created_at < ?");
    $period_stmt_completed->execute([$start_date, $end_date_exclusive]);
    $period_stats['completed_reports'] = $period_stmt_completed->fetchColumn() ?? 0;

    $period_stmt_pending = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = 'pending' AND created_at >= ? AND created_at < ?");
    $period_stmt_pending->execute([$start_date, $end_date_exclusive]);
    $period_stats['pending_reports'] = $period_stmt_pending->fetchColumn() ?? 0;

    $period_stmt_revenue = $conn->prepare("SELECT COALESCE(SUM(paid_amount), 0) FROM payments WHERE created_at >= ? AND created_at < ?");
    $period_stmt_revenue->execute([$start_date, $end_date_exclusive]);
    $period_stats['period_revenue'] = $period_stmt_revenue->fetchColumn() ?? 0;
    $response_data['period_stats'] = $period_stats;

    // Overall statistics (These might also be affected by date range in some interpretations, but typically are totals)
    // For this implementation, we will calculate them based on the selected date range as well.
    // If they should always be true totals, the WHERE clauses related to dates should be removed from these specific queries.
    $overall_stats = [];

    // Filter all overall stats by the selected date range where applicable
    $stmt_branches = $conn->prepare("SELECT COUNT(*) FROM branches WHERE status = 1 AND created_at >= ? AND created_at < ?");
    $stmt_branches->execute([$start_date, $end_date_exclusive]);
    $overall_stats['branches'] = $stmt_branches->fetchColumn() ?? 0;

    $stmt_test_categories = $conn->prepare("SELECT COUNT(*) FROM test_categories WHERE status = 1 AND created_at >= ? AND created_at < ?");
    $stmt_test_categories->execute([$start_date, $end_date_exclusive]);
    $overall_stats['test_categories'] = $stmt_test_categories->fetchColumn() ?? 0;

    $stmt_active_users = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 1 AND created_at >= ? AND created_at < ?");
    $stmt_active_users->execute([$start_date, $end_date_exclusive]);
    $overall_stats['active_users'] = $stmt_active_users->fetchColumn() ?? 0;
    
    $stmt_total_patients = $conn->prepare("SELECT COUNT(*) FROM patients WHERE created_at >= ? AND created_at < ?");
    $stmt_total_patients->execute([$start_date, $end_date_exclusive]);
    $overall_stats['total_patients'] = $stmt_total_patients->fetchColumn() ?? 0;

    $stmt_test_master_all = $conn->prepare("SELECT COUNT(*) FROM tests WHERE created_at >= ? AND created_at < ?");
    $stmt_test_master_all->execute([$start_date, $end_date_exclusive]);
    $overall_stats['test_master_all'] = $stmt_test_master_all->fetchColumn() ?? 0;

    $stmt_available_tests = $conn->prepare("SELECT COUNT(*) FROM tests WHERE status = 1 AND created_at >= ? AND created_at < ?");
    $stmt_available_tests->execute([$start_date, $end_date_exclusive]);
    $overall_stats['available_tests'] = $stmt_available_tests->fetchColumn() ?? 0;

    $stmt_total_reports = $conn->prepare("SELECT COUNT(*) FROM reports WHERE created_at >= ? AND created_at < ?");
    $stmt_total_reports->execute([$start_date, $end_date_exclusive]);
    $overall_stats['total_reports'] = $stmt_total_reports->fetchColumn() ?? 0;

    $response_data['overall_stats'] = $overall_stats;

    // Recent payments
    $payment_stmt = $conn->prepare("
        SELECT 
            p.id,
            p.paid_amount,
            p.payment_mode,
            p.created_at,
            pt.name as patient_name,
            (SELECT t_sub.test_name 
             FROM tests t_sub 
             JOIN reports r_sub ON t_sub.id = r_sub.test_id 
             WHERE r_sub.patient_id = p.patient_id 
             ORDER BY r_sub.created_at DESC 
             LIMIT 1) as test_name,
            b.branch_name
        FROM payments p
        LEFT JOIN patients pt ON p.patient_id = pt.id
        LEFT JOIN branches b ON p.branch_id = b.id
        WHERE DATE(p.created_at) BETWEEN ? AND ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $payment_stmt->execute([$start_date, $end_date]);
    $response_data['recent_payments'] = $payment_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activities
    $activity_stmt = $conn->prepare("
        SELECT a.*, u.name as user_name 
        FROM activities a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.created_at) BETWEEN ? AND ?
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $activity_stmt->execute([$start_date, $end_date]);
    $response_data['activities'] = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    // Log to server error log
    error_log("PDOException in get_dashboard_data.php: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\\\\nTrace: " . $e->getTraceAsString());
    
    // Send generic error to client
    $response_data['error'] = 'Database operation failed. Check server logs for details.';
    // $response_data['debug_error'] = $e->getMessage(); // Keep this commented out for production
    
} catch (Exception $e) {
    // Catch any other general exceptions
    error_log("AJAX Dashboard Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $response_data['error'] = 'An unexpected error occurred. Check server logs for details.';
    // $response_data['debug_error'] = $e->getMessage(); // Optionally send detailed error to client during development
    if (!headers_sent()) {
        http_response_code(500);
    }
}

if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response_data);
?>
