<?php
// Session is already started in config.php
if(!isset($config_loaded)) {
    require_once __DIR__ . '/../inc/config.php';
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /usfitnes/auth/login.php");
    exit();
}

// Check if user has branch admin role
if ($_SESSION['role'] !== 'branch_admin') {
    header("Location: /usfitnes/auth/unauthorized.php");
    exit();
}

// Check if branch_id is set
if (!isset($_SESSION['branch_id'])) {
    header("Location: /usfitnes/auth/unauthorized.php");
    exit();
}

// Function to log activity
function logActivity($conn, $description) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO activities (user_id, description, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $description]);
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}
?> 