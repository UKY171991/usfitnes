<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkBranchAdminAccess();

$branch_id = $_SESSION['branch_id'];

// Handle new payment
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = $_POST['report_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    if(!empty($report_id) && !empty($amount) && !empty($payment_method)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO payments (report_id, amount, payment_method, transaction_id, branch_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$report_id, $amount, $payment_method, $transaction_id, $branch_id]);
            
            // Update report payment status
            $stmt = $conn->prepare("UPDATE reports SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$report_id]);
            
            // Log activity
            $activity = "Payment received for Report #$report_id - ₹$amount";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: payments.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error recording payment: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all payments for this branch with report and patient details
$payments = $conn->prepare("
    SELECT p.*, r.id as report_id, pt.name as patient_name, t.name as test_name
    FROM payments p
    LEFT JOIN reports r ON p.report_id = r.id
    LEFT JOIN patients pt ON r.patient_id = pt.id
    LEFT JOIN tests t ON r.test_id = t.id
    WHERE p.branch_id = ?
    ORDER BY p.created_at DESC
");
$payments->execute([$branch_id]);
$payments = $payments->fetchAll(PDO::FETCH_ASSOC);

// Get unpaid reports for dropdown
$unpaid_reports = $conn->prepare("
    SELECT r.id, r.patient_id, p.name as patient_name, t.name as test_name, t.price
    FROM reports r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN tests t ON r.test_id = t.id
    WHERE r.branch_id = ? AND r.payment_status = 'unpaid'
    ORDER BY r.created_at DESC
");
$unpaid_reports->execute([$branch_id]);
$unpaid_reports = $unpaid_reports->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Payments</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
        <i class="fas fa-plus"></i> Record Payment
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Payment recorded successfully!</div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Report ID</th>
                <th>Patient</th>
                <th>Test</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Transaction ID</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($payments as $payment): ?>
                <tr>
                    <td><?php echo $payment['id']; ?></td>
                    <td><?php echo $payment['report_id']; ?></td>
                    <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($payment['test_name']); ?></td>
                    <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></td>
                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($payment['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="report_id" class="form-label">Select Report *</label>
                        <select class="form-control" id="report_id" name="report_id" required>
                            <option value="">Select Report</option>
                            <?php foreach($unpaid_reports as $report): ?>
                                <option value="<?php echo $report['id']; ?>">
                                    #<?php echo $report['id']; ?> - 
                                    <?php echo htmlspecialchars($report['patient_name']); ?> - 
                                    <?php echo htmlspecialchars($report['test_name']); ?> - 
                                    ₹<?php echo number_format($report['price'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount *</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="netbanking">Net Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill amount when report is selected
document.getElementById('report_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if(selectedOption.value) {
        const price = selectedOption.text.split('₹')[1];
        document.getElementById('amount').value = price;
    }
});
</script>

<?php include '../inc/footer.php'; ?> 