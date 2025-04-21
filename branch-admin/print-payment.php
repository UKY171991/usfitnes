<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

if (!isset($_GET['id'])) {
    die('Payment ID is required');
}

$payment_id = intval($_GET['id']);
$branch_id = $_SESSION['branch_id'];

try {
    // First get the payment details
    $stmt = $conn->prepare("
        SELECT p.*, 
               COALESCE(b.name, '') as branch_name,
               COALESCE(b.address, '') as branch_address,
               COALESCE(b.phone, '') as branch_phone,
               COALESCE(b.email, '') as branch_email,
               COALESCE(pt.name, '') as patient_name,
               COALESCE(pt.phone, '') as patient_phone,
               COALESCE(pt.email, '') as patient_email
        FROM payments p
        LEFT JOIN branches b ON p.branch_id = b.id
        LEFT JOIN patients pt ON p.patient_id = pt.id
        WHERE p.id = ? AND p.branch_id = ?
    ");

    $stmt->execute([$payment_id, $branch_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        die('Payment not found or access denied');
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #<?php echo htmlspecialchars($payment['invoice_no']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                padding: 20px;
                font-size: 14px;
            }
            .no-print {
                display: none !important;
            }
            .receipt {
                border: none !important;
                box-shadow: none !important;
            }
        }
        .receipt {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            max-height: 100px;
            margin-bottom: 20px;
        }
        .receipt-header {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
        .receipt-footer {
            border-top: 2px solid #ddd;
            margin-top: 20px;
            padding-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt">
            <div class="text-end mb-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>

            <div class="receipt-header">
                <div class="row">
                    <div class="col-6">
                        <h4><?php echo htmlspecialchars($payment['branch_name']); ?></h4>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($payment['branch_address'])); ?></p>
                        <p class="mb-0">Phone: <?php echo htmlspecialchars($payment['branch_phone']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($payment['branch_email']); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <h4>PAYMENT RECEIPT</h4>
                        <p class="mb-0">Receipt No: <?php echo htmlspecialchars($payment['invoice_no']); ?></p>
                        <p class="mb-0">Date: <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></p>
                        <p>Time: <?php echo date('h:i A', strtotime($payment['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="patient-info mb-4">
                <h5>Patient Information</h5>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($payment['patient_name']); ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($payment['patient_phone']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($payment['patient_email']); ?></p>
                    </div>
                </div>
            </div>

            <div class="payment-details mb-4">
                <h5>Payment Details</h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Total Amount</th>
                            <td class="text-end">₹<?php echo number_format($payment['total_amount'], 2); ?></td>
                        </tr>
                        <?php if ($payment['discount'] > 0): ?>
                        <tr>
                            <td>Discount</td>
                            <td class="text-end">-₹<?php echo number_format($payment['discount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Paid Amount</th>
                            <td class="text-end">₹<?php echo number_format($payment['paid_amount'], 2); ?></td>
                        </tr>
                        <?php if ($payment['due_amount'] > 0): ?>
                        <tr>
                            <th>Due Amount</th>
                            <td class="text-end">₹<?php echo number_format($payment['due_amount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="payment-info mt-4">
                    <p><strong>Payment Mode:</strong> <?php echo ucfirst($payment['payment_mode']); ?></p>
                    <?php if ($payment['transaction_id']): ?>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                    <?php endif; ?>
                    <p><strong>Payment Date:</strong> <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></p>
                </div>
            </div>

            <div class="receipt-footer text-center">
                <p class="mb-0">Thank you for choosing our services!</p>
                <p class="mb-0">This is a computer generated receipt and does not require a signature.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 