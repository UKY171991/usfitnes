<?php
// Auto-login and redirect test
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, auto-login with demo credentials
if (!isset($_SESSION['user_id'])) {
    require_once 'config.php';
    
    try {
        $stmt = $conn->prepare("SELECT id, username, full_name, user_type, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username = 'admin');
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify('password', $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
            }
        }
    } catch (Exception $e) {
        // Handle error
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auto Login & Redirect</title>
    <meta http-equiv="refresh" content="1;url=doctors_fixed.php">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px;
            background: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>PathLab Pro - Auto Login</h2>
        <div class="spinner"></div>
        <p>Logging in with demo credentials and redirecting to doctors page...</p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="color: green;">
                ✅ Successfully logged in as: <?php echo htmlspecialchars($_SESSION['full_name']); ?>
            </div>
        <?php else: ?>
            <div style="color: red;">
                ❌ Login failed. <a href="login.php">Click here to login manually</a>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px;">
            <a href="doctors_fixed.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Go to Fixed Doctors Page
            </a>
        </p>
        
        <p style="font-size: 12px; color: #666; margin-top: 20px;">
            If not redirected automatically, <a href="doctors_fixed.php">click here</a>
        </p>
    </div>
    
    <script>
        // JavaScript redirect as backup
        setTimeout(function() {
            window.location.href = 'doctors_fixed.php';
        }, 2000);
    </script>
</body>
</html>
