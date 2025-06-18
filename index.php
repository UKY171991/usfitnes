<?php
session_start();

// Include database configuration
require_once 'config.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if(!empty($username) && !empty($password)) {
        try {
            // Check user in database
            $stmt = $pdo->prepare("SELECT id, username, password, full_name, user_type FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } catch(PDOException $e) {
            // Fallback to default admin for first login
            if($username === 'admin' && $password === 'password') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = 'System Administrator';
                $_SESSION['user_type'] = 'admin';
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Database connection error. Please try again.";
            }
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  <title>PathLab Pro | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  
  <style>
    body {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%);
      min-height: 100vh;
    }
    .login-box {
      margin: 7% auto;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .card-header {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%);
      border-radius: 15px 15px 0 0;
    }
    .login-logo {
      color: white;
      font-weight: bold;
    }
    .btn-primary {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%);
      border: none;
      border-radius: 25px;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #1e3c72 0%, #2c5aa0 100%);
    }
    .form-control {
      border-radius: 25px;
      border: 2px solid #e9ecef;
    }
    .form-control:focus {
      border-color: #2c5aa0;
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">  <div class="login-logo">
    <a href="#" class="login-logo"><b>PathLab</b>Pro</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-header text-center">
      <h1 class="h4 text-white mb-0">Laboratory Login</h1>
    </div>
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to access laboratory system</p>

      <?php if(isset($error)): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
      </div>
      <?php endif; ?>

      <form action="index.php" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Username" name="username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <div class="social-auth-links text-center mb-3">
        <p>- OR -</p>
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot-password.php">I forgot my password</a>
      </p>
      <p class="mb-0">
        <a href="register.php" class="text-center">Register a new membership</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
// Demo credentials info
$(document).ready(function() {
    // Add demo info
    setTimeout(function() {
        if(!$('.demo-info').length) {
            $('.login-card-body').prepend(`                <div class="alert alert-info alert-dismissible demo-info">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-info"></i> Demo Login: Username: <strong>admin</strong>, Password: <strong>password</strong>
                </div>
            `);
        }
    }, 1000);
});
</script>
</body>
</html>
