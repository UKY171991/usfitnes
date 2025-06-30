<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<h3>Session Status:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

echo "<h3>Session Variables:</h3>";
if (!empty($_SESSION)) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "No session variables found.<br>";
}

echo "<h3>Authentication Check:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<div style='color: green;'>✓ User is logged in</div>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
    echo "Full Name: " . ($_SESSION['full_name'] ?? 'Not set') . "<br>";
    echo "User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
    echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
} else {
    echo "<div style='color: red;'>✗ User is not logged in</div>";
}

echo "<h3>Quick Actions:</h3>";
echo "<a href='index.php'>Go to Login</a> | ";
echo "<a href='dashboard.php'>Go to Dashboard</a> | ";
echo "<a href='users.php'>Go to Users</a><br><br>";

// Test login with default credentials
if (isset($_POST['auto_login'])) {
    require_once 'config.php';
    
    echo "<h3>Auto-Login Test:</h3>";
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, user_type FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify('password', $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['role'] = $user['user_type'];
            
            echo "<div style='color: green;'>✓ Auto-login successful! <a href='users.php'>Go to Users Page</a></div>";
        } else {
            echo "<div style='color: red;'>✗ Auto-login failed - Invalid credentials</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>✗ Auto-login error: " . $e->getMessage() . "</div>";
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    echo "<div style='color: orange;'>Session destroyed. <a href='debug_session.php'>Refresh</a></div>";
}
?>

<form method="POST" style="margin-top: 20px;">
    <button type="submit" name="auto_login" value="1">Auto-Login as Admin</button>
    <button type="submit" name="logout" value="1">Logout</button>
</form>
