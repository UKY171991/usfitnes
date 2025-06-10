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
// include '../inc/header.php'; // This line has been removed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?php echo htmlspecialchars($report['id']); ?></title>
    <link rel="stylesheet" href="admin-shared.css"> <!-- Moved into head -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.4;
        }
        .print-container {
            width: 100%;
            max-width: 800px; /* Optional: constrain width for screen view */
            margin: 0 auto; /* Center on screen */
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-header h1 { /* Existing H1 for "Laboratory Test Report" */
            font-size: 18pt;
            margin-bottom: 5px;
        }
        .report-header h2 { /* New H2 for Branch Name */
            font-size: 16pt; /* Slightly smaller than main title, or adjust as needed */
            margin-bottom: 5px; 
        }
        .report-header p {
            font-size: 10pt;
            margin-bottom: 3px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            border-bottom: 2px solid #333;
            padding-bottom: 4px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 160px auto; /* Adjusted for potentially longer labels */
            gap: 6px 12px;
            font-size: 10pt;
        }
        .info-grid dt {
            font-weight: bold;
            color: #444;
        }
        .info-grid dd {
            margin-left: 0; /* Reset default dd margin */
        }
        table.detail-table, table.result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        table.detail-table th, table.detail-table td,
        table.result-table th, table.result-table td {
            border: 1px solid #bbb;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        table.detail-table th {
            background-color: #f2f2f2; /* Light background for table headers */
            width: 180px; /* Fixed width for label column */
        }
        table.result-table th {
            background-color: #f2f2f2;
        }
        table.result-table td {
            min-height: 60px; /* Ensure space for results */
            white-space: pre-wrap; /* Preserve line breaks and spaces in results */
        }
        .report-footer {
            text-align: center;
            font-size: 9pt;
            color: #555;
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .report-footer p {
            margin: 5px 0;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 10mm; /* Standard print margin */
                font-size: 10pt; /* Base font size for print */
                color: #000; /* Ensure text is black for print */
                -webkit-print-color-adjust: exact; /* Chrome/Safari: force printing of colors/backgrounds */
                print-color-adjust: exact; /* Standard: force printing of colors/backgrounds */
            }
            .print-container {
                width: 100%;
                max-width: none;
                margin: 0;
            }
            .report-header h1 {
                font-size: 20pt; /* Larger for print header */
            }
            .report-header h2 {
                font-size: 18pt; /* Adjust as needed for print */
            }
            .report-header p {
                font-size: 11pt;
            }
            .section-title {
                font-size: 14pt;
            }
            .info-grid {
                font-size: 10pt;
            }
            table.detail-table, table.result-table {
                font-size: 9pt; /* Slightly smaller table text for print if needed */
            }
            table.detail-table th, table.detail-table td,
            table.result-table th, table.result-table td {
                padding: 5px; /* Adjust padding for print */
            }
            /* Attempt to prevent elements from breaking across pages */
            .report-header, .section, table, .info-grid, .report-footer {
                page-break-inside: avoid;
            }
            tr, dt, dd {
                 page-break-inside: avoid;
            }
            h1, h2, h3, h4, h5, h6, .section-title {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        
        <div class="report-header">
            <h2 style="font-size: 16pt; margin-bottom: 5px;"><?php echo htmlspecialchars($report['branch_name'] ?? 'Main Lab'); ?></h2>
            <h1>Laboratory Test Report</h1>
            <p>
                <?php /* <strong><?php echo htmlspecialchars($report['branch_name'] ?? 'Main Lab'); ?></strong><br> */ ?>
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