<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];
$payment_id = $_GET['id'] ?? 0;

// Verify payment belongs to this branch
$stmt = $conn->prepare("
    SELECT 
        p.*,
        pt.name as patient_name,
        r.id as report_id,
        t.test_name
    FROM payments p
    JOIN patients pt ON p.patient_id = pt.id
    JOIN reports r ON p.report_id = r.id
    JOIN tests t ON r.test_id = t.id
    WHERE p.id = ? AND p.branch_id = ?
");
$stmt->execute([$payment_id, $branch_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header("Location: payments.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (!empty($status) && !empty($payment_method)) {
        try {
            $stmt = $conn->prepare("
                UPDATE payments 
                SET status = ?, payment_method = ?, transaction_id = ?, notes = ?
                WHERE id = ? AND branch_id = ?
            ");
            $stmt->execute([$status, $payment_method, $transaction_id, $notes, $payment_id, $branch_id]);

            // Log activity
            $activity = "Updated payment #$payment_id status to " . ucfirst($status);
            $stmt = $conn->prepare("
                INSERT INTO activities (user_id, activity_type, description, branch_id) 
                VALUES (?, 'payment_update', ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $activity, $branch_id]);

            header("Location: payments.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating payment: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Update Payment</h1>
    <a href="payments.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Patient Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['patient_name']); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Test Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['test_name']); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="text" class="form-control" value="₹<?php echo number_format($payment['amount'], 2); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Payment Status *</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending" <?php echo $payment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $payment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $payment['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash" <?php echo $payment['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="card" <?php echo $payment['payment_method'] == 'card' ? 'selected' : ''; ?>>Card</option>
                            <option value="upi" <?php echo $payment['payment_method'] == 'upi' ? 'selected' : ''; ?>>UPI</option>
                            <option value="netbanking" <?php echo $payment['payment_method'] == 'netbanking' ? 'selected' : ''; ?>>Net Banking</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                               value="<?php echo htmlspecialchars($payment['transaction_id']); ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Payment
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 