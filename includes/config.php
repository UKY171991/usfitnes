<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u902379465_fitness');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.use_strict_mode', 1);

// Security settings
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes in seconds

// Application settings
define('SITE_URL', 'http://localhost/usfitnes');
define('SITE_NAME', 'US Fitness Lab Management');
define('ADMIN_EMAIL', 'admin@usfitnes.com');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../uploads');

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Date/Time settings
date_default_timezone_set('UTC');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s'); 