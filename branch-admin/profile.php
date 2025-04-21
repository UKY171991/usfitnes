<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'];
$admin_id = $_SESSION['user_id'];

// Fetch branch admin details
$stmt = $conn->prepare("
    SELECT 
        u.*,
        b.branch_code,
        b.branch_name,
        b.address,
        b.city,
        b.state,
        b.pincode,
        b.phone,
        b.email,
        b.status,
        b.created_at as branch_created_at
    FROM users u
    JOIN branches b ON u.branch_id = b.id
    WHERE u.id = ? AND u.branch_id = ? AND u.role = 'branch_admin'
");
$stmt->execute([$admin_id, $branch_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists for other users
    if ($email !== $admin['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already in use";
        }
    }
    
    // Handle password change if requested
    if (!empty($current_password)) {
        if (!password_verify($current_password, $admin['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }
    }
    
    // Update profile if no errors
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Update basic info
            $stmt = $conn->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $admin_id]);
            
            // Update password if changed
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $admin_id]);
            }
            
            $conn->commit();
            $_SESSION['success'] = "Profile updated successfully";
            header("Location: profile.php");
            exit;
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "An error occurred. Please try again.";
        }
    }
}

include '../inc/branch-header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Profile</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Profile</li>
    </ol>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Personal Information
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($admin['phone']); ?>">
                        </div>
                        <hr>
                        <h5>Change Password</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hospital me-1"></i>
                    Branch Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Branch Code</label>
                        <p class="form-control-static"><?php echo htmlspecialchars($admin['branch_code'] ?: 'Not Set'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <p class="form-control-static"><?php echo htmlspecialchars($admin['branch_name']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <p class="form-control-static">
                            <?php echo htmlspecialchars($admin['address']); ?>
                            <?php if ($admin['city'] || $admin['state'] || $admin['pincode']): ?>
                                <br>
                                <?php 
                                    echo htmlspecialchars(implode(', ', array_filter([
                                        $admin['city'],
                                        $admin['state'],
                                        $admin['pincode']
                                    ])));
                                ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <p class="form-control-static"><?php echo htmlspecialchars($admin['phone']); ?></p>
                    </div>
                    <?php if ($admin['email']): ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <p class="form-control-static"><?php echo htmlspecialchars($admin['email']); ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <p class="form-control-static">
                            <span class="badge bg-<?php echo $admin['status'] == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $admin['status'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </p>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        To update branch information, please contact the main administrator.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?> 