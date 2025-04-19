<?php
// Environment Configuration
define('ENVIRONMENT', 'development'); // development, testing, production

// Database Configuration
// define('DB_HOST', 'localhost');  // Using IP instead of localhost
// define('DB_NAME', 'u902379465_fitness');
// define('DB_USER', 'u902379465_fitness');
// define('DB_PASS', 'n@2Vb3*D');


define('DB_HOST', 'localhost');
define('DB_NAME', 'u902379465_fitness');
define('DB_USER', 'u902379465_fitness');
define('DB_PASS', 'c55]lGVc6P#r');

// Define DSN for database connection
// Format: mysql:host=DB_HOST;dbname=DB_NAME
define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

// Application Configuration
define('APP_NAME', 'Shiva Pathology Centre');
define('APP_URL', 'https://usfitnes.com/'); // Update this with your actual URL
define('APP_TIMEZONE', 'Asia/Kolkata');
define('APP_VERSION', '1.0.0');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'USFITNESS_SESSION');
define('SESSION_REGEN_TIME', 300); // 5 minutes

// Security Configuration
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// File Upload Configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@usfitnes.com');

// Error Reporting Configuration
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Logging Configuration
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');
define('LOG_LEVEL', ENVIRONMENT === 'production' ? 'ERROR' : 'DEBUG');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', __DIR__ . '/cache/');

// API Configuration
define('API_RATE_LIMIT', 100); // requests per hour
define('API_KEY_LIFETIME', 86400); // 24 hours

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Create required directories if they don't exist
$directories = ['logs', 'uploads', 'cache'];
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
}

// Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; img-src \'self\' data:; font-src \'self\' https://cdn.jsdelivr.net;');