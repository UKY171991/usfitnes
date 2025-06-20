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
    </div>    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to access laboratory system</p>

      <!-- Alert Messages -->
      <div id="alertContainer"></div>
      
      <!-- Demo Login Info -->
      <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-info"></i> Demo Login:</h5>
        <strong>Username:</strong> admin<br>
        <strong>Password:</strong> password
      </div>

      <form id="loginForm">
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Username" id="username" name="username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>
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
