<?php
// Database Setup and Verification Script
require_once 'config_working.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>PathLab Pro - Database Setup</title>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'>";
echo "<style>
    .container { margin-top: 20px; }
    .status-success { color: #28a745; }
    .status-failed { color: #dc3545; }
    .status-info { color: #17a2b8; }
    .setup-card { margin-bottom: 20px; }
    .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; border-radius: 5px; }
</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h1 class='text-center mb-4'>PathLab Pro - Database Setup and Verification</h1>";

// Get database status
$status = getDatabaseStatus();

// Test database connection
echo "<div class='card setup-card'>";
echo "<div class='card-header'><h2>1. Database Connection Test</h2></div>";
echo "<div class='card-body'>";

if ($status['pdo_connected']) {
    echo "<p class='status-success'>✅ PDO Connection: <strong>SUCCESS</strong></p>";
} else {
    echo "<p class='status-failed'>❌ PDO Connection: <strong>FAILED</strong></p>";
}

if ($status['mysqli_connected']) {
    echo "<p class='status-success'>✅ MySQLi Connection: <strong>SUCCESS</strong></p>";
} else {
    echo "<p class='status-failed'>❌ MySQLi Connection: <strong>FAILED</strong></p>";
}

if ($status['using_mock_data']) {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Database Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . ($status['error_message'] ?: 'Unknown database error') . "</p>";
    echo "<p>The system is currently using mock data for development.</p>";
    echo "</div>";
    
    echo "<div class='alert alert-info'>";
    echo "<h5>📋 Setup Instructions</h5>";
    echo "<p>To fix the database connection, please follow these steps:</p>";
    echo "<ol>";
    echo "<li><strong>Install a local MySQL server:</strong>";
    echo "<ul>";
    echo "<li><a href='https://www.apachefriends.org/download.html' target='_blank'>XAMPP (Recommended)</a></li>";
    echo "<li><a href='http://www.wampserver.com/en/' target='_blank'>WAMP Server</a></li>";
    echo "<li><a href='https://www.mamp.info/en/downloads/' target='_blank'>MAMP</a></li>";
    echo "</ul></li>";
    echo "<li><strong>Start MySQL service</strong> in your chosen software</li>";
    echo "<li><strong>Create database:</strong> Open phpMyAdmin and create a database named <code>pathlab_pro</code></li>";
    echo "<li><strong>Refresh this page</strong> - the system will automatically create tables</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='alert alert-success'>";
    echo "✅ Database connection established successfully!";
    echo "</div>";
}

echo "</div></div>";

// Only show tables if connection is successful
if (!$status['using_mock_data'] && $pdo) {
    try {
        // Show all tables
        echo "<div class='card setup-card'>";
        echo "<div class='card-header'><h2>2. Database Tables</h2></div>";
        echo "<div class='card-body'>";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p class='status-info'>No tables found. Creating tables...</p>";
            createTables($pdo);
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        }
        
        echo "<div class='row'>";
        foreach ($tables as $table) {
            echo "<div class='col-md-4 mb-3'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h6 class='card-title'>📋 $table</h6>";
            
            // Count records in each table
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "<p class='card-text'>Records: <span class='badge badge-primary'>$count</span></p>";
            } catch (Exception $e) {
                echo "<p class='card-text'><span class='badge badge-warning'>Error counting</span></p>";
            }
            echo "</div></div></div>";
        }
        echo "</div>";
        echo "</div></div>";
        
        // Insert sample data
        echo "<div class='card setup-card'>";
        echo "<div class='card-header'><h3>3. Sample Data Insertion</h3></div>";
        echo "<div class='card-body'>";
        insertSampleData($pdo);
        echo "</div></div>";
        
        // Verify CRUD operations
        echo "<div class='card setup-card'>";
        echo "<div class='card-header'><h4>4. CRUD Operations Test</h4></div>";
        echo "<div class='card-body'>";
        testCrudOperations($pdo);
        echo "</div></div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<h4>Database Error</h4>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Show next steps
echo "<div class='card setup-card'>";
echo "<div class='card-header bg-success text-white'><h2>5. Next Steps</h2></div>";
echo "<div class='card-body'>";

if (!$status['using_mock_data']) {
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Setup Complete!</h4>";
    echo "<p>Your PathLab Pro system is ready to use.</p>";
    echo "</div>";
    
    echo "<h5>Quick Links:</h5>";
    echo "<div class='row'>";
    echo "<div class='col-md-3'><a href='login.php' class='btn btn-primary btn-block'>🔐 Login Page</a></div>";
    echo "<div class='col-md-3'><a href='dashboard.php' class='btn btn-info btn-block'>📊 Dashboard</a></div>";
    echo "<div class='col-md-3'><a href='patients_clean.php' class='btn btn-success btn-block'>👥 Patients</a></div>";
    echo "<div class='col-md-3'><a href='doctors.php' class='btn btn-warning btn-block'>👨‍⚕️ Doctors</a></div>";
    echo "</div>";
    echo "<br>";
    echo "<div class='row'>";
    echo "<div class='col-md-3'><a href='equipment.php' class='btn btn-secondary btn-block'>🔬 Equipment</a></div>";
    echo "<div class='col-md-3'><a href='test-orders.php' class='btn btn-dark btn-block'>📋 Test Orders</a></div>";
    echo "<div class='col-md-3'><a href='results.php' class='btn btn-info btn-block'>📊 Results</a></div>";
    echo "<div class='col-md-3'><a href='reports.php' class='btn btn-primary btn-block'>📈 Reports</a></div>";
    echo "</div>";
    
    echo "<div class='mt-4'>";
    echo "<h5>Default Login Credentials:</h5>";
    echo "<div class='code-block'>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Setup Required</h4>";
    echo "<p>Please install and configure MySQL before using the system.</p>";
    echo "</div>";
}

echo "</div></div>";

echo "</div>"; // Close container
echo "</body></html>";

function insertSampleData($pdo) {
    // Insert sample patients
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h5>👥 Inserting Sample Patients...</h5>";
            $patients = [
                ['John', 'Doe', '+1234567890', 'john.doe@email.com', '1985-06-15', 'male', 'A+'],
                ['Jane', 'Smith', '+1234567891', 'jane.smith@email.com', '1990-03-22', 'female', 'B+'],
                ['Robert', 'Johnson', '+1234567892', 'robert.j@email.com', '1978-11-08', 'male', 'O+'],
                ['Mary', 'Williams', '+1234567893', 'mary.w@email.com', '1982-09-12', 'female', 'AB+'],
                ['David', 'Brown', '+1234567894', 'david.brown@email.com', '1975-04-30', 'male', 'O-']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, blood_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($patients as $patient) {
                $stmt->execute($patient);
            }
            echo "<p class='status-success'>✅ " . count($patients) . " sample patients inserted</p>";
        } else {
            echo "<p class='status-info'>ℹ️ Patients table already has $count records</p>";
        }
    } catch (Exception $e) {
        echo "<p class='status-failed'>❌ Error inserting patients: " . $e->getMessage() . "</p>";
    }
    
    // Insert sample doctors
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM doctors");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h5>👨‍⚕️ Inserting Sample Doctors...</h5>";
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
            echo "<p class='status-success'>✅ " . count($doctors) . " sample doctors inserted</p>";
        } else {
            echo "<p class='status-info'>ℹ️ Doctors table already has $count records</p>";
        }
    } catch (Exception $e) {
        echo "<p class='status-failed'>❌ Error inserting doctors: " . $e->getMessage() . "</p>";
    }
    
    // Insert sample equipment
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<h5>🔬 Inserting Sample Equipment...</h5>";
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
            echo "<p class='status-success'>✅ " . count($equipment) . " sample equipment inserted</p>";
        } else {
            echo "<p class='status-info'>ℹ️ Equipment table already has $count records</p>";
        }
    } catch (Exception $e) {
        echo "<p class='status-failed'>❌ Error inserting equipment: " . $e->getMessage() . "</p>";
    }
}

