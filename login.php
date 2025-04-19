<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => 3600, // 1 hour
    'cookie_lifetime' => 3600 // 1 hour
]);

// Include required files
try {
    require_once 'config.php';
    require_once 'db_connect.php';
    require_once 'includes/Auth.php';
} catch (Exception $e) {
    error_log("Include Error: " . $e->getMessage());
    die("Failed to load required files. Please check the error logs.");
}

// Initialize authentication
try {
    $auth = Auth::getInstance();
} catch (Exception $e) {
    error_log("Auth Error: " . $e->getMessage());
    die("Authentication system error. Please check the error logs.");
}

// Check for brute force attempts
function checkBruteForce($email) {
    $db = Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = time() - LOGIN_TIMEOUT;
    
    try {
        $stmt = $db->query(
            "SELECT COUNT(*) FROM login_attempts 
            WHERE ip_address = :ip AND attempt_time > :time",
            ['ip' => $ip, 'time' => $time]
        );
        $attempts = $stmt->fetchColumn();
        
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Brute force check error: " . $e->getMessage());
        return false;
    }
}

// Record login attempt
function recordLoginAttempt($email, $success) {
    $db = Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $db->query(
            "INSERT INTO login_attempts (email, ip_address, attempt_time, success) 
            VALUES (:email, :ip, :time, :success)",
            [
                'email' => $email,
                'ip' => $ip,
                'time' => time(),
                'success' => $success
            ]
        );
    } catch (Exception $e) {
        error_log("Login attempt recording error: " . $e->getMessage());
    }
}

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_time'] = time();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid request');
        }
        
        // Check CSRF token lifetime
        if (time() - $_SESSION['csrf_time'] > CSRF_TOKEN_LIFETIME) {
            throw new Exception('Session expired. Please refresh the page.');
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!$email) {
            throw new Exception('Invalid email format');
        }

        // Check for brute force
        if (checkBruteForce($email)) {
            throw new Exception('Too many login attempts. Please try again later.');
        }

        if ($auth->login($email, $password)) {
            // Fetch branch_id automatically from the user's account
            $stmt = $pdo->prepare("SELECT branch_id FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $branch = $stmt->fetch();

            if ($branch) {
                $_SESSION['branch_id'] = $branch['branch_id'];
            }

            // Regenerate session ID after successful login
            session_regenerate_id(true);

            // Record successful login
            recordLoginAttempt($email, true);

            // Redirect based on role
            header("Location: dashboard.php");
            exit();
        } else {
            // Record failed login
            recordLoginAttempt($email, false);
            $error = "Invalid login credentials.";
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
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
            font-family: 'Nunito', sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .forgot-password {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
            animation: slideIn 0.3s ease-in-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .input-group-text {
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }

        .password-toggle {
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .card-header, .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h3><?php echo APP_NAME; ?></h3>
                <p>Welcome back! Please login to your account</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            <span class="input-group-text password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                    
                    <div class="text-center">
                        <a href="forgot-password.php" class="forgot-password">
                            <i class="bi bi-question-circle me-1"></i>Forgot Password?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>