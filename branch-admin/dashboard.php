<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkBranchAdminAccess();

$branch_id = $_SESSION['branch_id'];

// Get statistics for the branch
$stats = [
    'patients' => $conn->prepare("SELECT COUNT(*) FROM patients WHERE branch_id = ?")->execute([$branch_id])->fetchColumn(),
    'tests' => $conn->prepare("SELECT COUNT(*) FROM tests")->fetchColumn(),
    'reports' => $conn->prepare("SELECT COUNT(*) FROM reports WHERE branch_id = ?")->execute([$branch_id])->fetchColumn(),
    'revenue' => $conn->prepare("SELECT SUM(amount) FROM payments WHERE branch_id = ?")->execute([$branch_id])->fetchColumn() ?? 0,
    'pending_reports' => $conn->prepare("SELECT COUNT(*) FROM reports WHERE branch_id = ? AND status = 'pending'")->execute([$branch_id])->fetchColumn(),
    'completed_reports' => $conn->prepare("SELECT COUNT(*) FROM reports WHERE branch_id = ? AND status = 'completed'")->execute([$branch_id])->fetchColumn()
];

// Get recent activities for the branch
$activities = $conn->prepare("
    SELECT a.*, u.name as user_name 
    FROM activities a 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE u.branch_id = ? 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$activities->execute([$branch_id]);
$activities = $activities->fetchAll(PDO::FETCH_ASSOC);

// Get recent reports
$recent_reports = $conn->prepare("
    SELECT r.*, p.name as patient_name 
    FROM reports r 
    LEFT JOIN patients p ON r.patient_id = p.id 
    WHERE r.branch_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$recent_reports->execute([$branch_id]);
$recent_reports = $recent_reports->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Branch Dashboard</h1>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Patients</h5>
                <h2 class="display-4"><?php echo $stats['patients']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Tests</h5>
                <h2 class="display-4"><?php echo $stats['tests']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Reports</h5>
                <h2 class="display-4"><?php echo $stats['reports']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Pending Reports</h5>
                <h2 class="display-4"><?php echo $stats['pending_reports']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Completed Reports</h5>
                <h2 class="display-4"><?php echo $stats['completed_reports']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Revenue</h5>
                <h2 class="display-4">₹<?php echo number_format($stats['revenue'], 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Reports</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_reports as $report): ?>
                                <tr>
                                    <td><?php echo $report['id']; ?></td>
                                    <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../reports/report-template.php?id=<?php echo $report['id']; ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>User</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 