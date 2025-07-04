<?php
// Ensure session is started only if it doesn't exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Only redirect if we're not already on the login page or handling an API request
    $current_page = basename($_SERVER['PHP_SELF']);
    if($current_page != 'login.php' && $current_page != 'index.php' && strpos($current_page, '_api.php') === false) {
        header("Location: login.php");
        exit();
    }
}

// Set user information variables for use in templates
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Guest';
$full_name = $_SESSION['full_name'] ?? 'Guest User';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$user_initial = strtoupper(substr($full_name, 0, 1));

// Function to check if a menu item should be marked as active
function isActive($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page_name) ? 'active' : '';
}

// Function to get logo path or return null if no logo exists
function getLogoPath() {
    $logoPath = 'img/logo.svg';
    $altLogoPath = 'img/logo.png';
    
    if (file_exists($logoPath)) {
        return $logoPath;
    } elseif (file_exists($altLogoPath)) {
        return $altLogoPath;
    }
    
    return null;
}

// Function to check if logo exists
function hasLogo() {
    return getLogoPath() !== null;
}
?>
