<?php
// Test the login API directly
header('Content-Type: text/html');

echo "<h2>API Login Test</h2>";

// Test POST data
$testData = [
    'action' => 'login',
    'username' => 'admin',
    'password' => 'password'
];

// Use cURL to test the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/api/auth_api.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>API Test Results:</h3>";
echo "<p>HTTP Status Code: " . $httpCode . "</p>";

if ($error) {
    echo "<p>cURL Error: " . $error . "</p>";
} else {
    echo "<p>Response:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode JSON
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "<h4>Decoded Response:</h4>";
        echo "<ul>";
        echo "<li>Success: " . ($decoded['success'] ? 'true' : 'false') . "</li>";
        echo "<li>Message: " . ($decoded['message'] ?? 'N/A') . "</li>";
        if (isset($decoded['data'])) {
            echo "<li>Data: " . print_r($decoded['data'], true) . "</li>";
        }
        echo "</ul>";
    }
}

// Also test direct inclusion
echo "<h3>Direct API Test:</h3>";
try {
    require_once 'config.php';
    
    // Test the login function logic directly
    $stmt = $pdo->prepare("SELECT id, username, password, name, user_type FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify('password', $user['password'])) {
        echo "<p>✅ Direct database login test successful</p>";
        echo "<p>User data: " . print_r($user, true) . "</p>";
    } else {
        echo "<p>❌ Direct database login test failed</p>";
        if (!$user) {
            echo "<p>User not found</p>";
        } else {
            echo "<p>Password verification failed</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
