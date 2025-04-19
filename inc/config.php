<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u902379465_fitness');
define('DB_PASS', '&m0DCQT!Jn0');
define('DB_NAME', 'u902379465_fitness');

// System Configuration
define('SITE_NAME', 'Pathology CRM');
define('SITE_URL', 'https://usfitnes.com');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
session_start();

// Timezone
date_default_timezone_set('Asia/Kolkata');
?> 