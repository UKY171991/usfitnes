<?php
/**
 * Application Constants
 * Global configuration settings
 */

// Environment Detection
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Base URL Configuration
define('BASE_URL', $is_local ? 'http://localhost/usfitnes/' : 'https://usfitnes.com/');
define('SITE_NAME', 'Pathology Lab Management System');
define('SITE_VERSION', '1.0.0');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__) . '/');
define('SRC_PATH', ROOT_PATH . 'src/');
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');
define('ASSET_PATH', ROOT_PATH . 'assets/');
define('CONFIG_PATH', ROOT_PATH . 'config/');

// Upload and Storage Paths
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');
define('REPORT_PATH', ROOT_PATH . 'reports/');
define('LOG_PATH', ROOT_PATH . 'logs/');

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Pagination Settings
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Email Configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@pathlabs.com');
define('FROM_NAME', 'Pathology Lab');

// Report Settings
define('REPORT_LOGO_PATH', ASSET_PATH . 'images/logo.png');
define('REPORT_SIGNATURE_PATH', ASSET_PATH . 'images/signature.png');
define('REPORT_WATERMARK', 'CONFIDENTIAL');

// Payment Settings
define('CURRENCY', 'INR');
define('CURRENCY_SYMBOL', '₹');
define('GST_PERCENTAGE', 18);

// System Settings
define('TIMEZONE', 'Asia/Kolkata');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');
define('DISPLAY_DATETIME_FORMAT', 'd/m/Y h:i A');

// Log Levels
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_DEBUG', 'DEBUG');

// User Roles
define('ROLE_MASTER_ADMIN', 'master_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_BRANCH_ADMIN', 'branch_admin');
define('ROLE_PATIENT', 'patient');
define('ROLE_TECHNICIAN', 'technician');
define('ROLE_RECEPTIONIST', 'receptionist');

// Status Constants
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);

// Booking Status
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_SAMPLE_COLLECTED', 'sample_collected');
define('BOOKING_PROCESSING', 'processing');
define('BOOKING_COMPLETED', 'completed');
define('BOOKING_CANCELLED', 'cancelled');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Report Status
define('REPORT_PENDING', 'pending');
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
