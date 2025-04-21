<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $report_id = $_POST['report_id'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $payment_mode = $_POST['payment_mode'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';

    if (!$report_id || !$amount || !$payment_mode) {
        throw new Exception('Required fields are missing');
    }

    if (!in_array($payment_mode, ['cash', 'card', 'upi', 'netbanking'])) {
        throw new Exception('Invalid payment method');
    }

    if ($payment_mode !== 'cash' && !$transaction_id) {
        throw new Exception('Transaction ID is required for non-cash payments');
    }

    $branch_id = $_SESSION['branch_id'];

    // Start transaction
    $conn->beginTransaction();

    // Get report and verify it belongs to this branch
    $report_stmt = $conn->prepare("
        SELECT r.*, t.price as test_price, p.id as patient_id
        FROM reports r
        JOIN tests t ON r.test_id = t.id
        JOIN patients p ON r.patient_id = p.id
        JOIN branch_tests bt ON r.test_id = bt.test_id
        WHERE r.id = ? AND bt.branch_id = ?
    ");
    $report_stmt->execute([$report_id, $branch_id]);
    $report = $report_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        throw new Exception('Report not found or access denied');
    }

    // Get total amount already paid
    $paid_stmt = $conn->prepare("
        SELECT COALESCE(SUM(paid_amount), 0) as total_paid
        FROM payments
        WHERE patient_id = ? AND branch_id = ?
    ");
    $paid_stmt->execute([$report['patient_id'], $branch_id]);
    $total_paid = $paid_stmt->fetchColumn();

    // Verify payment amount doesn't exceed remaining balance
    $remaining = $report['test_price'] - $total_paid;
    if ($amount > $remaining) {
        throw new Exception('Payment amount exceeds remaining balance');
    }

    // Create payment record
    $payment_stmt = $conn->prepare("
        INSERT INTO payments (
            invoice_no,
            patient_id,
            branch_id,
            total_amount,
            paid_amount,
            due_amount,
            payment_mode,
            transaction_id,
            payment_date,
            created_by,
            created_at
        ) VALUES (
            CONCAT('INV-', LPAD(?, 6, '0')),
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            CURDATE(),
            ?,
            NOW()
        )
    ");
    $payment_stmt->execute([
        $report_id,
        $report['patient_id'],
        $branch_id,
        $report['test_price'],
        $amount,
        $report['test_price'] - ($total_paid + $amount),
        $payment_mode,
        $transaction_id,
        $_SESSION['user_id']
    ]);

    // Log activity
    $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        "Recorded payment of ₹$amount for report #$report_id"
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 