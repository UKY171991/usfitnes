<?php
session_start();

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
  <title>PathLab Pro | Registration</title>

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
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    .register-box {
      margin: 5% auto;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 15px 15px 0 0;
    }
    .register-logo {
      color: white;
      font-weight: bold;
    }
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 25px;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    .form-control {
      border-radius: 25px;
      border: 2px solid #e9ecef;
    }
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
  </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">  <div class="register-logo">
    <a href="login.php" class="register-logo"><b>PathLab</b>Pro</a>
  </div>

  <div class="card">
    <div class="card-header text-center">
      <h1 class="h4 text-white mb-0">Register</h1>
    </div>
    <div class="card-body register-card-body">
      <p class="login-box-msg">Register a new membership</p>

      <!-- Alert Messages -->
      <div id="alertMessages"></div>

      <form id="registerForm">
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="First name" id="firstname" name="firstname" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Last name" id="lastname" name="lastname" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" id="email" name="email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="password" name="password" required minlength="6">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Retype password" id="confirm_password" name="confirm_password" required minlength="6">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
              <label for="agreeTerms">
               I agree to the <a href="#">terms</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
              <span id="registerBtnText">Register</span>
              <span id="registerSpinner" class="spinner-border spinner-border-sm d-none ml-2"></span>
            </button>
          </div>
          <!-- /.col -->
        </div>      </form>

      <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">I already have a membership</a>
      </div>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
<!-- /.register-box -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#registerForm').submit(function(e) {
        e.preventDefault();
        
        // Clear previous alerts
        $('#alertMessages').empty();
        
        // Get form data
        const formData = {
            firstname: $('#firstname').val().trim(),
            lastname: $('#lastname').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val(),
            confirm_password: $('#confirm_password').val(),
            terms: $('#agreeTerms').is(':checked'),
            action: 'register'
        };
        
        // Client-side validation
        if(!formData.firstname || !formData.lastname || !formData.email || !formData.password || !formData.confirm_password) {
            showAlert('danger', 'Please fill in all fields.');
            return;
        }
        
        if(formData.password !== formData.confirm_password) {
            showAlert('danger', 'Passwords do not match.');
            return;
        }
        
        if(formData.password.length < 6) {
            showAlert('danger', 'Password must be at least 6 characters long.');
            return;
        }
        
        if(!formData.terms) {
            showAlert('danger', 'Please agree to the terms and conditions.');
            return;
        }
        
        // Show loading state
        $('#registerBtn').prop('disabled', true);
        $('#registerBtnText').text('Registering...');
        $('#registerSpinner').removeClass('d-none');
        
        // Send AJAX request
        $.ajax({
            url: 'api/auth_api.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showAlert('success', 'Registration successful! You can now login.');
                    $('#registerForm')[0].reset();
                    
                    // Redirect to login page after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    showAlert(response.message || 'Registration failed. Please try again.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Registration failed. Please try again.');
                console.error('Registration error:', error);
            },
            complete: function() {
                // Reset button state
                $('#registerBtn').prop('disabled', false);
                $('#registerBtnText').text('Register');
                $('#registerSpinner').addClass('d-none');
            }
        });
    });
    
    // Real-time password validation
    $('#confirm_password').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if(confirmPassword && password !== confirmPassword) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else if(confirmPassword && password === confirmPassword) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-invalid is-valid');
        }
    });
    
    // Email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if(email && !emailPattern.test(email)) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else if(email) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-invalid is-valid');
        }
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon fas ${type === 'success' ? 'fa-check' : 'fa-ban'}"></i> ${message}
        </div>
    `;
    
    $('#alertMessages').html(alertHtml);
    
    // Auto dismiss success messages after 5 seconds
    if(type === 'success') {
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
}
</script>
</body>
</html>
