<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone setting
date_default_timezone_set('America/New_York');

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'u902379465_fitness',
    'username' => 'u902379465_fitness',
    'password' => '4gS>#RKZV~R',
    'port' => '3306',
    'charset' => 'utf8mb4'
];


// Alternative configurations to try
$alternative_configs = [
    [
        'host' => '127.0.0.1',
        'dbname' => 'pathlab_pro',
        'username' => 'root',
        'password' => '',
        'port' => '3306',
        'charset' => 'utf8mb4'
    ],
    [
        'host' => 'localhost',
        'dbname' => 'pathlab_pro',
        'username' => 'root',
        'password' => 'root',
        'port' => '8889',
        'charset' => 'utf8mb4'
    ]
];

// Global variables
$pdo = null;
$conn = null;
$use_mock_data = false;
$db_connection_error = null;

// Try to establish database connection
function establishDatabaseConnection() {
    global $db_config, $alternative_configs, $pdo, $conn, $use_mock_data, $db_connection_error;
    
    $configs_to_try = array_merge([$db_config], $alternative_configs);
    
    foreach ($configs_to_try as $config) {
        try {
            // Test basic MySQL connection first
            $test_pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']}", 
                $config['username'], 
                $config['password']
            );
            $test_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Try to create database if it doesn't exist
            $test_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}`");
            $test_pdo = null;
            
            // Now connect to the specific database
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}", 
                $config['username'], 
                $config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Also create MySQLi connection for backward compatibility
            $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);
            if ($conn->connect_error) {
                throw new Exception("MySQLi Connection failed: " . $conn->connect_error);
            }
            $conn->set_charset($config['charset']);
            
            // If we get here, connection succeeded
            createTables($pdo);
            return true;
            
        } catch (Exception $e) {
            $db_connection_error = $e->getMessage();
            continue; // Try next configuration
        }
    }
    
    // All connection attempts failed
    $use_mock_data = true;
    return false;
}

// Helper functions for AJAX responses
function jsonResponse($success, $message, $data = null, $extra = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!empty($extra)) {
        $response = array_merge($response, $extra);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get database status for display
function getDatabaseStatus() {
    global $pdo, $conn, $use_mock_data, $db_connection_error;
    
    return [
        'pdo_connected' => ($pdo instanceof PDO),
        'mysqli_connected' => ($conn instanceof mysqli),
        'using_mock_data' => $use_mock_data,
        'error_message' => $db_connection_error
    ];
}

// Try to establish connection when this file is included
establishDatabaseConnection();

// Create all necessary tables
function createTables($pdo) {
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL,
              `email` varchar(255) NOT NULL,
              `password` varchar(255) NOT NULL,
              `first_name` varchar(100) DEFAULT NULL,
              `last_name` varchar(100) DEFAULT NULL,
              `role` enum('admin','doctor','staff') NOT NULL DEFAULT 'staff',
              `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
              `last_login` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `username` (`username`),
              UNIQUE KEY `email` (`email`),
              KEY `idx_status` (`status`),
              KEY `idx_role` (`role`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'patients' => "
            CREATE TABLE IF NOT EXISTS `patients` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `first_name` varchar(100) NOT NULL,
              `last_name` varchar(100) NOT NULL,
              `phone` varchar(20) NOT NULL,
              `email` varchar(255) DEFAULT NULL,
              `date_of_birth` date DEFAULT NULL,
              `gender` enum('male','female','other') DEFAULT NULL,
              `address` text DEFAULT NULL,
              `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `phone_unique` (`phone`),
              KEY `idx_status` (`status`),
              KEY `idx_name` (`first_name`, `last_name`),
              KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'doctors' => "
            CREATE TABLE IF NOT EXISTS `doctors` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `doctor_id` varchar(20) UNIQUE DEFAULT NULL,
              `name` varchar(255) NOT NULL,
              `specialization` varchar(255) NOT NULL,
              `email` varchar(255) DEFAULT NULL,
              `phone` varchar(20) DEFAULT NULL,
              `license_number` varchar(100) DEFAULT NULL,
              `address` text DEFAULT NULL,
              `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_status` (`status`),
              KEY `idx_specialization` (`specialization`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'equipment' => "
            CREATE TABLE IF NOT EXISTS `equipment` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `model` varchar(255) DEFAULT NULL,
              `brand` varchar(255) DEFAULT NULL,
              `category` varchar(100) DEFAULT NULL,
              `serial_number` varchar(100) DEFAULT NULL,
              `purchase_date` date DEFAULT NULL,
              `warranty_expiry` date DEFAULT NULL,
              `last_maintenance` date DEFAULT NULL,
              `next_maintenance` date DEFAULT NULL,
              `status` enum('active','maintenance','inactive','deleted') NOT NULL DEFAULT 'active',
              `location` varchar(255) DEFAULT NULL,
              `notes` text DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_status` (`status`),
              KEY `idx_category` (`category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'test_orders' => "
            CREATE TABLE IF NOT EXISTS `test_orders` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `order_number` varchar(50) UNIQUE NOT NULL,
              `patient_id` int(11) NOT NULL,
              `doctor_id` int(11) DEFAULT NULL,
              `test_type` varchar(255) NOT NULL,
              `test_description` text DEFAULT NULL,
              `priority` enum('normal','urgent','stat') DEFAULT 'normal',
              `status` enum('pending','in_progress','completed','cancelled','deleted') NOT NULL DEFAULT 'pending',
              `ordered_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `sample_collected_date` datetime DEFAULT NULL,
              `expected_completion_date` datetime DEFAULT NULL,
              `notes` text DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL,
              KEY `idx_status` (`status`),
              KEY `idx_ordered_date` (`ordered_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'test_results' => "
            CREATE TABLE IF NOT EXISTS `test_results` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `order_id` int(11) NOT NULL,
              `patient_id` int(11) NOT NULL,
              `test_name` varchar(255) NOT NULL,
              `result_value` text NOT NULL,
              `reference_range` varchar(255) DEFAULT NULL,
              `unit` varchar(50) DEFAULT NULL,
              `status` enum('pending','completed','reviewed','reported','deleted') NOT NULL DEFAULT 'pending',
              `result_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `reviewed_by` int(11) DEFAULT NULL,
              `reviewed_date` datetime DEFAULT NULL,
              `notes` text DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`order_id`) REFERENCES `test_orders`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
              KEY `idx_status` (`status`),
              KEY `idx_result_date` (`result_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        "
    ];
    
    foreach ($tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            error_log("Error creating table $table_name: " . $e->getMessage());
        }
    }
    
    // Insert default admin user if no users exist
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) 
                                  VALUES (?, ?, ?, ?, ?, 'admin', 'active')");
            $stmt->execute(['admin', 'admin@pathlab.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin', 'User']);
        }
    } catch (Exception $e) {
        error_log("Error creating default admin: " . $e->getMessage());
    }
}

// Session management
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(false, 'Authentication required');
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

function requireRole($required_role) {
    requireLogin();
    $user_role = getUserRole();
    
    $role_hierarchy = ['admin' => 3, 'doctor' => 2, 'staff' => 1];
    $required_level = $role_hierarchy[$required_role] ?? 0;
    $user_level = $role_hierarchy[$user_role] ?? 0;
    
    if ($user_level < $required_level) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(false, 'Insufficient permissions');
        } else {
            header('Location: dashboard.php?error=access_denied');
            exit;
        }
    }
}
?>
