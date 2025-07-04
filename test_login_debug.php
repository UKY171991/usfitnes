<?php
// Simple debug test for login page
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; margin: 10px 0; padding: 15px; }
    </style>
</head>
<body>
    <h1>Login Page Debug Test</h1>
    
    <div class="test-section">
        <h3>Logo Test</h3>
        <p>Logo exists: <?php echo hasLogo() ? 'YES' : 'NO'; ?></p>
        <p>Logo path: <?php echo getLogoPath() ?: 'None'; ?></p>
        <?php if (hasLogo()): ?>
            <img src="<?php echo getLogoPath(); ?>" alt="Logo" style="max-width: 100px;">
        <?php else: ?>
            <p>No logo found - will show title instead</p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h3>Session Test</h3>
        <p>Session Status: <?php echo session_status(); ?></p>
        <p>User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></p>
        <p>Should redirect to dashboard: <?php echo isset($_SESSION['user_id']) ? 'YES' : 'NO'; ?></p>
    </div>
    
    <div class="test-section">
        <h3>File Paths Test</h3>
        <p>index.php exists: <?php echo file_exists('index.php') ? 'YES' : 'NO'; ?></p>
        <p>CSS file exists: <?php echo file_exists('css/custom.css') ? 'YES' : 'NO'; ?></p>
        <p>AdminLTE CDN test: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css"></p>
    </div>
    
    <div class="test-section">
        <h3>Simple Login Form Test</h3>
        <form style="background: #f0f0f0; padding: 20px;">
            <div style="margin: 10px 0;">
                <label>Username:</label><br>
                <input type="text" name="username" value="admin" style="padding: 5px; width: 200px;">
            </div>
            <div style="margin: 10px 0;">
                <label>Password:</label><br>
                <input type="password" name="password" value="password" style="padding: 5px; width: 200px;">
            </div>
            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none;">Login</button>
        </form>
    </div>
    
    <div class="test-section">
        <h3>Database Connection Test</h3>
        <?php
        try {
            require_once 'config.php';
            $test_query = $pdo->query("SELECT COUNT(*) as count FROM users");
            $result = $test_query->fetch();
            echo "<p style='color: green;'>Database connection: SUCCESS</p>";
            echo "<p>Users count: " . $result['count'] . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Database connection: FAILED - " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</body>
</html>
