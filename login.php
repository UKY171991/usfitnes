<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration and utilities
require_once 'config.php';
require_once 'includes/init.php';

// Check if user is logged in and redirect to dashboard
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
  <title>Login - PathLab Pro | Laboratory Management System</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/custom.css">
  
  <style>
    :root {
      --primary-color: #2c5aa0;
      --primary-dark: #1e3c72;
      --primary-light: #4b6cb7;
    }
    
    body {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      min-height: 100vh;
      font-family: 'Source Sans Pro', sans-serif;
    }
    
    .login-box {
      margin: 5% auto;
      width: 400px;
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-logo img {
      max-width: 80px;
      height: auto;
      margin-bottom: 1rem;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }
    
    .login-logo h1 {
      color: white;
      font-size: 2.5rem;
      font-weight: 300;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .login-logo p {
      color: rgba(255,255,255,0.8);
      font-size: 1.1rem;
      margin: 0.5rem 0 0 0;
    }
    
    .card {
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      border: none;
    }
    
    .card-body {
      padding: 2rem;
    }
    
    .login-card-msg {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #666;
      font-size: 1.1rem;
    }
    
    .form-control {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      padding: 0.75rem 1rem;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
    }
    
    .input-group-text {
      border-radius: 8px 0 0 8px;
      border: 2px solid #e9ecef;
      border-right: none;
      background-color: #f8f9fa;
    }
    
    .btn-primary {
      background: linear-gradient(45deg, var(--primary-color), var(--primary-dark));
      border: none;
      border-radius: 8px;
      padding: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn-primary:hover {
      background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(44, 90, 160, 0.4);
    }
    
    .alert-info {
      border-radius: 8px;
      border-left: 4px solid var(--primary-color);
    }
    
    .text-center a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }
    
    .text-center a:hover {
      color: var(--primary-dark);
      text-decoration: underline;
    }
    
    .forgot-password a {
      color: var(--primary-color);
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .forgot-password a:hover {
      color: var(--primary-dark);
      text-decoration: underline;
    }
    
    #togglePassword {
      border-radius: 0 8px 8px 0;
      border: 2px solid #e9ecef;
      border-left: none;
      background-color: #f8f9fa;
      color: #6c757d;
      transition: all 0.3s ease;
    }
    
    #togglePassword:hover {
      background-color: #e9ecef;
      color: var(--primary-color);
    }
    
    #togglePassword:focus {
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
      border-color: var(--primary-color);
    }
    
    .input-group:focus-within #togglePassword {
      border-color: var(--primary-color);
    }
    
    .input-group:focus-within .form-control {
      border-right: none;
    }
    
    @media (max-width: 768px) {
      .login-box {
        width: 90%;
        margin: 10% auto;
      }
      
      .card-body {
        padding: 1.5rem;
      }
      
      .login-logo h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body class="login-page">
<div class="login-box">
  <div class="login-logo">
    <?php if (hasLogo()): ?>
        <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo">
        <h1>PathLab Pro</h1>
    <?php else: ?>
        <h1>PathLab Pro</h1>
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
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                <i class="fas fa-eye" id="passwordIcon"></i>
              </button>
            </div>
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
          <div class="col-5">
            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
              <span class="btn-text">Sign In</span>
              <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Signing In...
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
        <p class="mb-0 mt-2">
          <a href="index.php" class="text-muted">Back to Home</a>
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
    
    // Handle password toggle
    $('#togglePassword').on('click', function() {
        const passwordField = $('#password');
        const passwordIcon = $('#passwordIcon');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            $(this).attr('title', 'Hide Password');
        } else {
            passwordField.attr('type', 'password');
            passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            $(this).attr('title', 'Show Password');
        }
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
        },
        error: function(xhr, status, error) {
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