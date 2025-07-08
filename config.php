<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Local Database configuration for development
// $host = 'localhost';
// $dbname = 'pathlab_pro';
// $username = 'root';
// $password = '';

$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '!f#gGC^VKs0';

try {
    // First create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo_temp = null;
    
    // Connect to the database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Also create MySQLi connection for backward compatibility
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("MySQLi Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create database tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` enum('admin','lab_technician','receptionist','doctor') DEFAULT 'lab_technician',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(20) NOT NULL UNIQUE,
  `full_name` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `test_name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
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
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `address` text,
  `hospital` varchar(200) DEFAULT NULL,
  `referral_percentage` decimal(5,2) DEFAULT '0.00',
  `status` enum('active','inactive') DEFAULT 'active',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `test_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL UNIQUE,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `order_date` date NOT NULL,
  `priority` enum('Normal','Urgent','STAT') DEFAULT 'Normal',
  `status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `test_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `test_orders_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `test_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `test_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `test_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_order_items_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `result_values` text NOT NULL,
  `reference_range` varchar(100) DEFAULT NULL,
  `status` enum('Normal','Abnormal','Critical') DEFAULT 'Normal',
  `is_critical` tinyint(1) DEFAULT '0',
  `notes` text,
  `technician_id` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `test_id` (`test_id`),
  KEY `technician_id` (`technician_id`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `test_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  CONSTRAINT `test_results_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  CONSTRAINT `test_results_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment` (
  `equipment_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(200) NOT NULL,
  `equipment_type` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('Working','Under Maintenance','Out of Order','Retired') DEFAULT 'Working',
  `cost` decimal(12,2) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_maintenance` (
  `maintenance_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `maintenance_type` enum('Routine','Calibration','Repair','Emergency') NOT NULL,
  `maintenance_date` date NOT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maintenance_id`),
  KEY `equipment_id` (`equipment_id`),
  CONSTRAINT `equipment_maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
            INSERT INTO users (username, password, email, full_name, user_type) 
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
            INSERT INTO tests (test_code, test_name, category_id, description, normal_range, unit, price) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($tests as $test) {
            $insertTest->execute($test);
        }
        
        // Insert sample patients
        $patients = [
            ['PAT001', 'John Smith', 'John', 'Smith', 'john.smith@email.com', '123-456-7890', '123 Main St', '1985-06-15', 'Male'],
            ['PAT002', 'Jane Johnson', 'Jane', 'Johnson', 'jane.j@email.com', '987-654-3210', '456 Oak Ave', '1990-12-03', 'Female'],
            ['PAT003', 'Mike Brown', 'Mike', 'Brown', 'mike.brown@email.com', '555-123-4567', '789 Pine Rd', '1978-09-22', 'Male'],
            ['PAT004', 'Sarah Wilson', 'Sarah', 'Wilson', 'sarah.w@email.com', '444-555-6666', '321 Elm St', '1988-03-18', 'Female']
        ];
        
        $insertPatient = $pdo->prepare("
            INSERT INTO patients (patient_id, full_name, first_name, last_name, email, phone, address, date_of_birth, gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($patients as $patient) {
            $insertPatient->execute($patient);
        }
        
        // Insert sample doctors
        $doctors = [
            ['DOC001', 'Dr. Robert', 'Anderson', 'dr.anderson@hospital.com', '111-222-3333', 'Cardiology', 'MD12345'],
            ['DOC002', 'Dr. Emily', 'Brown', 'dr.brown@clinic.com', '444-555-6666', 'Internal Medicine', 'MD67890'],
            ['DOC003', 'Dr. David', 'Martinez', 'dr.martinez@medical.com', '777-888-9999', 'Pathology', 'MD11111']
        ];
        
        $insertDoctor = $pdo->prepare("
            INSERT INTO doctors (doctor_id, first_name, last_name, email, phone, specialization, license_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
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
