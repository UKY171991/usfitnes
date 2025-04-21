<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Report ID is required');
    }

    $report_id = intval($_GET['id']);
    $branch_id = $_SESSION['branch_id'];

    // Get report details with all necessary information
    $stmt = $conn->prepare("
        SELECT 
            r.*,
            p.name as patient_name,
            t.test_name,
            t.normal_range,
            t.unit,
            t.price as test_price,
            COALESCE((
                SELECT SUM(py.paid_amount) 
                FROM payments py
                WHERE py.patient_id = r.patient_id 
                AND py.branch_id = ?
                AND py.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
            ), 0) as paid_amount,
            (t.price - COALESCE((
                SELECT SUM(py2.paid_amount) 
                FROM payments py2
                WHERE py2.patient_id = r.patient_id 
                AND py2.branch_id = ?
                AND py2.invoice_no = CONCAT('INV-', LPAD(r.id, 6, '0'))
            ), 0)) as due_amount
        FROM reports r
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        JOIN branch_tests bt ON t.id = bt.test_id
        WHERE r.id = ? AND bt.branch_id = ?
    ");

    $stmt->execute([$branch_id, $branch_id, $report_id, $branch_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        throw new Exception('Report not found or access denied');
    }

    echo json_encode([
        'success' => true,
        'report' => $report
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 