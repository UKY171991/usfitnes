<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

if (!isset($_GET['id'])) {
    die('Report ID is required');
}

$report_id = intval($_GET['id']);
$branch_id = $_SESSION['branch_id'];

// Get report details
$stmt = $conn->prepare("
    SELECT 
        r.*,
        p.name as patient_name,
        p.phone as patient_phone,
        p.email as patient_email,
        t.test_name,
        t.normal_range,
        t.unit,
        t.price as test_price,
        b.name as branch_name,
        b.address as branch_address,
        b.phone as branch_phone,
        b.email as branch_email,
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
    JOIN branch_tests bt ON t.id = bt.test_id AND bt.branch_id = ?
    JOIN branches b ON b.id = ?
    WHERE r.id = ?
");

$stmt->execute([$branch_id, $branch_id, $branch_id, $branch_id, $report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die('Report not found or access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report #<?php echo $report['id']; ?></title>
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
            .report {
                border: none !important;
                box-shadow: none !important;
            }
        }
        .report {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .report-header {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
        .report-footer {
            border-top: 2px solid #ddd;
            margin-top: 20px;
            padding-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="report">
            <div class="text-end mb-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>

            <div class="report-header">
                <div class="row">
                    <div class="col-6">
                        <h4><?php echo htmlspecialchars($report['branch_name']); ?></h4>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($report['branch_address'])); ?></p>
                        <p class="mb-0">Phone: <?php echo htmlspecialchars($report['branch_phone']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($report['branch_email']); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <h4>TEST REPORT</h4>
                        <p class="mb-0">Report ID: <?php echo $report['id']; ?></p>
                        <p class="mb-0">Date: <?php echo date('d/m/Y', strtotime($report['created_at'])); ?></p>
                        <p>Status: <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span></p>
                    </div>
                </div>
            </div>

            <div class="patient-info mb-4">
                <h5>Patient Information</h5>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($report['patient_name']); ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($report['patient_phone']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($report['patient_email']); ?></p>
                    </div>
                </div>
            </div>

            <div class="test-details mb-4">
                <h5>Test Details</h5>
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1"><strong>Test Name:</strong> <?php echo htmlspecialchars($report['test_name']); ?></p>
                        <p class="mb-1"><strong>Normal Range:</strong> <?php echo htmlspecialchars($report['normal_range'] ?? 'N/A'); ?></p>
                        <p class="mb-1"><strong>Unit:</strong> <?php echo htmlspecialchars($report['unit'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <?php if($report['result']): ?>
            <div class="test-result mb-4">
                <h5>Test Result</h5>
                <div class="border rounded p-3">
                    <?php echo nl2br(htmlspecialchars($report['result'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($report['notes']): ?>
            <div class="notes mb-4">
                <h5>Notes</h5>
                <div class="border rounded p-3">
                    <?php echo nl2br(htmlspecialchars($report['notes'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="payment-details mb-4">
                <h5>Payment Details</h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Test Price</th>
                            <td class="text-end">₹<?php echo number_format($report['test_price'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Paid Amount</th>
                            <td class="text-end">₹<?php echo number_format($report['paid_amount'], 2); ?></td>
                        </tr>
                        <?php if($report['due_amount'] > 0): ?>
                        <tr>
                            <th>Due Amount</th>
                            <td class="text-end">₹<?php echo number_format($report['due_amount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="report-footer text-center">
                <p class="mb-0">This is a computer generated report.</p>
                <?php if($report['status'] == 'completed'): ?>
                <p class="mb-0">This report has been verified and approved.</p>
                <?php else: ?>
                <p class="mb-0 text-danger">This is a pending report and has not been verified.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 