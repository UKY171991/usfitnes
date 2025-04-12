<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include required files
try {
    require_once 'config.php';
    require_once 'db_connect.php';
} catch (Exception $e) {
    die("Configuration Error: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception("Database query preparation failed");
            }
            
            if (!$stmt->execute([$email])) {
                throw new Exception("Database query execution failed");
            }
            
            if ($stmt->fetch()) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token
                $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
                if (!$updateStmt) {
                    throw new Exception("Failed to prepare update statement");
                }
                
                if ($updateStmt->execute([$token, $expiry, $email])) {
                    // For testing purposes, display the reset link
                    $resetLink = APP_URL . "/reset-password.php?token=" . $token;
                    $success = 'Password reset instructions have been sent to your email.<br>
                              <small class="text-muted">(For testing: <a href="'.$resetLink.'">'.$resetLink.'</a>)</small>';
                } else {
                    throw new Exception("Failed to update reset token");
                }
            } else {
                $error = 'No account found with that email address';
            }
        } catch (Exception $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --background-color: #f8f9fc;
        }

        body {
            background: linear-gradient(135deg, var(--background-color) 0%, #e3e6f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .forgot-password-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
            margin: auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
            background: white;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border-bottom: none;
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .card-header h3 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
            color: white;
        }

        .card-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 0.95rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }

        .card-body {
            padding: 2rem;
            background-color: white;
        }

        .form-control {
            height: auto;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        .input-group-text {
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            border-right: none;
            padding: 0.75rem 1rem;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary:disabled {
            background: #6c757d;
            transform: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .back-to-login {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-to-login:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .success-icon {
            font-size: 3rem;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .forgot-password-container {
                padding: 10px;
            }
            
            .card-header, .card-body {
                padding: 1.5rem;
            }
        }
    </style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="login-page bg-body-secondary">
    <div class="forgot-password-container">
        <div class="card">
            <div class="card-header">
                <h3>Forgot Password</h3>
                <p>Enter your email address and we'll send you instructions to reset your password</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle success-icon"></i>
                        <div class="alert alert-success mb-4">
                            <?php echo $success; ?>
                        </div>
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Return to Login
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" id="forgotPasswordForm">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter your registered email" required
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-4" id="submitBtn">
                            <i class="bi bi-send me-2"></i>Send Reset Instructions
                        </button>
                        
                        <div class="text-center">
                            <a href="login.php" class="back-to-login">
                                <i class="bi bi-arrow-left"></i>
                                Back to Login
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- /.login-box -->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle alerts auto-dismiss
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Handle form submission
            const form = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!email.value.trim()) {
                        e.preventDefault();
                        return;
                    }
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                });
            }
        });
    </script>
  </body>
  <!--end::Body-->
</html>