<?php
// Database Configuration
// define('DB_HOST', 'localhost');  // Using IP instead of localhost
// define('DB_NAME', 'u902379465_fitness');
// define('DB_USER', 'u902379465_fitness');
// define('DB_PASS', 'n@2Vb3*D');


define('DB_HOST', 'localhost');
define('DB_NAME', 'usfitness');
define('DB_USER', 'root');
define('DB_PASS', '');

// Define DSN for database connection
// Format: mysql:host=DB_HOST;dbname=DB_NAME
define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

// Application Configuration
define('APP_NAME', 'Shiva Pathology Centre');
define('APP_URL', 'https://usfitnes.com/'); // Update this with your actual URL
define('APP_TIMEZONE', 'Asia/Kolkata');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'USFITNESS_SESSION');

// Security Configuration
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// Error Reporting - Disable in production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);