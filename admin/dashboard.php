<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Get statistics with error handling
try {
    $stats = [
        'branches' => $conn->query("SELECT COUNT(*) FROM branches")->fetchColumn() ?? 0,
        'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn() ?? 0,
        'patients' => $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn() ?? 0,
        'tests' => $conn->query("SELECT COUNT(*) FROM tests")->fetchColumn() ?? 0,
        'reports' => $conn->query("SELECT COUNT(*) FROM reports")->fetchColumn() ?? 0,
        'revenue' => $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments")->fetchColumn() ?? 0
    ];

    // Get recent activities with error handling
    $stmt = $conn->prepare("
        SELECT a.*, u.name as user_name 
        FROM activities a 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Log the error
    error_log("Dashboard Error: " . $e->getMessage());
    // Set default values if there's an error
    $stats = [
        'branches' => 0,
        'users' => 0,
        'patients' => 0,
        'tests' => 0,
        'reports' => 0,
        'revenue' => 0
    ];
    $activities = [];
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Branches</h5>
                <h2 class="display-4"><?php echo number_format($stats['branches']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Users</h5>
                <h2 class="display-4"><?php echo number_format($stats['users']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Patients</h5>
                <h2 class="display-4"><?php echo number_format($stats['patients']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Tests</h5>
                <h2 class="display-4"><?php echo number_format($stats['tests']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Reports</h5>
                <h2 class="display-4"><?php echo number_format($stats['reports']); ?></h2>
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

<!-- Recent Activities -->
<div class="row mt-4">
    <div class="col-12">
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
                            <?php if(empty($activities)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No recent activities</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($activities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['user_name'] ?? 'Unknown User'); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 