<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkUserAccess();

$branch_id = $_SESSION['branch_id'];

// Get all patients for this branch
$patients = $conn->prepare("
    SELECT * FROM patients 
    WHERE branch_id = ? 
    ORDER BY name
");
$patients->execute([$branch_id]);
$patients = $patients->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">View Patients</h1>
    <a href="add-patient.php" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add New Patient
    </a>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Patient added successfully!</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($patients as $patient): ?>
                <tr>
                    <td><?php echo $patient['id']; ?></td>
                    <td><?php echo htmlspecialchars($patient['name']); ?></td>
                    <td><?php echo htmlspecialchars($patient['age']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($patient['gender'])); ?></td>
                    <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    <td><?php echo htmlspecialchars($patient['email']); ?></td>
                    <td><?php echo htmlspecialchars($patient['address']); ?></td>
                    <td>
                        <a href="generate-report.php?patient_id=<?php echo $patient['id']; ?>" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-file-medical"></i> Generate Report
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../inc/footer.php'; ?> 