function testCrudOperations($pdo) {
    echo "<h5>🧪 Testing CRUD Operations...</h5>";
    
    // Test CREATE operation
    try {
        $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, phone, email) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['Test', 'Patient', '+9999999999', 'test@test.com']);
        $test_id = $pdo->lastInsertId();
        echo "<p class='status-success'>✅ CREATE: Successfully inserted test patient (ID: $test_id)</p>";
        
        // Test READ operation
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$test_id]);
        $patient = $stmt->fetch();
        if ($patient) {
            echo "<p class='status-success'>✅ READ: Successfully retrieved patient data</p>";
        } else {
            echo "<p class='status-failed'>❌ READ: Failed to retrieve patient data</p>";
        }
        
        // Test UPDATE operation
        $stmt = $pdo->prepare("UPDATE patients SET first_name = ? WHERE id = ?");
        $result = $stmt->execute(['Updated Test', $test_id]);
        if ($result && $stmt->rowCount() > 0) {
            echo "<p class='status-success'>✅ UPDATE: Successfully updated patient data</p>";
        } else {
            echo "<p class='status-failed'>❌ UPDATE: Failed to update patient data</p>";
        }
        
        // Test DELETE operation
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $result = $stmt->execute([$test_id]);
        if ($result && $stmt->rowCount() > 0) {
            echo "<p class='status-success'>✅ DELETE: Successfully deleted test patient</p>";
        } else {
            echo "<p class='status-failed'>❌ DELETE: Failed to delete test patient</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='status-failed'>❌ CRUD Test Error: " . $e->getMessage() . "</p>";
    }
}
?>
