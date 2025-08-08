<?php
session_start();
require_once 'config.php';

// Test script to check session and user data
echo "Session Status: " . (isset($_SESSION['user_id']) ? "Active" : "Not Active") . "<br>";

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found: " . $user['name'] . "<br>";
        echo "User type: " . $user['user_type'] . "<br>";
    } else {
        echo "User not found in database<br>";
    }
} else {
    echo "No session found. Creating demo session...<br>";
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'System Administrator';
    $_SESSION['user_type'] = 'admin';
    echo "Demo session created. <a href='dashboard.php'>Go to Dashboard</a>";
}
?>
