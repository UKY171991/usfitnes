<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkUserAccess();

$branch_id = $_SESSION['branch_id'];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if(!empty($name) && !empty($age) && !empty($gender)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO patients (name, age, gender, phone, email, address, branch_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $age, $gender, $phone, $email, $address, $branch_id]);
            
            // Log activity
            $activity = "New patient added: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: view-patients.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding patient: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Patient</h1>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="age" class="form-label">Age *</label>
                    <input type="number" class="form-control" id="age" name="age" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
            </div>
            <div class="text-end">
                <a href="view-patients.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Patient</button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 