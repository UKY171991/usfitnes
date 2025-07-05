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
    .terms-link {
      color: #667eea !important;
      text-decoration: none !important;
      transition: all 0.3s ease;
    }
    .terms-link:hover {
      color: #5a6fd8 !important;
      text-decoration: underline !important;
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

      <!-- Registration Form (Step 1) -->
      <form id="registerForm" style="display: block;">
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
               I agree to the <a href="terms-and-conditions.php" target="_blank" class="terms-link">Terms & Conditions</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
              <span id="registerBtnText">Send OTP</span>
              <span id="registerSpinner" class="spinner-border spinner-border-sm d-none ml-2"></span>
            </button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <!-- OTP Verification Form (Step 2) -->
      <form id="otpForm" style="display: none;">
        <div class="text-center mb-3">
          <h5>Email Verification</h5>
          <p class="text-muted">We've sent a 6-digit verification code to</p>
          <strong id="verificationEmail"></strong>
        </div>
        
        <div class="input-group mb-3">
          <input type="text" class="form-control text-center" placeholder="Enter 6-digit OTP" id="otp" name="otp" required maxlength="6" style="letter-spacing: 0.5em; font-size: 1.2em;">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-key"></span>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-6">
            <button type="button" class="btn btn-secondary btn-block" id="backBtn">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </button>
          </div>
          <div class="col-6">
            <button type="submit" class="btn btn-primary btn-block" id="verifyBtn">
              <span id="verifyBtnText">Verify</span>
              <span id="verifySpinner" class="spinner-border spinner-border-sm d-none ml-2"></span>
            </button>
          </div>
        </div>
        
        <div class="text-center mt-3">
          <p class="text-muted mb-1">Didn't receive the code?</p>
          <button type="button" class="btn btn-link p-0" id="resendBtn">
            <span id="resendBtnText">Resend OTP</span>
            <span id="resendSpinner" class="spinner-border spinner-border-sm d-none ml-2"></span>
          </button>
          <div id="resendTimer" class="text-muted mt-1" style="display: none;">
            Resend available in <span id="countdown">60</span> seconds
          </div>
        </div>
      </form>

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
let currentEmail = '';
let resendCountdown = 0;
let resendTimer = null;

$(document).ready(function() {
    // Step 1: Send OTP
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
            action: 'send_otp'
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
        $('#registerBtnText').text('Sending OTP...');
        $('#registerSpinner').removeClass('d-none');
        
        // Send AJAX request
        $.ajax({
            url: 'api/otp_api.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    currentEmail = formData.email;
                    $('#verificationEmail').text(currentEmail);
                    
                    // Show OTP form, hide registration form
                    $('#registerForm').hide();
                    $('#otpForm').show();
                    
                    showAlert('success', response.message);
                    
                    // Start resend timer
                    startResendTimer();
                    
                    // Focus on OTP input
                    $('#otp').focus();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseJSON;
                showAlert('danger', response ? response.message : 'Failed to send OTP. Please try again.');
            },
            complete: function() {
                // Reset button state
                $('#registerBtn').prop('disabled', false);
                $('#registerBtnText').text('Send OTP');
                $('#registerSpinner').addClass('d-none');
            }
        });
    });
    
    // Step 2: Verify OTP
    $('#otpForm').submit(function(e) {
        e.preventDefault();
        
        const otp = $('#otp').val().trim();
        
        if(!otp || otp.length !== 6) {
            showAlert('danger', 'Please enter a valid 6-digit OTP.');
            return;
        }
        
        // Show loading state
        $('#verifyBtn').prop('disabled', true);
        $('#verifyBtnText').text('Verifying...');
        $('#verifySpinner').removeClass('d-none');
        
        // Clear previous alerts
        $('#alertMessages').empty();
        
        // Send verification request
        $.ajax({
            url: 'api/otp_api.php',
            method: 'POST',
            data: {
                email: currentEmail,
                otp: otp,
                action: 'verify_otp'
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showAlert('success', response.message);
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseJSON;
                showAlert('danger', response ? response.message : 'Verification failed. Please try again.');
            },
            complete: function() {
                // Reset button state
                $('#verifyBtn').prop('disabled', false);
                $('#verifyBtnText').text('Verify');
                $('#verifySpinner').addClass('d-none');
            }
        });
    });
    
    // Back button
    $('#backBtn').click(function() {
        $('#otpForm').hide();
        $('#registerForm').show();
        $('#otp').val('');
        $('#alertMessages').empty();
        stopResendTimer();
    });
    
    // Resend OTP
    $('#resendBtn').click(function() {
        if(resendCountdown > 0) return;
        
        // Show loading state
        $('#resendBtn').prop('disabled', true);
        $('#resendBtnText').text('Sending...');
        $('#resendSpinner').removeClass('d-none');
        
        $.ajax({
            url: 'api/otp_api.php',
            method: 'POST',
            data: {
                email: currentEmail,
                action: 'resend_otp'
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showAlert('success', response.message);
                    startResendTimer();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseJSON;
                showAlert('danger', response ? response.message : 'Failed to resend OTP. Please try again.');
            },
            complete: function() {
                // Reset button state
                $('#resendBtn').prop('disabled', false);
                $('#resendBtnText').text('Resend OTP');
                $('#resendSpinner').addClass('d-none');
            }
        });
    });
    
    // OTP input formatting
    $('#otp').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if(value.length > 6) value = value.substr(0, 6);
        $(this).val(value);
        
        // Auto-submit when 6 digits entered
        if(value.length === 6) {
            setTimeout(() => $('#otpForm').submit(), 500);
        }
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

function startResendTimer() {
    resendCountdown = 60;
    $('#resendBtn').prop('disabled', true);
    $('#resendTimer').show();
    
    resendTimer = setInterval(function() {
        resendCountdown--;
        $('#countdown').text(resendCountdown);
        
        if(resendCountdown <= 0) {
            stopResendTimer();
        }
    }, 1000);
}

function stopResendTimer() {
    if(resendTimer) {
        clearInterval(resendTimer);
        resendTimer = null;
    }
    resendCountdown = 0;
    $('#resendBtn').prop('disabled', false);
    $('#resendTimer').hide();
}

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
    
    // Scroll to top to show alert
    $('html, body').animate({
        scrollTop: 0
    }, 300);
}
</script>
</body>
</html>
