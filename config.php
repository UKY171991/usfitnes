<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '^V$J12k*D5y';

try {
    // First create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo_temp = null;
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create database tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('admin', 'lab_technician', 'doctor', 'receptionist') DEFAULT 'lab_technician',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    license_number VARCHAR(50),
    hospital_affiliation VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS test_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_code VARCHAR(20) UNIQUE NOT NULL,
    test_name VARCHAR(100) NOT NULL,
    category_id INT,
    description TEXT,
    sample_type ENUM('Blood', 'Urine', 'Stool', 'Sputum', 'Tissue', 'Other') NOT NULL,
    normal_range TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_hours INT DEFAULT 24,
    instructions TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES test_categories(id)
);

CREATE TABLE IF NOT EXISTS test_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT,
    order_date DATETIME NOT NULL,
    priority ENUM('Normal', 'Urgent', 'STAT') DEFAULT 'Normal',
    status ENUM('Pending', 'Sample_Collected', 'In_Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    total_amount DECIMAL(10,2),
    payment_status ENUM('Pending', 'Paid', 'Partial') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

CREATE TABLE IF NOT EXISTS test_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    test_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES test_orders(id),
    FOREIGN KEY (test_id) REFERENCES tests(id)
);

CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    parameter_name VARCHAR(100) NOT NULL,
    result_value VARCHAR(255),
    normal_range VARCHAR(100),
    unit VARCHAR(20),
    flag ENUM('Normal', 'High', 'Low', 'Critical') DEFAULT 'Normal',
    tested_by INT,
    tested_date DATETIME,
    verified_by INT,
    verified_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_item_id) REFERENCES test_order_items(id),
    FOREIGN KEY (tested_by) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    serial_number VARCHAR(100),
    category VARCHAR(100),
    purchase_date DATE,
    warranty_expiry DATE,
    status ENUM('Working', 'Maintenance', 'Out_of_Order') DEFAULT 'Working',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS equipment_maintenance (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    maintenance_type VARCHAR(100) NOT NULL,
    maintenance_date DATE NOT NULL,
    performed_by VARCHAR(100),
    cost DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id)
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    report_path VARCHAR(255),
    generated_by INT,
    generated_date DATETIME,
    report_status ENUM('Draft', 'Final', 'Delivered') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES test_orders(id),
    FOREIGN KEY (generated_by) REFERENCES users(id)
);
";

try {
    $pdo->exec($sql);
    
    // Insert default admin user if not exists
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
            ['Pathology', 'Tissue and cellular examination']
        ];
        
        $insertCategory = $pdo->prepare("INSERT INTO test_categories (category_name, description) VALUES (?, ?)");
        foreach ($categories as $category) {
            $insertCategory->execute($category);
        }
        
        // Insert sample tests
        $tests = [
            ['CBC001', 'Complete Blood Count', 1, 'Blood', '4.5-5.5 million cells/mcL', 150.00],
            ['LFT001', 'Liver Function Test', 2, 'Blood', 'ALT: 7-35 U/L', 300.00],
            ['GLUC01', 'Blood Glucose Fasting', 2, 'Blood', '70-100 mg/dL', 80.00],
            ['URIN01', 'Urine Routine', 2, 'Urine', 'Clear, Yellow', 100.00],
            ['CULT01', 'Blood Culture', 3, 'Blood', 'No Growth', 500.00]
        ];
        
        $insertTest = $pdo->prepare("
            INSERT INTO tests (test_code, test_name, category_id, sample_type, normal_range, price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($tests as $test) {
            $insertTest->execute($test);
        }
        
        // Insert sample patients
        $patients = [
            ['PAT001', 'John Doe', 'John', 'Doe', 'john.doe@email.com', '123-456-7890', '123 Main St', '1985-05-15', 'Male'],
            ['PAT002', 'Jane Smith', 'Jane', 'Smith', 'jane.smith@email.com', '098-765-4321', '456 Oak Ave', '1990-08-22', 'Female'],
            ['PAT003', 'Michael Johnson', 'Michael', 'Johnson', 'michael.j@email.com', '555-123-4567', '789 Pine Rd', '1975-12-10', 'Male'],
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
        
        echo "Pathology Lab database setup completed successfully!";
    }
    
} catch(PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}

return $pdo;
?>
