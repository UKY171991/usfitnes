<?php
require_once 'config.php';

echo "Database Migration Script<br><br>";

try {
    // Drop and recreate tables with correct structure
    echo "Dropping existing tables...<br>";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS test_results");
    $pdo->exec("DROP TABLE IF EXISTS test_order_items");
    $pdo->exec("DROP TABLE IF EXISTS test_orders");
    $pdo->exec("DROP TABLE IF EXISTS equipment_maintenance");
    $pdo->exec("DROP TABLE IF EXISTS equipment");
    $pdo->exec("DROP TABLE IF EXISTS tests");
    $pdo->exec("DROP TABLE IF EXISTS test_categories");
    $pdo->exec("DROP TABLE IF EXISTS doctors");
    $pdo->exec("DROP TABLE IF EXISTS patients");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Creating new tables...<br>";
    
    // Create users table
    $pdo->exec("
    CREATE TABLE users (
      id int(11) NOT NULL AUTO_INCREMENT,
      username varchar(50) NOT NULL UNIQUE,
      password varchar(255) NOT NULL,
      email varchar(100) NOT NULL,
      name varchar(100) NOT NULL,
      phone varchar(20) DEFAULT NULL,
      user_type enum('admin','lab_technician','receptionist','doctor') DEFAULT 'lab_technician',
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    // Create patients table
    $pdo->exec("
    CREATE TABLE patients (
      id int(11) NOT NULL AUTO_INCREMENT,
      patient_id varchar(20) NOT NULL UNIQUE,
      name varchar(100) NOT NULL,
      date_of_birth date DEFAULT NULL,
      gender enum('male','female','other') DEFAULT NULL,
      phone varchar(20) NOT NULL,
      email varchar(100) DEFAULT NULL,
      address text,
      emergency_contact varchar(100) DEFAULT NULL,
      medical_history text,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    // Create doctors table
    $pdo->exec("
    CREATE TABLE doctors (
      id int(11) NOT NULL AUTO_INCREMENT,
      doctor_id varchar(20) NOT NULL UNIQUE,
      name varchar(100) NOT NULL,
      email varchar(100) DEFAULT NULL,
      phone varchar(20) NOT NULL,
      specialization varchar(100) NOT NULL,
      license_number varchar(50) DEFAULT NULL,
      address text,
      hospital varchar(200) DEFAULT NULL,
      status enum('active','inactive') DEFAULT 'active',
      notes text,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    // Create test_categories table
    $pdo->exec("
    CREATE TABLE test_categories (
      id int(11) NOT NULL AUTO_INCREMENT,
      category_name varchar(100) NOT NULL,
      description text,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    // Create tests table
    $pdo->exec("
    CREATE TABLE tests (
      id int(11) NOT NULL AUTO_INCREMENT,
      test_code varchar(20) NOT NULL UNIQUE,
      name varchar(200) NOT NULL,
      category_id int(11) NOT NULL,
      description text,
      normal_range varchar(100) DEFAULT NULL,
      unit varchar(50) DEFAULT NULL,
      price decimal(10,2) NOT NULL DEFAULT '0.00',
      duration_hours int(11) DEFAULT '24',
      sample_type varchar(50) DEFAULT 'Blood',
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY category_id (category_id),
      CONSTRAINT tests_ibfk_1 FOREIGN KEY (category_id) REFERENCES test_categories (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    // Create test_orders table
    $pdo->exec("
    CREATE TABLE test_orders (
      id int(11) NOT NULL AUTO_INCREMENT,
      patient_id int(11) NOT NULL,
      test_id int(11) NOT NULL,
      doctor_id int(11) DEFAULT NULL,
      priority enum('normal','high','urgent') DEFAULT 'normal',
      status enum('pending','processing','completed','cancelled') DEFAULT 'pending',
      notes text,
      order_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY patient_id (patient_id),
      KEY test_id (test_id),
      KEY doctor_id (doctor_id),
      CONSTRAINT test_orders_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patients (id),
      CONSTRAINT test_orders_ibfk_2 FOREIGN KEY (test_id) REFERENCES tests (id),
      CONSTRAINT test_orders_ibfk_3 FOREIGN KEY (doctor_id) REFERENCES doctors (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    
    echo "Tables created successfully!<br><br>";
    
    // Insert default admin user
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, name, user_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'admin@pathlabpro.com', 'System Administrator', 'admin']);
    echo "Default admin user created (username: admin, password: password)<br>";
    
    // Insert sample test categories
    $categories = [
        ['Hematology', 'Blood related tests'],
        ['Biochemistry', 'Chemical analysis of body fluids'],
        ['Microbiology', 'Infectious disease testing'],
        ['Immunology', 'Immune system related tests'],
        ['Pathology', 'Tissue and cellular analysis']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO test_categories (category_name, description) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "Sample test categories added<br>";
    
    // Insert sample tests
    $tests = [
        ['CBC001', 'Complete Blood Count', 1, 'Full blood analysis including RBC, WBC, platelets', '4.5-5.5 million/μL', 'cells/μL', 25.00],
        ['GLU001', 'Fasting Blood Glucose', 2, 'Blood sugar level measurement', '70-100 mg/dL', 'mg/dL', 15.00],
        ['LIP001', 'Lipid Profile', 2, 'Cholesterol and triglycerides analysis', 'Total: <200 mg/dL', 'mg/dL', 35.00],
        ['HBA1C', 'HbA1c', 2, 'Average blood glucose over 3 months', '<5.7%', '%', 40.00],
        ['URI001', 'Urine Analysis', 2, 'Complete urine examination', 'Normal', 'Various', 20.00]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO tests (test_code, name, category_id, description, normal_range, unit, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($tests as $test) {
        $stmt->execute($test);
    }
    echo "Sample tests added<br>";
    
    // Insert sample patients
    $patients = [
        ['PAT001', 'John Smith', 'john.smith@email.com', '123-456-7890', '123 Main St', '1985-06-15', 'male'],
        ['PAT002', 'Jane Johnson', 'jane.j@email.com', '987-654-3210', '456 Oak Ave', '1990-12-03', 'female'],
        ['PAT003', 'Mike Brown', 'mike.brown@email.com', '555-123-4567', '789 Pine Rd', '1978-09-22', 'male'],
        ['PAT004', 'Sarah Wilson', 'sarah.w@email.com', '444-555-6666', '321 Elm St', '1988-03-18', 'female']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO patients (patient_id, name, email, phone, address, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($patients as $patient) {
        $stmt->execute($patient);
    }
    echo "Sample patients added<br>";
    
    // Insert sample doctors
    $doctors = [
        ['DOC001', 'Dr. Robert Anderson', 'dr.anderson@hospital.com', '111-222-3333', 'Cardiology', 'MD12345'],
        ['DOC002', 'Dr. Emily Brown', 'dr.brown@clinic.com', '444-555-6666', 'Internal Medicine', 'MD67890'],
        ['DOC003', 'Dr. David Martinez', 'dr.martinez@medical.com', '777-888-9999', 'Pathology', 'MD11111']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO doctors (doctor_id, name, email, phone, specialization, license_number) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($doctors as $doctor) {
        $stmt->execute($doctor);
    }
    echo "Sample doctors added<br>";
    
    echo "<br><strong>Migration completed successfully!</strong><br>";
    echo "You can now login with username: admin, password: password<br>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
