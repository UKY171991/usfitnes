<?php
require_once 'includes/Auth.php';

$auth = Auth::getInstance();
$auth->logout();

// Clear session cookie manually
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}


// Redirect to login page
header("Location: login.php");
exit();