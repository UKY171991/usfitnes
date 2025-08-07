<?php
require_once 'config.php';

try {
    $conn = getDatabaseConnection();
    
    echo "<h2>Setting Up Database Tables</h2>";
    
    // Create patients table
    $sql = "CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>✓ Patients table created</p>";
    
    // Create doctors table
    $sql = "CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        specialization VARCHAR(100),
        license_number VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>✓ Doctors table created</p>";
    
    // Create test_orders table
    $sql = "CREATE TABLE IF NOT EXISTS test_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(20) UNIQUE,
        patient_id INT,
        doctor_id INT,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id),
        FOREIGN KEY (doctor_id) REFERENCES doctors(id)
    )";
    $conn->exec($sql);
    echo "<p>✓ Test Orders table created</p>";
    
    // Insert sample data
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients");
    $stmt->execute();
    $patientCount = $stmt->fetchColumn();
    
    if ($patientCount == 0) {
        // Insert sample patients
        $patients = [
            ['John Doe', 'john@example.com', '123-456-7890', '1990-05-15', 'male'],
            ['Jane Smith', 'jane@example.com', '098-765-4321', '1985-12-20', 'female'],
            ['Bob Johnson', 'bob@example.com', '555-123-4567', '1978-03-10', 'male'],
            ['Alice Brown', 'alice@example.com', '444-987-6543', '1992-07-25', 'female']
        ];
        
        $stmt = $conn->prepare("INSERT INTO patients (name, email, phone, date_of_birth, gender) VALUES (?, ?, ?, ?, ?)");
        foreach ($patients as $patient) {
            $stmt->execute($patient);
        }
        echo "<p>✓ Sample patients inserted</p>";
    }
    
    // Insert sample doctors
    $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors");
    $stmt->execute();
    $doctorCount = $stmt->fetchColumn();
    
    if ($doctorCount == 0) {
        $doctors = [
            ['Dr. Smith', 'dr.smith@hospital.com', '111-222-3333', 'Cardiology', 'LIC123456'],
            ['Dr. Johnson', 'dr.johnson@hospital.com', '222-333-4444', 'Internal Medicine', 'LIC789012'],
            ['Dr. Brown', 'dr.brown@hospital.com', '333-444-5555', 'Pathology', 'LIC345678']
        ];
        
        $stmt = $conn->prepare("INSERT INTO doctors (name, email, phone, specialization, license_number) VALUES (?, ?, ?, ?, ?)");
        foreach ($doctors as $doctor) {
            $stmt->execute($doctor);
        }
        echo "<p>✓ Sample doctors inserted</p>";
    }
    
    // Insert sample test orders
    $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders");
    $stmt->execute();
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount == 0) {
        $orders = [
            ['ORD-001', 1, 1, 'pending'],
            ['ORD-002', 2, 2, 'completed'],
            ['ORD-003', 3, 1, 'processing'],
            ['ORD-004', 4, 3, 'pending'],
            ['ORD-005', 1, 2, 'completed']
        ];
        
        $stmt = $conn->prepare("INSERT INTO test_orders (order_number, patient_id, doctor_id, status) VALUES (?, ?, ?, ?)");
        foreach ($orders as $order) {
            $stmt->execute($order);
        }
        echo "<p>✓ Sample test orders inserted</p>";
    }
    
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    echo "<p><a href='test_db.php'>Test Database Connection</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
