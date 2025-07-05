<?php
// Simple login test
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle login
if (isset($_POST['login'])) {
    require_once 'config.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            $stmt = $conn->prepare("SELECT id, username, full_name, user_type, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    header("Location: doctors.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "User not found";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter username and password";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 400px; margin: 0 auto; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .success { color: green; background: #e6ffe6; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: blue; background: #e6f3ff; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quick Login Test</h2>
        
        <?php if (isset($error)): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="success">✅ Logged in as: <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
            <div class="info">
                <strong>Session Info:</strong><br>
                User ID: <?php echo $_SESSION['user_id']; ?><br>
                Username: <?php echo $_SESSION['username']; ?><br>
                User Type: <?php echo $_SESSION['user_type']; ?>
            </div>
            <p>
                <a href="doctors.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                    Go to Doctors Page
                </a>
                <a href="?logout=1" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;">
                    Logout
                </a>
            </p>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="admin" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <div class="info">
                <strong>Demo Credentials:</strong><br>
                Username: admin<br>
                Password: password
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
