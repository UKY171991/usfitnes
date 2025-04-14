<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session at the very beginning
session_start();

// Include required files
try {
    require_once 'config.php';
    require_once 'db_connect.php';
    require_once 'includes/Auth.php';
} catch (Exception $e) {
    error_log("Include Error: " . $e->getMessage());
    die("Failed to load required files. Please check the error logs.");
}

// Insert demo user if the "demo" query parameter is present

        $demoEmail = 'demo@example.com';
        $demoPassword = password_hash('password123', PASSWORD_DEFAULT); // Hash the password
        $db = new PDO(DSN, DB_USER, DB_PASS); // Assuming DSN, DB_USER, and DB_PASS are defined in config.php

        // Check if the demo user already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $demoEmail]);
        if ($stmt->fetchColumn() == 0) {
            // Insert the demo user
            $stmt = $db->prepare("INSERT INTO users (email, password, created_at) VALUES (:email, :password, NOW())");
            $stmt->execute([
                'email' => $demoEmail,
                'password' => $demoPassword
            ]);
            echo "Demo user created successfully. You can log in with email: demo@example.com and password: password123";
        } else {
            echo "Demo user already exists.";
        }
    

// Initialize authentication
try {
    $auth = Auth::getInstance();
} catch (Exception $e) {
    error_log("Auth Error: " . $e->getMessage());
    die("Authentication system error. Please check the error logs.");
}

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password';
        } else {
            if ($auth->login($email, $password)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
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
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
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