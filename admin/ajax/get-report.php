<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

$report_id = $_GET['id'];

try {
    // Get report details with related information
    $stmt = $conn->prepare("
        SELECT 
            r.*,
            p.name as patient_name,
            t.test_name,
            t.price as test_price,
            b.branch_name,
            COALESCE((
                SELECT SUM(py.paid_amount)
                FROM payments py
                WHERE py.patient_id = r.patient_id
                AND py.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
            ), 0) as total_amount
        FROM reports r
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN tests t ON r.test_id = t.id
        LEFT JOIN branches b ON p.branch_id = b.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }
    
    // Format dates and numbers
    $report['created_at'] = date('Y-m-d H:i:s', strtotime($report['created_at']));
    $report['total_amount'] = number_format($report['total_amount'], 2);
    
    echo json_encode([
        'success' => true,
        'report' => $report
    ]);

} catch (PDOException $e) {
    error_log("Error fetching report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching the report'
    ]);
} 