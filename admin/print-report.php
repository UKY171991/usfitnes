<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

// Allow access for admin roles
// checkAdminAccess(); // Uncomment if you have this function and want strict access control

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die('Invalid Report ID.');
}
$report_id = (int)$_GET['id'];

try {
    // Fetch report details along with patient, test, and branch info
    $stmt = $conn->prepare("
        SELECT 
            r.*, 
            p.name as patient_name, 
            p.age as patient_age, 
            p.gender as patient_gender,
            t.test_name,
            t.normal_range,
            t.sample_type,
            b.branch_name,
            b.address as branch_address,
            b.phone as branch_phone
        FROM reports r
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN tests t ON r.test_id = t.id
        LEFT JOIN branches b ON p.branch_id = b.id
        WHERE r.id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        die('Report not found.');
    }
    
    // Optionally fetch payment details if needed for the report printout
    // $payment_stmt = $conn->prepare("SELECT * FROM payments WHERE report_id = ?");
    // $payment_stmt->execute([$report_id]);
    // $payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Print Report Error: " . $e->getMessage());
    die('An error occurred while fetching report details.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?php echo htmlspecialchars($report['id']); ?></title>
    <!-- Minimal styling, Bootstrap grid might be overkill -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
        }
        .print-container {
            max-width: 800px; /* A4-ish width */
            margin: 15px auto;
            padding: 20px;
            border: 1px solid #eee;
        }
        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #666;
        }
        .report-header h1 {
            margin: 0 0 5px 0;
            font-size: 22px;
            font-weight: 600;
        }
        .report-header p {
            margin: 0;
            font-size: 11px;
            color: #555;
        }
        .section {
             margin-bottom: 20px;
        }
        .section-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 150px 1fr; /* Label column and Value column */
            gap: 5px 15px; /* Row gap and Column gap */
            font-size: 13px;
        }
         .info-grid dt {
             font-weight: 500;
             color: #444;
             grid-column: 1;
         }
         .info-grid dd {
             margin: 0;
             grid-column: 2;
         }
        .result-section {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            min-height: 80px;
            background-color: #fdfdfd;
        }
        .result-section p {
             white-space: pre-wrap; 
             margin: 0;
             font-size: 13px;
        }
        .report-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
                font-size: 9.5pt;
                line-height: 1.3;
            }
            .print-container {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 10mm 8mm; /* Margins for printing */
                border: none;
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
             /* Ensure sections don't break across pages awkwardly if possible */
            .section, .result-section {
                page-break-inside: avoid;
            }
             .report-header, .report-footer {
                page-break-before: auto; 
                page-break-after: auto;
            }
            h1 { font-size: 18pt; }
            .section-title { font-size: 12pt; }
            .info-grid { font-size: 9pt; grid-template-columns: 120px 1fr; }
            .result-section p { font-size: 9pt; }
            .report-footer { font-size: 8pt; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        
        <div class="report-header">
            <h1>Laboratory Test Report</h1>
            <p>
                <strong><?php echo htmlspecialchars($report['branch_name'] ?? 'Main Lab'); ?></strong><br>
                <?php echo htmlspecialchars($report['branch_address'] ?? '-'); ?> | 
                Phone: <?php echo htmlspecialchars($report['branch_phone'] ?? '-'); ?>
            </p>
        </div>

        <div class="section">
            <div class="section-title">Patient & Report Information</div>
            <div class="info-grid">
                <dt>Patient Name:</dt>
                <dd><?php echo htmlspecialchars($report['patient_name']); ?></dd>
                
                <dt>Age/Gender:</dt>
                <dd>
                    <?php echo htmlspecialchars($report['patient_age'] ?? '-'); ?> /
                    <?php echo ucfirst(htmlspecialchars($report['patient_gender'] ?? '-')); ?>
                </dd>

                <dt>Report ID:</dt>
                <dd><?php echo htmlspecialchars($report['id']); ?></dd>
                
                <dt>Report Date:</dt>
                <dd><?php echo date('Y-m-d H:i', strtotime($report['created_at'])); ?></dd>
                
                <dt>Status:</dt>
                <dd><?php echo ucfirst(htmlspecialchars($report['status'])); ?></dd>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Test Details</div>
            <table class="table table-sm table-bordered detail-table">
                <tbody>
                    <tr>
                        <th style="width: 150px;">Test Name:</th>
                        <td><?php echo htmlspecialchars($report['test_name'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th style="width: 150px;">Sample Type:</th>
                        <td><?php echo htmlspecialchars($report['sample_type'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th style="width: 150px;">Normal Range:</th>
                        <td><?php echo htmlspecialchars($report['normal_range'] ?? '-'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Test Result</div>
            <table class="table table-sm table-bordered result-table">
                 <thead>
                    <tr>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="white-space: pre-wrap; min-height: 60px; vertical-align: top;">
                            <?php echo nl2br(htmlspecialchars($report['result'] ?? 'Result not available.')); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Optional: Add payment details if fetched -->
        <?php /* if (isset($payment) && $payment): ?>
        <div class="section-title">Payment Summary</div>
        <dl class="row">
             <dt class="col-sm-3">Total Amount:</dt>
             <dd class="col-sm-9">₹<?php echo number_format($payment['total_amount'] ?? 0, 2); ?></dd>
             <dt class="col-sm-3">Paid Amount:</dt>
             <dd class="col-sm-9">₹<?php echo number_format($payment['paid_amount'] ?? 0, 2); ?></dd>
             <dt class="col-sm-3">Due Amount:</dt>
             <dd class="col-sm-9">₹<?php echo number_format($payment['due_amount'] ?? 0, 2); ?></dd>
        </dl>
        <?php endif; */ ?>

        <div class="report-footer">
            Generated on: <?php echo date('Y-m-d H:i:s'); ?>
            <p style="margin-top: 5px;">--- End of Report ---</p>
             <button class="btn btn-sm btn-primary no-print mt-2" onclick="window.print()">Print Again</button>
        </div>

    </div>

    <script>
        // Automatically trigger print dialog on load
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> 