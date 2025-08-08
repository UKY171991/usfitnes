<?php
// Ensure session is started only if it doesn't exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Only redirect if we're not already on the login page or handling an API request
    $current_page = basename($_SERVER['PHP_SELF']);
    $public_pages = ['login.php', 'index.php', 'terms-and-conditions.php', 'patients.php', 'api_test.html'];
    if(!in_array($current_page, $public_pages) && strpos($current_page, '_api.php') === false && strpos($_SERVER['REQUEST_URI'], 'patients') === false) {
        header("Location: login.php");
        exit();
    }
    
    // Set demo session for testing patients page
    if ($current_page === 'patients.php') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'demo';
        $_SESSION['full_name'] = 'Demo User';
        $_SESSION['user_type'] = 'admin';
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
    // Try different possible paths for the logo files
    $possiblePaths = [
        'img/logo.svg',
        'img/logo.png'
    ];
    
    // Check from current directory
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Check from includes directory (when called from includes folder)
    foreach ($possiblePaths as $path) {
        $parentPath = '../' . $path;
        if (file_exists($parentPath)) {
            return $path; // Return relative to root
        }
    }
    
    // Check absolute path as fallback
    $rootPath = dirname(__DIR__);
    foreach ($possiblePaths as $path) {
        $absolutePath = $rootPath . '/' . $path;
        if (file_exists($absolutePath)) {
            return $path;
        }
    }
    
    return null;
}

// Function to check if logo exists
function hasLogo() {
    return getLogoPath() !== null;
}
?>
