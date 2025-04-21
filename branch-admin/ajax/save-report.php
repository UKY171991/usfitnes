<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $branch_id = $_SESSION['branch_id'];
    $patient_id = $_POST['patient_id'] ?? '';
    $test_id = $_POST['test_id'] ?? '';
    $price = $_POST['price'] ?? 0;
    $paid_amount = $_POST['paid_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (!$patient_id || !$test_id || !$payment_method) {
        throw new Exception('Required fields are missing');
    }

    // Validate that the test belongs to this branch
    $test_stmt = $conn->prepare("SELECT 1 FROM branch_tests WHERE branch_id = ? AND test_id = ?");
    $test_stmt->execute([$branch_id, $test_id]);
    if (!$test_stmt->fetch()) {
        throw new Exception('Invalid test selected');
    }

    // Start transaction
    $conn->beginTransaction();

    // Create report
    $report_stmt = $conn->prepare("
        INSERT INTO reports (patient_id, test_id, status, created_at) 
        VALUES (?, ?, 'pending', NOW())
    ");
    $report_stmt->execute([$patient_id, $test_id]);
    $report_id = $conn->lastInsertId();

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
            ? - ?,
            ?,
            ?,
            CURDATE(),
            ?,
            NOW()
        )
    ");
    $payment_stmt->execute([
        $report_id,
        $patient_id,
        $branch_id,
        $price,
        $paid_amount,
        $price,
        $paid_amount,
        $payment_method,
        $transaction_id,
        $_SESSION['user_id']
    ]);

    // Log activity
    $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        "Created new report #$report_id with payment"
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Report created successfully',
        'report_id' => $report_id
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