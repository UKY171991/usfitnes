<?php
// Database Setup and Verification Script
require_once 'config_working.php';

echo "<h1>PathLab Pro - Database Setup and Verification</h1>";
echo "<hr>";

// Test database connection
try {
    echo "<h2>1. Database Connection Test</h2>";
    
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "✅ PDO Connection: <strong>SUCCESS</strong><br>";
    } else {
        echo "❌ PDO Connection: <strong>FAILED</strong><br>";
    }
    
    if (isset($conn) && $conn instanceof mysqli) {
        echo "✅ MySQLi Connection: <strong>SUCCESS</strong><br>";
    } else {
        echo "❌ MySQLi Connection: <strong>FAILED</strong><br>";
    }
    
    echo "<hr>";
    
    // Show all tables
    echo "<h2>2. Database Tables</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>No tables found. Creating tables...</p>";
        createTables($pdo);
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table";
        
        // Count records in each table
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo " ($count records)";
        } catch (Exception $e) {
            echo " (error counting records)";
        }
        echo "</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    
    // Insert sample data
    echo "<h2>3. Sample Data Insertion</h2>";
    insertSampleData($pdo);
    
    echo "<hr>";
    
    // Verify CRUD operations
    echo "<h2>4. CRUD Operations Test</h2>";
    testCrudOperations($pdo);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    
    // Suggest using XAMPP/WAMP
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Database Connection Failed</h3>";
    echo "<p>It appears MySQL is not running on your system. Please:</p>";
    echo "<ol>";
    echo "<li>Install XAMPP, WAMP, or MAMP</li>";
    echo "<li>Start Apache and MySQL services</li>";
    echo "<li>Create a database named 'pathlab_pro'</li>";
    echo "<li>Run this script again</li>";
    echo "</ol>";
    echo "</div>";
}

function insertSampleData($pdo) {
    // Insert sample patients
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h3>Inserting Sample Patients...</h3>";
            $patients = [
                ['John', 'Doe', '+1234567890', 'john.doe@email.com', '1985-06-15', 'male'],
                ['Jane', 'Smith', '+1234567891', 'jane.smith@email.com', '1990-03-22', 'female'],
                ['Robert', 'Johnson', '+1234567892', 'robert.j@email.com', '1978-11-08', 'male'],
                ['Mary', 'Williams', '+1234567893', 'mary.w@email.com', '1982-09-12', 'female'],
                ['David', 'Brown', '+1234567894', 'david.brown@email.com', '1975-04-30', 'male']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($patients as $patient) {
                $stmt->execute($patient);
            }
            echo "✅ " . count($patients) . " sample patients inserted<br>";
        } else {
            echo "ℹ️ Patients table already has $count records<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error inserting patients: " . $e->getMessage() . "<br>";
    }
    
    // Insert sample doctors
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM doctors");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h3>Inserting Sample Doctors...</h3>";
            $doctors = [
                ['DR001', 'Dr. Sarah Wilson', 'Cardiology', 'sarah.wilson@hospital.com', '+1555001001', 'MD12345'],
                ['DR002', 'Dr. Michael Chen', 'Neurology', 'michael.chen@hospital.com', '+1555001002', 'MD12346'],
                ['DR003', 'Dr. Emily Davis', 'Pathology', 'emily.davis@hospital.com', '+1555001003', 'MD12347'],
                ['DR004', 'Dr. James Anderson', 'Radiology', 'james.anderson@hospital.com', '+1555001004', 'MD12348']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO doctors (doctor_id, name, specialization, email, phone, license_number) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($doctors as $doctor) {
                $stmt->execute($doctor);
            }
            echo "✅ " . count($doctors) . " sample doctors inserted<br>";
        } else {
            echo "ℹ️ Doctors table already has $count records<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error inserting doctors: " . $e->getMessage() . "<br>";
    }
    
    // Insert sample equipment
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h3>Inserting Sample Equipment...</h3>";
            $equipment = [
                ['X-Ray Machine', 'XR-500', 'Siemens', 'Radiology', 'XR500001', '2020-01-15', '2025-01-15'],
                ['Blood Analyzer', 'BA-3000', 'Abbott', 'Hematology', 'BA3000001', '2021-06-10', '2026-06-10'],
                ['Microscope', 'MS-Pro', 'Olympus', 'Pathology', 'MSP001', '2019-03-20', '2024-03-20'],
                ['Centrifuge', 'CF-400', 'Eppendorf', 'Laboratory', 'CF400001', '2022-02-28', '2027-02-28']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO equipment (name, model, brand, category, serial_number, purchase_date, warranty_expiry) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($equipment as $item) {
                $stmt->execute($item);
            }
            echo "✅ " . count($equipment) . " sample equipment inserted<br>";
        } else {
            echo "ℹ️ Equipment table already has $count records<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error inserting equipment: " . $e->getMessage() . "<br>";
    }
}

function testCrudOperations($pdo) {
    echo "<h3>Testing CRUD Operations...</h3>";
    
    // Test CREATE operation
    try {
        $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, phone, email) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['Test', 'Patient', '+9999999999', 'test@test.com']);
        $test_id = $pdo->lastInsertId();
        echo "✅ CREATE: Successfully inserted test patient (ID: $test_id)<br>";
        
        // Test READ operation
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$test_id]);
        $patient = $stmt->fetch();
        if ($patient) {
            echo "✅ READ: Successfully retrieved patient data<br>";
        } else {
            echo "❌ READ: Failed to retrieve patient data<br>";
        }
        
        // Test UPDATE operation
        $stmt = $pdo->prepare("UPDATE patients SET first_name = ? WHERE id = ?");
        $result = $stmt->execute(['Updated Test', $test_id]);
        if ($result && $stmt->rowCount() > 0) {
            echo "✅ UPDATE: Successfully updated patient data<br>";
        } else {
            echo "❌ UPDATE: Failed to update patient data<br>";
        }
        
        // Test DELETE operation
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $result = $stmt->execute([$test_id]);
        if ($result && $stmt->rowCount() > 0) {
            echo "✅ DELETE: Successfully deleted test patient<br>";
        } else {
            echo "❌ DELETE: Failed to delete test patient<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ CRUD Test Error: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h2>5. Next Steps</h2>";
echo "<p>✅ Database setup complete!</p>";
echo "<p>📝 You can now:</p>";
echo "<ul>";
echo "<li>Access the <a href='login.php'>Login Page</a> (admin/admin123)</li>";
echo "<li>View the <a href='dashboard.php'>Dashboard</a></li>";
echo "<li>Manage <a href='patients.php'>Patients</a></li>";
echo "<li>Manage <a href='doctors.php'>Doctors</a></li>";
echo "<li>Manage <a href='equipment.php'>Equipment</a></li>";
echo "</ul>";
?>
