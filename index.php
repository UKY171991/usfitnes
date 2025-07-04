<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <style>
    :root {
      --primary-color: #2c5aa0;
      --primary-dark: #1e3c72;
    }
    
    body {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      min-height: 100vh;
      font-family: 'Source Sans Pro', sans-serif;
    }
    
    .login-page {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    
    .login-box {
      width: 400px;
      margin: 0;
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-logo img {
      max-width: 80px;
      height: auto;
      margin-bottom: 1rem;
    }
    
    .login-logo h1 {
      color: white;
      font-weight: 300;
      font-size: 2.5rem;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .login-logo p {
      color: rgba(255,255,255,0.8);
      margin: 0.5rem 0 0 0;
      font-size: 1.1rem;
    }
    
    .card {
      border-radius: 15px;
      border: none;
      box-shadow: 0 15px 35px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.95);
    }
    
    .card-body {
      padding: 2rem;
    }
    
    .login-card-msg {
      text-align: center;
      color: #666;
      margin-bottom: 2rem;
      font-size: 1.1rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-control {
      border-radius: 10px;
      padding: 0.75rem 1rem;
      border: 1px solid #ddd;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
    }
    
    .input-group-text {
      border-radius: 10px 0 0 10px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
    }
    
    .input-group .form-control {
      border-radius: 0 10px 10px 0;
      border-left: none;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      border: none;
      border-radius: 10px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(44, 90, 160, 0.4);
    }
    
    .icheck-primary {
      margin-top: 1rem;
    }
    
    .forgot-password {
      text-align: center;
      margin-top: 1.5rem;
    }
    
    .forgot-password a {
      color: var(--primary-color);
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .forgot-password a:hover {
      color: var(--primary-dark);
    }
    
    .alert {
      border-radius: 10px;
      margin-bottom: 1rem;
    }
    
    @media (max-width: 576px) {
      .login-box {
        width: 90%;
        margin: 0 auto;
      }
      
      .card-body {
        padding: 1.5rem;
      }
      
      .login-logo h1 {
        font-size: 2rem;
      }
    }
</head>
<body class="login-page">
<div class="login-box">
  <div class="login-logo">
    <?php 
    // Include init.php for utility functions
    require_once 'includes/init.php';
    
    if (hasLogo()): ?>
        <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" style="max-width: 80px; height: auto; margin-bottom: 1rem;">
        <h1 style="font-size: 2.5rem;">PathLab Pro</h1>
    <?php else: ?>
        <h1 style="font-size: 3rem; margin-top: 0;">PathLab Pro</h1>
    <?php endif; ?>
    <p>Laboratory Management System</p>
  </div>
  
  <div class="card">
    <div class="card-body">
      <p class="login-card-msg">Sign in to access your laboratory dashboard</p>

      <!-- Alert Messages -->
      <div id="alertContainer"></div>
      
      <!-- Demo Login Info -->
      <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h6><i class="icon fas fa-info"></i> Demo Login Credentials:</h6>
        <strong>Username:</strong> admin<br>
        <strong>Password:</strong> password
      </div>

      <form id="loginForm">
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="fas fa-user"></i>
              </span>
            </div>
            <input type="text" class="form-control" placeholder="Username" id="username" name="username" required>
          </div>
        </div>
        
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="fas fa-lock"></i>
              </span>
            </div>
            <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>
          </div>
        </div>
        
        <div class="row align-items-center">
          <div class="col-7">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
              <span class="btn-text">Sign In</span>
              <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Signing In...
              </span>
            </button>
          </div>
          <!-- /.col -->
        </div>
      </form>

          <div class="col-5">
            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
              <span class="btn-text">Sign In</span>
              <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Signing in...
              </span>
            </button>
          </div>
        </div>
      </form>

      <div class="forgot-password">
        <a href="forgot-password.php">I forgot my password</a>
      </div>
      
      <div class="text-center mt-3">
        <p class="mb-0">
          <a href="register.php" class="text-muted">Register a new membership</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Handle login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        loginUser();
    });
    
    // Focus on username field
    $('#username').focus();
    
    // Pre-fill demo credentials if requested
    if (window.location.search.includes('demo=1')) {
        $('#username').val('admin');
        $('#password').val('password');
    }
});

function loginUser() {
    const username = $('#username').val().trim();
    const password = $('#password').val();
    
    if (!username || !password) {
        showAlert('error', 'Please enter both username and password');
        return;
    }
    
    // Show loading state
    setLoadingState(true);
    
    $.ajax({
        url: 'api/auth_api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'login',
            username: username,
            password: password
        }),
        dataType: 'json',
        success: function(response) {
            setLoadingState(false);
            
            if (response.success) {
                showAlert('success', 'Login successful! Redirecting...');
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                showAlert('error', response.message || 'Login failed');
            }
        },        error: function(xhr, status, error) {
            setLoadingState(false);
            console.error('AJAX Error:', error);
            console.log('Error details:', xhr);
            
            let message = 'Login failed. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response && response.message) {
                    message = response.message;
                }
            } catch (e) {
                if (xhr.status === 0) {
                    message = 'Network error. Please check your connection.';
                } else if (xhr.status === 500) {
                    message = 'Server error. Please try again later.';
                }
            }
            
            showAlert('error', message);
        }
    });
}

function setLoadingState(loading) {
    if (loading) {
        $('#loginBtn').prop('disabled', true);
        $('#loginBtn .btn-text').hide();
        $('#loginBtn .btn-loading').show();
    } else {
        $('#loginBtn').prop('disabled', false);
        $('#loginBtn .btn-text').show();
        $('#loginBtn .btn-loading').hide();
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fas fa-check' : 
                type === 'error' ? 'fas fa-ban' : 
                type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon ${icon}"></i> ${message}
        </div>
    `;
    
    $('#alertContainer').html(alert);
    
    // Auto-hide after 5 seconds (except for success messages)
    if (type !== 'success') {
        setTimeout(() => {
            $('#alertContainer .alert').fadeOut();
        }, 5000);
    }
}

// Clear alerts when user starts typing
$('#username, #password').on('input', function() {
    $('#alertContainer .alert').fadeOut();
});
</script>
</body>
</html>
