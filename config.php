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
$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '4gS>#RKZV~R';

// Alternative local configuration (uncomment for local development)
// $host = 'localhost';
// $dbname = 'pathlab_pro';
// $username = 'root';
// $password = '';

try {
    // First create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo_temp = null;
    
    // Connect to the database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Also create MySQLi connection for backward compatibility
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("MySQLi Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions for AJAX responses
function jsonResponse($success, $message, $data = null, $extra = []) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s'),
        'server_time' => time()
    ];
    
    // Merge any extra data
    if (!empty($extra) && is_array($extra)) {
        $response = array_merge($response, $extra);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function validateInput($data, $required_fields = []) {
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $field_name = ucfirst(str_replace(['_', '-'], ' ', $field));
            $errors[] = $field_name . " is required";
        }
    }
    
    return $errors;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/\D/', '', $phone);
    // Check if it's a valid length (10-15 digits)
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

function generateUniqueId($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = $prefix;
    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $result;
}

function logActivity($user_id, $action, $details = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '';
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j, Y', strtotime($datetime));
}

// CORS headers for AJAX requests
function setCorsHeaders() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    setCorsHeaders();
}

// Create database tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('admin','lab_technician','receptionist','doctor') DEFAULT 'lab_technician',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(20) NOT NULL UNIQUE,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `medical_history` text,
  `allergies` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `test_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_code` varchar(20) NOT NULL UNIQUE,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text,
  `normal_range` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `duration_hours` int(11) DEFAULT '24',
  `sample_type` varchar(50) DEFAULT 'Blood',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `test_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` varchar(20) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `address` text,
  `hospital` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `test_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `priority` enum('normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `sample_collected_at` timestamp NULL DEFAULT NULL,
  `collected_by` int(11) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `collected_by` (`collected_by`),
  CONSTRAINT `test_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `test_orders_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  CONSTRAINT `test_orders_ibfk_3` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `test_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_order_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `test_order_id` (`test_order_id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `test_order_items_ibfk_1` FOREIGN KEY (`test_order_id`) REFERENCES `test_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_order_items_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_order_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `result_value` text,
  `reference_range` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','verified','abnormal') DEFAULT 'pending',
  `is_critical` tinyint(1) DEFAULT '0',
  `comments` text,
  `tested_by` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `result_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `test_order_id` (`test_order_id`),
  KEY `test_id` (`test_id`),
  KEY `patient_id` (`patient_id`),
  KEY `tested_by` (`tested_by`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`test_order_id`) REFERENCES `test_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  CONSTRAINT `test_results_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `test_results_ibfk_4` FOREIGN KEY (`tested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `test_results_ibfk_5` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_code` varchar(50) NOT NULL UNIQUE,
  `equipment_name` varchar(200) NOT NULL,
  `equipment_type` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('active','inactive','maintenance','broken') DEFAULT 'active',
  `cost` decimal(12,2) DEFAULT NULL,
  `maintenance_schedule` enum('weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipment_code` (`equipment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `equipment_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `maintenance_type` enum('routine','calibration','repair','emergency') NOT NULL,
  `maintenance_date` date NOT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT '0.00',
  `description` text,
  `next_maintenance_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `equipment_id` (`equipment_id`),
  CONSTRAINT `equipment_maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    // Execute all table creation statements
    $statements = array_filter(explode(';', $sql));
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    // Create default admin user if it doesn't exist
    $checkAdmin = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $checkAdmin->execute();
    
    if ($checkAdmin->fetchColumn() == 0) {
        $adminPassword = password_hash('password', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (username, password, email, name, user_type) 
            VALUES ('admin', ?, 'admin@pathlabpro.com', 'System Administrator', 'admin')
        ");
        $insertAdmin->execute([$adminPassword]);
    }
    
    // Insert sample data if tables are empty
    $checkPatients = $pdo->prepare("SELECT COUNT(*) FROM patients");
    $checkPatients->execute();
    
    if ($checkPatients->fetchColumn() == 0) {
        // Insert test categories
        $categories = [
            ['Hematology', 'Blood related tests'],
            ['Biochemistry', 'Chemical analysis of body fluids'],
            ['Microbiology', 'Infectious disease testing'],
            ['Immunology', 'Immune system related tests'],
            ['Pathology', 'Tissue and cellular analysis']
        ];
        
        $insertCategory = $pdo->prepare("INSERT INTO test_categories (category_name, description) VALUES (?, ?)");
        foreach ($categories as $category) {
            $insertCategory->execute($category);
        }
        
        // Insert sample tests
        $tests = [
            ['CBC001', 'Complete Blood Count', 1, 'Full blood analysis including RBC, WBC, platelets', '4.5-5.5 million/μL', 'cells/μL', 25.00],
            ['GLU001', 'Fasting Blood Glucose', 2, 'Blood sugar level measurement', '70-100 mg/dL', 'mg/dL', 15.00],
            ['LIP001', 'Lipid Profile', 2, 'Cholesterol and triglycerides analysis', 'Total: <200 mg/dL', 'mg/dL', 35.00],
            ['HBA1C', 'HbA1c', 2, 'Average blood glucose over 3 months', '<5.7%', '%', 40.00],
            ['URI001', 'Urine Analysis', 2, 'Complete urine examination', 'Normal', 'Various', 20.00]
        ];
        
        $insertTest = $pdo->prepare("
            INSERT INTO tests (test_code, name, category_id, description, normal_range, unit, price) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($tests as $test) {
            $insertTest->execute($test);
        }
        
        // Insert sample patients
        $patients = [
            ['PAT001', 'John', 'Smith', '1985-06-15', 'male', '123-456-7890', 'john.smith@email.com', '123 Main St', 'Jane Smith', '123-456-7891', 'O+'],
            ['PAT002', 'Jane', 'Johnson', '1990-12-03', 'female', '987-654-3210', 'jane.j@email.com', '456 Oak Ave', 'Bob Johnson', '987-654-3211', 'A+'],
            ['PAT003', 'Mike', 'Brown', '1978-09-22', 'male', '555-123-4567', 'mike.brown@email.com', '789 Pine Rd', 'Sarah Brown', '555-123-4568', 'B+'],
            ['PAT004', 'Sarah', 'Wilson', '1988-03-18', 'female', '444-555-6666', 'sarah.w@email.com', '321 Elm St', 'David Wilson', '444-555-6667', 'AB+']
        ];
        
        $insertPatient = $pdo->prepare("
            INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact, emergency_phone, blood_group) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($patients as $patient) {
            $insertPatient->execute($patient);
        }
        
        // Insert sample doctors
        $doctors = [
            ['DOC001', 'Dr. Robert Anderson', 'dr.anderson@hospital.com', '111-222-3333', 'Cardiology', 'MD12345'],
            ['DOC002', 'Dr. Emily Brown', 'dr.brown@clinic.com', '444-555-6666', 'Internal Medicine', 'MD67890'],
            ['DOC003', 'Dr. David Martinez', 'dr.martinez@medical.com', '777-888-9999', 'Pathology', 'MD11111']
        ];
        
        $insertDoctor = $pdo->prepare("
            INSERT INTO doctors (doctor_id, name, email, phone, specialization, license_number) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($doctors as $doctor) {
            $insertDoctor->execute($doctor);
        }
        
        echo "PathLab Pro local database setup completed successfully!";
    }
    
} catch(PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}

return $pdo;
?>
