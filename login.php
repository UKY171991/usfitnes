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
  <title>PathLab Pro | Laboratory Management System</title>

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
    
    .hero-section {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .hero-content {
      z-index: 2;
      max-width: 800px;
      padding: 2rem;
    }
    
    .hero-background {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .logo-container {
      margin-bottom: 2rem;
    }
    
    .logo-container img {
      max-width: 120px;
      height: auto;
      margin-bottom: 1rem;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }
    
    .hero-title {
      font-size: 4rem;
      font-weight: 300;
      margin-bottom: 1rem;
      text-shadow: 0 4px 8px rgba(0,0,0,0.3);
      line-height: 1.2;
    }
    
    .hero-subtitle {
      font-size: 1.5rem;
      margin-bottom: 3rem;
      opacity: 0.9;
      font-weight: 300;
    }
    
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin: 3rem 0;
    }
    
    .feature-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    
    .feature-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: rgba(255,255,255,0.9);
    }
    
    .feature-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    
    .feature-description {
      opacity: 0.8;
      line-height: 1.6;
    }
    
    .cta-buttons {
      margin-top: 3rem;
    }
    
    .btn-hero {
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      display: inline-block;
      margin: 0 1rem;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }
    
    .btn-primary-hero {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: rgba(255, 255, 255, 0.3);
    }
    
    .btn-primary-hero:hover {
      background: white;
      color: var(--primary-color);
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      text-decoration: none;
    }
    
    .btn-secondary-hero {
      background: transparent;
      color: white;
      border-color: rgba(255, 255, 255, 0.5);
    }
    
    .btn-secondary-hero:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: white;
      transform: translateY(-3px);
      text-decoration: none;
      color: white;
    }
    
    .stats-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 2rem;
      margin: 3rem 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 2rem;
      text-align: center;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      display: block;
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      opacity: 0.8;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .hero-subtitle {
        font-size: 1.2rem;
      }
      
      .features-grid {
        grid-template-columns: 1fr;
      }
      
      .btn-hero {
        display: block;
        margin: 1rem 0;
      }
      
      .stats-section {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>
<body>
<div class="hero-section">
  <div class="hero-background"></div>
  
  <div class="hero-content">
    <div class="logo-container">
      <?php if (hasLogo()): ?>
        <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo">
      <?php endif; ?>
    </div>
    
    <h1 class="hero-title">PathLab Pro</h1>
    <p class="hero-subtitle">Advanced Laboratory Management System</p>
    
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-flask"></i>
        </div>
        <h3 class="feature-title">Sample Management</h3>
        <p class="feature-description">Track and manage laboratory samples with complete traceability and workflow automation.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <h3 class="feature-title">Analytics & Reports</h3>
        <p class="feature-description">Generate comprehensive reports and analytics for better decision making and compliance.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-users"></i>
        </div>
        <h3 class="feature-title">Patient Management</h3>
        <p class="feature-description">Manage patient records, test orders, and results in a centralized system.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-microscope"></i>
        </div>
        <h3 class="feature-title">Equipment Tracking</h3>
        <p class="feature-description">Monitor laboratory equipment, maintenance schedules, and calibration records.</p>
      </div>
    </div>
    
    <div class="stats-section">
      <div class="stat-item">
        <span class="stat-number">99.9%</span>
        <span class="stat-label">Accuracy</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">24/7</span>
        <span class="stat-label">Support</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">1000+</span>
        <span class="stat-label">Labs Served</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">ISO</span>
        <span class="stat-label">Certified</span>
      </div>
    </div>
    
    <div class="cta-buttons">
      <a href="login.php" class="btn-hero btn-primary-hero">
        <i class="fas fa-sign-in-alt mr-2"></i> Access System
      </a>
      <a href="register.php" class="btn-hero btn-secondary-hero">
        <i class="fas fa-user-plus mr-2"></i> Register
      </a>
    </div>
    
    <div class="mt-4">
      <p style="opacity: 0.7; font-size: 0.9rem;">
        Demo Login: <strong>admin</strong> / <strong>password</strong>
      </p>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Add smooth scroll animation for feature cards
    $('.feature-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });
    
    // Add floating animation
    setInterval(function() {
        $('.feature-card').each(function() {
            if (Math.random() > 0.7) {
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => {
                    $(this).removeClass('animate__animated animate__pulse');
                }, 1000);
            }
        });
    }, 3000);
});
</script>
</body>
</html>
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
