<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u902379465_fitness');
define('DB_USER', 'u902379465_fitness');
define('DB_PASS', 'yI5$I$zdC>R');

// Application Configuration
define('APP_NAME', 'Shiva Pathology Centre');
define('APP_URL', 'http://localhost/usfitnes'); // Update this with your actual URL
define('APP_TIMEZONE', 'Asia/Kolkata');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'USFITNESS_SESSION');

// Security Configuration
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Set timezone
date_default_timezone_set(APP_TIMEZONE); 