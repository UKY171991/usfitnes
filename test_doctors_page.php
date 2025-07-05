<?php
session_start();
require_once 'config.php';

// Function to test authentication
function testAuth() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}

// Function to test database connection
function testDatabaseConnection() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT 1");
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to test doctors API
function testDoctorsAPI() {
    $url = 'https://usfitnes.com/api/doctors_api.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['success' => false, 'error' => 'Failed to connect to API'];
    }
    
    $data = json_decode($response, true);
    return $data;
}

// Test login with demo credentials
function testLogin($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, username, full_name, user_type, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Invalid password'];
            }
        } else {
            return ['success' => false, 'error' => 'User not found'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>PathLab Pro - Doctors Page Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .test-result.pass { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .test-result.fail { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .test-result.warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>PathLab Pro - Doctors Page Diagnostics</h1>
    
    <div class="test-section">
        <h2>1. Database Connection Test</h2>
        <?php 
        $dbTest = testDatabaseConnection();
        if ($dbTest): ?>
            <div class="test-result pass">✅ Database connection: PASSED</div>
        <?php else: ?>
            <div class="test-result fail">❌ Database connection: FAILED</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>2. Demo User Login Test</h2>
        <?php 
        $loginTest = testLogin('admin', 'password');
        if ($loginTest['success']): ?>
            <div class="test-result pass">✅ Demo user login: PASSED</div>
            <pre><?php echo htmlspecialchars(json_encode($loginTest['user'], JSON_PRETTY_PRINT)); ?></pre>
        <?php else: ?>
            <div class="test-result fail">❌ Demo user login: FAILED</div>
            <div class="error">Error: <?php echo htmlspecialchars($loginTest['error']); ?></div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>3. Current Session Status</h2>
        <?php if (testAuth()): ?>
            <div class="test-result pass">✅ User is authenticated</div>
            <div>User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></div>
            <div>Username: <?php echo $_SESSION['username'] ?? 'Not set'; ?></div>
            <div>Full Name: <?php echo $_SESSION['full_name'] ?? 'Not set'; ?></div>
        <?php else: ?>
            <div class="test-result fail">❌ User is not authenticated</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>4. Doctors API Test</h2>
        <?php 
        $apiTest = testDoctorsAPI();
        if ($apiTest && isset($apiTest['success'])): ?>
            <?php if ($apiTest['success']): ?>
                <div class="test-result pass">✅ Doctors API: PASSED</div>
                <div>Doctors count: <?php echo count($apiTest['data'] ?? []); ?></div>
            <?php else: ?>
                <div class="test-result fail">❌ Doctors API: FAILED</div>
                <div class="error">Error: <?php echo htmlspecialchars($apiTest['message'] ?? 'Unknown error'); ?></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="test-result fail">❌ Doctors API: FAILED to connect</div>
        <?php endif; ?>
        <pre><?php echo htmlspecialchars(json_encode($apiTest, JSON_PRETTY_PRINT)); ?></pre>
    </div>

    <div class="test-section">
        <h2>5. Required Files Check</h2>
        <?php 
        $files = [
            'includes/header.php',
            'includes/footer.php', 
            'includes/sidebar.php',
            'includes/init.php',
            'api/doctors_api.php',
            'js/common.js',
            'css/custom.css'
        ];
        
        foreach ($files as $file): 
            if (file_exists($file)): ?>
                <div class="test-result pass">✅ <?php echo $file; ?>: EXISTS</div>
            <?php else: ?>
                <div class="test-result fail">❌ <?php echo $file; ?>: MISSING</div>
            <?php endif;
        endforeach; ?>
    </div>

    <div class="test-section">
        <h2>6. Test Actions</h2>
        <?php if (!testAuth()): ?>
            <form method="post" style="margin: 10px 0;">
                <button type="submit" name="login_demo" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px;">
                    Login as Demo User (admin/password)
                </button>
            </form>
        <?php else: ?>
            <form method="post" style="margin: 10px 0;">
                <button type="submit" name="logout" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 3px;">
                    Logout
                </button>
            </form>
        <?php endif; ?>
        
        <a href="doctors.php" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            Go to Doctors Page
        </a>
        <a href="login.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            Go to Login Page
        </a>
    </div>
</body>
</html>

<?php
// Handle login demo user action
if (isset($_POST['login_demo'])) {
    $loginResult = testLogin('admin', 'password');
    if ($loginResult['success']) {
        $user = $loginResult['user'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_type'] = $user['user_type'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle logout action
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
