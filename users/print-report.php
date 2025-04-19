<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkUserAccess();

$branch_id = $_SESSION['branch_id'];
$report_id = $_GET['id'] ?? '';

if(empty($report_id)) {
    header("Location: view-patients.php");
    exit();
}

// Get report details with patient and test information
$report = $conn->prepare("
    SELECT r.*, p.name as patient_name, p.age, p.gender, p.phone,
           t.name as test_name, t.normal_range, t.reporting_time,
           b.name as branch_name, b.address as branch_address, b.phone as branch_phone
    FROM reports r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN tests t ON r.test_id = t.id
    LEFT JOIN branches b ON r.branch_id = b.id
    WHERE r.id = ? AND r.branch_id = ?
");
$report->execute([$report_id, $branch_id]);
$report = $report->fetch(PDO::FETCH_ASSOC);

if(!$report) {
    header("Location: view-patients.php");
    exit();
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Print Report</h1>
    <div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
        <a href="view-patients.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2>Pathology Report</h2>
            <h4><?php echo htmlspecialchars($report['branch_name']); ?></h4>
            <p><?php echo htmlspecialchars($report['branch_address']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($report['branch_phone']); ?></p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Patient Details</h5>
                <table class="table table-sm">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?php echo htmlspecialchars($report['age']); ?></td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?php echo ucfirst(htmlspecialchars($report['gender'])); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($report['phone']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Report Details</h5>
                <table class="table table-sm">
                    <tr>
                        <th>Report ID:</th>
                        <td><?php echo $report['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Test:</th>
                        <td><?php echo htmlspecialchars($report['test_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if($report['status'] == 'completed'): ?>
            <div class="mb-4">
                <h5>Test Result</h5>
                <div class="p-3 bg-light">
                    <?php echo nl2br(htmlspecialchars($report['result'])); ?>
                </div>
            </div>
            
            <div class="mb-4">
                <h5>Normal Range</h5>
                <div class="p-3 bg-light">
                    <?php echo nl2br(htmlspecialchars($report['normal_range'])); ?>
                </div>
            </div>
            
            <?php if(!empty($report['remarks'])): ?>
                <div class="mb-4">
                    <h5>Remarks</h5>
                    <div class="p-3 bg-light">
                        <?php echo nl2br(htmlspecialchars($report['remarks'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Test result is pending. Please check back later.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <p>This is a computer-generated report. No signature is required.</p>
            <p>Report generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</div>

<style>
@media print {
    .d-flex {
        display: none !important;
    }
    .card {
        border: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>

<?php include '../inc/footer.php'; ?> 