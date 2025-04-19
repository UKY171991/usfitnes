<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pathology_crm');

// System Configuration
define('SITE_NAME', 'Pathology CRM');
define('SITE_URL', 'http://localhost/pathology-crm');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
session_start();

// Timezone
date_default_timezone_set('Asia/Kolkata');
?> 