<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Get date range for filtering
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get branch filter
$branch_id = $_GET['branch_id'] ?? '';

// Build query
$query = "
    SELECT r.*, p.name as patient_name, b.name as branch_name, u.name as created_by
    FROM reports r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN branches b ON r.branch_id = b.id
    LEFT JOIN users u ON r.created_by = u.id
    WHERE r.created_at BETWEEN ? AND ?
";
$params = [$start_date, $end_date];

if(!empty($branch_id)) {
    $query .= " AND r.branch_id = ?";
    $params[] = $branch_id;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for filter
$branches = $conn->query("SELECT id, name FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_reports = count($reports);
$total_amount = array_sum(array_column($reports, 'total_amount'));
$pending_reports = count(array_filter($reports, function($report) {
    return $report['status'] == 'pending';
}));
$completed_reports = count(array_filter($reports, function($report) {
    return $report['status'] == 'completed';
}));

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="branch_id" class="form-label">Branch</label>
                <select class="form-control" id="branch_id" name="branch_id">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>" <?php echo $branch_id == $branch['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Reports</h5>
                <h2 class="display-4"><?php echo $total_reports; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Amount</h5>
                <h2 class="display-4">₹<?php echo number_format($total_amount, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Reports</h5>
                <h2 class="display-4"><?php echo $pending_reports; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Completed Reports</h5>
                <h2 class="display-4"><?php echo $completed_reports; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $report): ?>
                <tr>
                    <td><?php echo $report['id']; ?></td>
                    <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($report['branch_name']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                    <td>₹<?php echo number_format($report['total_amount'], 2); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $report['status'] == 'completed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($report['created_by']); ?></td>
                    <td>
                        <a href="../reports/report-template.php?id=<?php echo $report['id']; ?>" 
                           class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="../reports/report-template.php?id=<?php echo $report['id']; ?>&download=1" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../inc/footer.php'; ?> 