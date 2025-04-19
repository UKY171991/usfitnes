<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

// Check if user is logged in
checkUserAccess();

// Get report ID from request
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($report_id <= 0) {
    die('Invalid report ID');
}

try {
    // Get report details
    $stmt = $conn->prepare("
        SELECT r.*, p.name as patient_name, p.age, p.gender, p.phone, p.email,
               t.name as test_name, t.normal_range, t.unit,
               b.name as branch_name, b.address as branch_address, b.phone as branch_phone,
               u.name as doctor_name
        FROM reports r
        JOIN patients p ON r.patient_id = p.id
        JOIN tests t ON r.test_id = t.id
        JOIN branches b ON r.branch_id = b.id
        LEFT JOIN users u ON r.doctor_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$report) {
        die('Report not found');
    }

    // Get test parameters and results
    $stmt = $conn->prepare("
        SELECT p.name, p.normal_range, p.unit, r.result, r.remarks
        FROM report_parameters r
        JOIN test_parameters p ON r.parameter_id = p.id
        WHERE r.report_id = ?
        ORDER BY p.display_order ASC
    ");
    $stmt->execute([$report_id]);
    $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report - <?php echo $report['test_name']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .report-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .header p {
            color: #7f8c8d;
            margin: 5px 0;
        }
        .patient-info, .test-info {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background-color: #f8f9fa;
            width: 30%;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .results-table th, .results-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .results-table th {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .signature {
            margin-top: 40px;
            text-align: right;
        }
        .signature p {
            margin: 5px 0;
        }
        @media print {
            body {
                padding: 0;
            }
            .report-container {
                border: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="header">
            <h1><?php echo SITE_NAME; ?></h1>
            <p><?php echo $report['branch_name']; ?></p>
            <p><?php echo $report['branch_address']; ?></p>
            <p>Phone: <?php echo $report['branch_phone']; ?></p>
        </div>

        <div class="patient-info">
            <h2>Patient Information</h2>
            <table class="info-table">
                <tr>
                    <th>Patient Name</th>
                    <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                </tr>
                <tr>
                    <th>Age/Gender</th>
                    <td><?php echo htmlspecialchars($report['age'] . '/' . $report['gender']); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo htmlspecialchars($report['phone']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($report['email']); ?></td>
                </tr>
            </table>
        </div>

        <div class="test-info">
            <h2>Test Information</h2>
            <table class="info-table">
                <tr>
                    <th>Test Name</th>
                    <td><?php echo htmlspecialchars($report['test_name']); ?></td>
                </tr>
                <tr>
                    <th>Sample Collection Date</th>
                    <td><?php echo date('d/m/Y', strtotime($report['collection_date'])); ?></td>
                </tr>
                <tr>
                    <th>Report Date</th>
                    <td><?php echo date('d/m/Y', strtotime($report['report_date'])); ?></td>
                </tr>
                <tr>
                    <th>Referring Doctor</th>
                    <td><?php echo htmlspecialchars($report['doctor_name'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>

        <div class="results">
            <h2>Test Results</h2>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Result</th>
                        <th>Normal Range</th>
                        <th>Unit</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($parameters as $param): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($param['name']); ?></td>
                        <td><?php echo htmlspecialchars($param['result']); ?></td>
                        <td><?php echo htmlspecialchars($param['normal_range']); ?></td>
                        <td><?php echo htmlspecialchars($param['unit']); ?></td>
                        <td><?php echo htmlspecialchars($param['remarks']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="signature">
            <p>Lab Technician: ___________________</p>
            <p>Signature: ___________________</p>
            <p>Date: <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="footer">
            <p>This is a computer-generated report. No signature is required.</p>
            <p>Report ID: <?php echo $report_id; ?></p>
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()">Print Report</button>
            <button onclick="window.close()">Close</button>
        </div>
    </div>
</body>
</html> 