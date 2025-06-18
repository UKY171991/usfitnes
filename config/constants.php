<?php
/**
 * Application Constants
 * Configuration constants for US Fitness Lab following project instructions
 */

// Environment Detection
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Base URL Configuration (as per instructions)
define('BASE_URL', $is_local ? 'http://localhost/usfitnes/' : 'https://yourdomain.com/usfitnes/');

// Define paths (as per instructions)
define('REPORT_PATH', __DIR__ . '/../reports/');
define('LOG_PATH', __DIR__ . '/../logs/');

// AJAX URL (as per instructions)
define('AJAX_URL', BASE_URL . 'ajax.php');

// Application settings
define('APP_NAME', 'US Fitness Lab');
define('APP_VERSION', '1.0.0');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__) . '/');
define('SRC_PATH', ROOT_PATH . 'src/');
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');
define('ASSET_PATH', ROOT_PATH . 'assets/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');

// User roles (as per instructions)
define('USER_ROLE_MASTER_ADMIN', 'master_admin');
define('USER_ROLE_BRANCH_ADMIN', 'branch_admin');
define('USER_ROLE_PATIENT', 'patient');

// User status
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_PENDING', 'pending');

// Booking status (as per instructions database schema)
define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_CONFIRMED', 'confirmed');
define('BOOKING_STATUS_COMPLETED', 'completed');
define('BOOKING_STATUS_CANCELLED', 'cancelled');

// Payment status (as per instructions database schema)
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_COMPLETED', 'completed');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Report status (as per instructions database schema)
define('REPORT_STATUS_PENDING', 'pending');
define('REPORT_STATUS_PROCESSING', 'processing');
define('REPORT_STATUS_READY', 'ready');
define('REPORT_STATUS_DELIVERED', 'delivered');

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Pagination Settings
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 30 * 24 * 3600); // 30 days
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900); // 15 minutes

// System Settings
define('TIMEZONE', 'Asia/Kolkata');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');
define('DISPLAY_DATETIME_FORMAT', 'd/m/Y h:i A');

// Currency (as per instructions)
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');
define('GST_PERCENTAGE', 18);

// Email Configuration
define('EMAIL_FROM_ADDRESS', 'noreply@usfitness.com');
define('EMAIL_FROM_NAME', 'US Fitness Lab');
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Report Settings
define('REPORT_LOGO_PATH', ASSET_PATH . 'images/logo.png');
define('REPORT_SIGNATURE_PATH', ASSET_PATH . 'images/signature.png');
define('REPORT_WATERMARK', 'CONFIDENTIAL');

// Logging levels
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_DEBUG', 'DEBUG');

// Environment detection
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $is_local ? 'development' : 'production');
}

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}

// Timezone
date_default_timezone_set(TIMEZONE);

// Test categories (can be moved to database later)
define('TEST_CATEGORIES', [
    'blood' => 'Blood Tests',
    'urine' => 'Urine Tests',
    'stool' => 'Stool Tests',
    'imaging' => 'Imaging Tests',
    'cardiac' => 'Cardiac Tests',
    'hormone' => 'Hormone Tests',
    'cancer' => 'Cancer Screening',
    'diabetes' => 'Diabetes Tests',
    'liver' => 'Liver Function Tests',
    'kidney' => 'Kidney Function Tests'
]);

// Default test parameters
define('DEFAULT_TEST_PARAMETERS', [
    'specimen' => ['Blood', 'Urine', 'Stool', 'Saliva', 'Tissue'],
    'methods' => ['ELISA', 'PCR', 'Microscopy', 'Culture', 'Biochemical'],
    'units' => ['mg/dL', 'g/dL', 'mIU/mL', 'ng/mL', 'μg/mL', 'IU/L', 'U/L', '%']
]);

// Legacy constants for backward compatibility
define('ROLE_MASTER_ADMIN', USER_ROLE_MASTER_ADMIN);
define('ROLE_ADMIN', USER_ROLE_MASTER_ADMIN);
define('ROLE_BRANCH_ADMIN', USER_ROLE_BRANCH_ADMIN);
define('ROLE_PATIENT', USER_ROLE_PATIENT);
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('BOOKING_PENDING', BOOKING_STATUS_PENDING);
define('BOOKING_CONFIRMED', BOOKING_STATUS_CONFIRMED);
define('BOOKING_COMPLETED', BOOKING_STATUS_COMPLETED);
define('BOOKING_CANCELLED', BOOKING_STATUS_CANCELLED);
define('PAYMENT_PENDING', PAYMENT_STATUS_PENDING);
define('PAYMENT_PAID', PAYMENT_STATUS_COMPLETED);
define('PAYMENT_FAILED', PAYMENT_STATUS_FAILED);
define('PAYMENT_REFUNDED', PAYMENT_STATUS_REFUNDED);
define('REPORT_PENDING', REPORT_STATUS_PENDING);

// Report additional statuses
define('REPORT_IN_PROGRESS', 'in_progress');
define('REPORT_COMPLETED', 'completed');
define('REPORT_VERIFIED', 'verified');

// Error Messages
define('ERROR_UNAUTHORIZED', 'Unauthorized access');
define('ERROR_INVALID_INPUT', 'Invalid input provided');
define('ERROR_DATABASE', 'Database error occurred');
define('ERROR_FILE_UPLOAD', 'File upload failed');
define('ERROR_PAYMENT', 'Payment processing failed');

// Success Messages
define('SUCCESS_CREATED', 'Record created successfully');
define('SUCCESS_UPDATED', 'Record updated successfully');
define('SUCCESS_DELETED', 'Record deleted successfully');
define('SUCCESS_PAYMENT', 'Payment completed successfully');

// Environment Settings
if ($is_local) {
    define('DEBUG_MODE', true);
    define('LOG_LEVEL', LOG_LEVEL_DEBUG);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    define('DEBUG_MODE', false);
    define('LOG_LEVEL', LOG_LEVEL_ERROR);
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set Timezone
date_default_timezone_set(TIMEZONE);
?>
