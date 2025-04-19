<?php
require_once '../inc/config.php';
require_once '../inc/db.php';

session_start();

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(!empty($username) && !empty($password)) {
        try {
            // Get user from database
            $stmt = $conn->prepare("
                SELECT u.*, b.id as branch_id, b.name as branch_name 
                FROM users u 
                LEFT JOIN branches b ON u.branch_id = b.id 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                if($user['branch_id']) {
                    $_SESSION['branch_id'] = $user['branch_id'];
                    $_SESSION['branch_name'] = $user['branch_name'];
                }
                
                // Log activity
                $activity = "User logged in: {$user['username']}";
                $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
                $stmt->execute([$user['id'], $activity]);
                
                // Redirect based on role
                switch($user['role']) {
                    case 'admin':
                        header("Location: ../admin/dashboard.php");
                        break;
                    case 'branch_admin':
                        header("Location: ../branch-admin/dashboard.php");
                        break;
                    case 'technician':
                    case 'receptionist':
                        header("Location: ../users/view-patients.php");
                        break;
                    default:
                        header("Location: ../index.php");
                }
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pathology CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 30px 20px;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            padding: 12px;
            border-radius: 5px;
        }
        .btn-login {
            padding: 12px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Pathology CRM</h3>
                    <p class="text-muted">Login to your account</p>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 