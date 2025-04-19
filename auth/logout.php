<?php
require_once '../inc/config.php';
require_once '../inc/db.php';

// Remove redundant session_start since it's in config.php
// session_start();

// Log activity if user is logged in
if(isset($_SESSION['user_id'])) {
    try {
        $activity = "User logged out: {$_SESSION['username']}";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
    } catch(PDOException $e) {
        // Log error but continue with logout
        error_log("Error logging logout activity: " . $e->getMessage());
    }
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?> 