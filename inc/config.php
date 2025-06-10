<?php
// Environment Detection
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Database Configuration based on environment
if ($is_local) {
    // Local Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'fitness');
} else {
    // Live Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u902379465_fitness');
    define('DB_PASS', '&m0DCQT!Jn0');
    define('DB_NAME', 'u902379465_fitness');
}

// System Configuration
define('SITE_NAME', 'Pathology CRM');
define('SITE_URL', $is_local ? 'http://localhost/usfitnes' : 'https://usfitnes.com');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/'); 

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', $is_local ? 1 : 0);

// Session Configuration - Only start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>