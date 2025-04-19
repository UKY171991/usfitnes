<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkUserAccess();

$branch_id = $_SESSION['branch_id'];

// Get pending reports for this branch
$reports = $conn->prepare("
    SELECT r.*, p.name as patient_name, t.name as test_name
    FROM reports r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN tests t ON r.test_id = t.id
    WHERE r.branch_id = ? AND r.status = 'pending'
    ORDER BY r.created_at ASC
");
$reports->execute([$branch_id]);
$reports = $reports->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = $_POST['report_id'] ?? '';
    $result = $_POST['result'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    
    if(!empty($report_id) && !empty($result)) {
        try {
            // Update report
            $stmt = $conn->prepare("
                UPDATE reports 
                SET result = ?, remarks = ?, status = 'completed', completed_at = NOW() 
                WHERE id = ? AND branch_id = ?
            ");
            $stmt->execute([$result, $remarks, $report_id, $branch_id]);
            
            // Log activity
            $activity = "Test result uploaded for Report #$report_id";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: upload-result.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error uploading result: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Upload Test Results</h1>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Result uploaded successfully!</div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Patient</th>
                <th>Test</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $report): ?>
                <tr>
                    <td><?php echo $report['id']; ?></td>
                    <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($report['test_name']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($report['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#uploadResultModal<?php echo $report['id']; ?>">
                            <i class="fas fa-upload"></i> Upload Result
                        </button>
                    </td>
                </tr>
                
                <!-- Upload Result Modal -->
                <div class="modal fade" id="uploadResultModal<?php echo $report['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Upload Result - Report #<?php echo $report['id']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <div class="modal-body">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <div class="mb-3">
                                        <label for="result" class="form-label">Result *</label>
                                        <textarea class="form-control" id="result" name="result" rows="5" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="remarks" class="form-label">Remarks</label>
                                        <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Upload Result</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../inc/footer.php'; ?> 