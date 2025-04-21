<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
if (!isset($_POST['payment_id']) || !isset($_POST['amount']) || !isset($_POST['payment_mode'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$payment_id = intval($_POST['payment_id']);
$amount = floatval($_POST['amount']);
$payment_mode = $_POST['payment_mode'];
$transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : null;
$branch_id = $_SESSION['branch_id'];

// Validate payment mode
$valid_payment_modes = ['cash', 'card', 'upi', 'netbanking'];
if (!in_array($payment_mode, $valid_payment_modes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment mode']);
    exit;
}

// Validate transaction ID for non-cash payments
if ($payment_mode !== 'cash' && empty($transaction_id)) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required for non-cash payments']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Get the existing payment and verify it belongs to the current branch
    $stmt = $conn->prepare("
        SELECT p.*, r.test_id, t.price as test_price 
        FROM payments p
        JOIN reports r ON p.patient_id = r.patient_id
        JOIN tests t ON r.test_id = t.id
        WHERE p.id = ? AND p.branch_id = ?
    ");
    $stmt->execute([$payment_id, $branch_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception('Payment not found or access denied');
    }

    // Calculate total paid amount including the new payment
    $total_paid = $payment['paid_amount'] + $amount;
    if ($total_paid > $payment['total_amount']) {
        throw new Exception('Payment amount exceeds the total amount due');
    }

    // Update the payment record
    $stmt = $conn->prepare("
        UPDATE payments 
        SET paid_amount = ?,
            due_amount = total_amount - ?,
            payment_mode = ?,
            transaction_id = ?,
            updated_at = NOW()
        WHERE id = ? AND branch_id = ?
    ");
    $stmt->execute([
        $total_paid,
        $total_paid,
        $payment_mode,
        $transaction_id,
        $payment_id,
        $branch_id
    ]);

    // Log the activity
    $stmt = $conn->prepare("
        INSERT INTO activities (user_id, description) 
        VALUES (?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        "Updated payment #" . $payment['invoice_no'] . " with additional amount of ₹" . number_format($amount, 2)
    ]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 