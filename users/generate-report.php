<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkUserAccess();

$branch_id = $_SESSION['branch_id'];
$patient_id = $_GET['patient_id'] ?? '';

if(empty($patient_id)) {
    header("Location: view-patients.php");
    exit();
}

// Get patient details
$patient = $conn->prepare("SELECT * FROM patients WHERE id = ? AND branch_id = ?");
$patient->execute([$patient_id, $branch_id]);
$patient = $patient->fetch(PDO::FETCH_ASSOC);

if(!$patient) {
    header("Location: view-patients.php");
    exit();
}

// Get all tests
$tests = $conn->prepare("
    SELECT t.*, c.name as category_name 
    FROM tests t 
    LEFT JOIN test_categories c ON t.category_id = c.id 
    ORDER BY c.name, t.name
");
$tests->execute();
$tests = $tests->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $test_id = $_POST['test_id'] ?? '';
    $doctor_name = $_POST['doctor_name'] ?? '';
    $referral = $_POST['referral'] ?? '';
    
    if(!empty($test_id)) {
        try {
            // Get test price
            $stmt = $conn->prepare("SELECT price FROM tests WHERE id = ?");
            $stmt->execute([$test_id]);
            $test_price = $stmt->fetchColumn();
            
            // Create report
            $stmt = $conn->prepare("
                INSERT INTO reports (patient_id, test_id, doctor_name, referral, price, branch_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$patient_id, $test_id, $doctor_name, $referral, $test_price, $branch_id]);
            $report_id = $conn->lastInsertId();
            
            // Log activity
            $activity = "New report generated for patient: {$patient['name']}";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: print-report.php?id=$report_id");
            exit();
        } catch(PDOException $e) {
            $error = "Error generating report: " . $e->getMessage();
        }
    } else {
        $error = "Please select a test";
    }
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Generate Report</h1>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Patient Details</h5>
                <table class="table table-sm">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?php echo htmlspecialchars($patient['age']); ?></td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?php echo ucfirst(htmlspecialchars($patient['gender'])); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="test_id" class="form-label">Select Test *</label>
                    <select class="form-control" id="test_id" name="test_id" required>
                        <option value="">Select Test</option>
                        <?php 
                        $current_category = '';
                        foreach($tests as $test): 
                            if($current_category != $test['category_name']) {
                                if($current_category != '') echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($test['category_name']) . '">';
                                $current_category = $test['category_name'];
                            }
                        ?>
                            <option value="<?php echo $test['id']; ?>" data-price="<?php echo $test['price']; ?>">
                                <?php echo htmlspecialchars($test['name']); ?> - ₹<?php echo number_format($test['price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="doctor_name" class="form-label">Doctor's Name</label>
                    <input type="text" class="form-control" id="doctor_name" name="doctor_name">
                </div>
            </div>
            <div class="mb-3">
                <label for="referral" class="form-label">Referral</label>
                <textarea class="form-control" id="referral" name="referral" rows="2"></textarea>
            </div>
            <div class="text-end">
                <a href="view-patients.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 