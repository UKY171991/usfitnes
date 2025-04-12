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
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
                if ($stmt->execute([$token, $expiry, $email])) {
                    // For testing purposes, display the reset link
                    // In production, this should be sent via email
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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo APP_NAME; ?> - Forgot Password</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE 4 | Forgot Password" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
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
            font-family: 'Nunito', sans-serif;
        }

        .forgot-password-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
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
        }

        .card-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .card-body {
            padding: 2rem;
            background-color: white;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.25);
        }

        .back-to-login {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-to-login:hover {
            color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .input-group-text {
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
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
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Handle alerts
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
            
            if (form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                });
            }
        });
    </script>
  </body>
  <!--end::Body-->
</html>