<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];

// Get payments for the branch
$stmt = $conn->prepare("
    SELECT 
        p.*,
        pt.name as patient_name,
        t.test_name,
        t.price as test_price
    FROM payments p
    JOIN patients pt ON p.patient_id = pt.id
    JOIN reports r ON p.patient_id = r.patient_id
    JOIN tests t ON r.test_id = t.id
    WHERE p.branch_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$branch_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Payments</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Patient</th>
                        <th>Test</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Payment Mode</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($payments)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No payments found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['invoice_no']); ?></td>
                                <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['test_name']); ?></td>
                                <td>₹<?php echo number_format($payment['total_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($payment['paid_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($payment['due_amount'], 2); ?></td>
                                <td><?php echo ucfirst($payment['payment_mode']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal<?php echo $payment['id']; ?>"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($payment['due_amount'] > 0): ?>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $payment['id']; ?>"
                                                title="Update Payment">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="printReceipt(<?php echo $payment['id']; ?>)"
                                                title="Print Receipt">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Payment Modal -->
                            <div class="modal fade" id="viewModal<?php echo $payment['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Payment Details #<?php echo $payment['invoice_no']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($payment['invoice_no']); ?></p>
                                            <p><strong>Patient:</strong> <?php echo htmlspecialchars($payment['patient_name']); ?></p>
                                            <p><strong>Test:</strong> <?php echo htmlspecialchars($payment['test_name']); ?></p>
                                            <p><strong>Total Amount:</strong> ₹<?php echo number_format($payment['total_amount'], 2); ?></p>
                                            <p><strong>Paid Amount:</strong> ₹<?php echo number_format($payment['paid_amount'], 2); ?></p>
                                            <p><strong>Due Amount:</strong> ₹<?php echo number_format($payment['due_amount'], 2); ?></p>
                                            <p><strong>Payment Mode:</strong> <?php echo ucfirst($payment['payment_mode']); ?></p>
                                            <?php if($payment['transaction_id']): ?>
                                            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Payment Date:</strong> <?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <?php if($payment['due_amount'] > 0): ?>
                                            <button type="button" class="btn btn-primary" 
                                                    data-bs-dismiss="modal"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $payment['id']; ?>">
                                                Update Payment
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Payment Modal -->
                            <?php if($payment['due_amount'] > 0): ?>
                            <div class="modal fade" id="editModal<?php echo $payment['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Payment #<?php echo $payment['invoice_no']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="editPaymentForm<?php echo $payment['id']; ?>" 
                                                  action="ajax/update-payment.php" method="POST">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Total Amount</label>
                                                    <input type="number" class="form-control" 
                                                           value="<?php echo $payment['total_amount']; ?>" readonly>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Previously Paid</label>
                                                    <input type="number" class="form-control" 
                                                           value="<?php echo $payment['paid_amount']; ?>" readonly>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Due Amount</label>
                                                    <input type="number" class="form-control" 
                                                           value="<?php echo $payment['due_amount']; ?>" readonly>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Additional Payment Amount *</label>
                                                    <input type="number" class="form-control" name="amount" required 
                                                           min="0" max="<?php echo $payment['due_amount']; ?>" 
                                                           step="0.01">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Payment Mode *</label>
                                                    <select class="form-select payment-mode-select" name="payment_mode" required>
                                                        <option value="">Select Payment Mode</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="card">Card</option>
                                                        <option value="upi">UPI</option>
                                                        <option value="netbanking">Net Banking</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 transaction-id-group">
                                                    <label class="form-label">Transaction ID</label>
                                                    <input type="text" class="form-control" name="transaction_id">
                                                    <small class="text-muted">Required for Card/UPI/Net Banking payments</small>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary update-payment-btn">Update Payment</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment mode changes
    document.querySelectorAll('.payment-mode-select').forEach(select => {
        select.addEventListener('change', function() {
            const transactionIdGroup = this.closest('.modal-body').querySelector('.transaction-id-group');
            const transactionIdInput = transactionIdGroup.querySelector('input[name="transaction_id"]');
            
            if (this.value === 'cash') {
                transactionIdGroup.style.display = 'none';
                transactionIdInput.required = false;
                transactionIdInput.value = '';
            } else {
                transactionIdGroup.style.display = 'block';
                transactionIdInput.required = true;
            }
        });
    });

    // Handle update button clicks
    document.querySelectorAll('.update-payment-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const modal = this.closest('.modal');
            const form = modal.querySelector('form');
            const formData = new FormData(form);
            
            // Disable button and show loading state
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            fetch('ajax/update-payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Close the modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                bsModal.hide();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred while updating the payment.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again.'
                });
            })
            .finally(() => {
                // Reset button state
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });

    // Set initial state for payment mode
    document.querySelectorAll('.payment-mode-select').forEach(select => {
        select.dispatchEvent(new Event('change'));
    });

    // Function to print payment receipt
    window.printReceipt = function(paymentId) {
        const printWindow = window.open(`print-payment.php?id=${paymentId}`, '_blank', 'width=800,height=600');
        if (printWindow) {
            printWindow.focus();
            // Add event listener to automatically trigger print when the page loads
            printWindow.onload = function() {
                printWindow.print();
            };
        } else {
            alert('Please allow popups to print the receipt');
        }
    }
});
</script>

<?php include '../inc/footer.php'; ?> 