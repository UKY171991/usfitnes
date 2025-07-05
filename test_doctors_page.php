<?php
// PathLab Pro - Doctors Page Diagnostics (Full Rewrite)
session_start();
require_once 'config.php';

// --- Utility Functions ---
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function testDatabaseConnection() {
    global $conn;
    if (!isset($conn) || !$conn) return [false, 'No DB connection object'];
    try {
        $stmt = $conn->prepare('SELECT 1');
        $stmt->execute();
        return [true, 'Connection successful'];
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }
}

function testLogin($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT id, username, full_name, user_type, password FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return [true, $user];
            } else {
                return [false, 'Invalid password'];
            }
        } else {
            return [false, 'User not found'];
        }
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }
}

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
        return [false, 'Failed to connect to API', null];
    }
    $data = json_decode($response, true);
    if (is_array($data) && isset($data['success'])) {
        return [$data['success'], $data['message'] ?? '', $data];
    }
    return [false, 'Invalid API response', $data];
}

function checkRequiredFiles($files) {
    $results = [];
    foreach ($files as $file) {
        $results[$file] = file_exists($file);
    }
    return $results;
}

// --- Handle Actions ---
if (isset($_POST['login_demo'])) {
    list($ok, $userOrError) = testLogin('admin', 'password');
    if ($ok) {
        $_SESSION['user_id'] = $userOrError['id'];
        $_SESSION['username'] = $userOrError['username'];
        $_SESSION['full_name'] = $userOrError['full_name'];
        $_SESSION['user_type'] = $userOrError['user_type'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// --- Run Tests ---
list($dbOk, $dbMsg) = testDatabaseConnection();
list($loginOk, $loginResult) = testLogin('admin', 'password');
list($apiOk, $apiMsg, $apiData) = testDoctorsAPI();
$requiredFiles = [
    'includes/header.php',
    'includes/footer.php',
    'includes/sidebar.php',
    'includes/init.php',
    'api/doctors_api.php',
    'js/common.js',
    'css/custom.css'
];
$fileCheck = checkRequiredFiles($requiredFiles);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctors Page Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f8; }
        h1 { color: #333; }
        .test-section { margin: 24px 0; padding: 18px; background: #fff; border: 1px solid #e0e0e0; border-radius: 7px; box-shadow: 0 2px 8px #0001; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; font-size: 1.05em; }
        .pass { background: #e6f9e6; border: 1px solid #b2e6b2; color: #1a7f1a; }
        .fail { background: #ffeaea; border: 1px solid #ffb3b3; color: #b30000; }
        .warning { background: #fffbe6; border: 1px solid #ffe58f; color: #b38f00; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 3px; border: none; text-decoration: none; font-weight: bold; margin: 5px 0; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <h1>Doctors Page Diagnostics</h1>

    <div class="test-section">
        <h2>1. Database Connection</h2>
        <div class="test-result <?php echo $dbOk ? 'pass' : 'fail'; ?>">
            <?php echo $dbOk ? '✅ Connection successful' : '❌ Connection failed'; ?>
            <br><small><?php echo htmlspecialchars($dbMsg); ?></small>
        </div>
    </div>

    <div class="test-section">
        <h2>2. Demo User Login</h2>
        <div class="test-result <?php echo $loginOk ? 'pass' : 'fail'; ?>">
            <?php echo $loginOk ? '✅ Login successful' : '❌ Login failed'; ?>
            <br><small><?php echo $loginOk ? 'User: ' . htmlspecialchars($loginResult['username']) : htmlspecialchars($loginResult); ?></small>
        </div>
        <?php if ($loginOk): ?>
            <pre><?php echo htmlspecialchars(json_encode($loginResult, JSON_PRETTY_PRINT)); ?></pre>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>3. Session Status</h2>
        <?php if (isAuthenticated()): ?>
            <div class="test-result pass">✅ User is authenticated</div>
            <div>User ID: <?php echo $_SESSION['user_id']; ?></div>
            <div>Username: <?php echo $_SESSION['username']; ?></div>
            <div>Full Name: <?php echo $_SESSION['full_name']; ?></div>
        <?php else: ?>
            <div class="test-result fail">❌ User is not authenticated</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>4. Doctors API</h2>
        <div class="test-result <?php echo $apiOk ? 'pass' : 'fail'; ?>">
            <?php echo $apiOk ? '✅ API Success' : '❌ API Failed'; ?>
            <br><small><?php echo htmlspecialchars($apiMsg); ?></small>
        </div>
        <pre><?php echo htmlspecialchars(json_encode($apiData, JSON_PRETTY_PRINT)); ?></pre>
    </div>

    <div class="test-section">
        <h2>5. Required Files</h2>
        <?php foreach ($fileCheck as $file => $exists): ?>
            <div class="test-result <?php echo $exists ? 'pass' : 'fail'; ?>">
                <?php echo $exists ? '✅' : '❌'; ?> <?php echo htmlspecialchars($file); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="test-section">
        <h2>6. Actions</h2>
        <?php if (!isAuthenticated()): ?>
            <form method="post">
                <button type="submit" name="login_demo" class="btn btn-primary">Login as Demo User (admin/password)</button>
            </form>
        <?php else: ?>
            <form method="post">
                <button type="submit" name="logout" class="btn btn-danger">Logout</button>
            </form>
        <?php endif; ?>
        <a href="doctors.php" class="btn btn-success">Go to Doctors Page</a>
        <a href="login.php" class="btn btn-secondary">Go to Login Page</a>
    </div>
</body>
</html>
