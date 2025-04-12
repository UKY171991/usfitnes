<?php
require_once 'config.php';
require_once 'includes/Auth.php';

// Initialize authentication
$auth = Auth::getInstance();

// Perform logout
$auth->logout();

// Destroy session
session_start();
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}
session_destroy();

// Clear any other cookies if they exist
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time()-42000, '/');
}

// Redirect to login page with a logout message
header("Location: login.php?msg=logout_success");
exit();
?> 