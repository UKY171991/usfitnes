<?php
// Database setup and demo user creation script
require_once 'config.php';

try {
    echo "Setting up database tables and demo user...\n\n";
    
    // Create users table if it doesn't exist
    $createUsersTable = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            user_type ENUM('admin', 'technician', 'doctor', 'user') DEFAULT 'user',
            is_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createUsersTable);
    echo "✓ Users table created/verified\n";
    
    // Create doctors table if it doesn't exist
    $createDoctorsTable = "
        CREATE TABLE IF NOT EXISTS doctors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT UNIQUE NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            specialization VARCHAR(100),
            license_number VARCHAR(50),
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            address TEXT,
            hospital VARCHAR(255),
            referral_percentage DECIMAL(5,2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createDoctorsTable);
    echo "✓ Doctors table created/verified\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Create admin user
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, user_type, email, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'admin',
            $hashedPassword,
            'System Administrator',
            'admin',
            'admin@pathlab.com',
            1
        ]);
        
        echo "✓ Demo admin user created\n";
    } else {
        echo "✓ Demo admin user already exists\n";
    }
    
    // Create sample doctors if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
    $doctorCount = $stmt->fetchColumn();
    
    if ($doctorCount == 0) {
        $sampleDoctors = [
            ['Dr. John', 'Smith', 'Cardiology', 'MD12345', '555-0101', 'john.smith@hospital.com', '123 Medical Plaza', 'City General Hospital', 15.0],
            ['Dr. Sarah', 'Johnson', 'Neurology', 'MD12346', '555-0102', 'sarah.johnson@clinic.com', '456 Health Center', 'Neurological Institute', 12.5],
            ['Dr. Michael', 'Brown', 'Pathology', 'MD12347', '555-0103', 'michael.brown@pathlab.com', '789 Lab Street', 'PathLab Medical Center', 20.0]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO doctors (first_name, last_name, specialization, license_number, phone, email, address, hospital, referral_percentage) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleDoctors as $doctor) {
            $stmt->execute($doctor);
        }
        
        echo "✓ Sample doctors created\n";
    } else {
        echo "✓ Doctors already exist in database\n";
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "Login credentials:\n";
    echo "Username: admin\n";
    echo "Password: password\n";
    echo "\nYou can now access: https://usfitnes.com/login.php\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
