<?php
/**
 * PathLab Pro - System Status Check
 */

require_once 'config.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>PathLab Pro - Status Check</title></head><body>\n";
echo "<h1>PathLab Pro - System Status Check</h1>\n";

// Check database connection
echo "<h2>Database Connection</h2>\n";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✅ Database connection: OK</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>\n";
}

// Check tables
echo "<h2>Database Tables</h2>\n";
$tables = ['patients', 'doctors', 'equipment', 'test_orders', 'tests', 'test_categories'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "<p style='color: green;'>✅ $table: $count records</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ $table: ERROR - " . $e->getMessage() . "</p>\n";
    }
}

// Check required columns
echo "<h2>Required Columns</h2>\n";
$columnChecks = [
    'patients' => ['patient_id', 'first_name', 'last_name', 'phone', 'blood_group'],
    'doctors' => ['doctor_id', 'name', 'specialization', 'phone'],
    'equipment' => ['equipment_code', 'equipment_name', 'equipment_type'],
    'test_orders' => ['order_number', 'patient_id', 'status']
];

foreach ($columnChecks as $table => $columns) {
    try {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $tableColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($columns as $column) {
            if (in_array($column, $tableColumns)) {
                echo "<p style='color: green;'>✅ $table.$column: EXISTS</p>\n";
            } else {
                echo "<p style='color: red;'>❌ $table.$column: MISSING</p>\n";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking $table: " . $e->getMessage() . "</p>\n";
    }
}

// Check files
echo "<h2>Required Files</h2>\n";
$files = [
    'Main Pages' => ['patients.php', 'doctors.php', 'equipment.php', 'test-orders.php'],
    'API Files' => ['api/patients_api.php', 'api/doctors_api.php', 'api/equipment_api.php', 'api/test_orders_api.php'],
    'AJAX Files' => ['ajax/patients_datatable.php', 'ajax/doctors_datatable.php', 'ajax/equipment_datatable.php']
];

foreach ($files as $category => $fileList) {
    echo "<h3>$category</h3>\n";
    foreach ($fileList as $file) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ $file: EXISTS</p>\n";
        } else {
            echo "<p style='color: red;'>❌ $file: MISSING</p>\n";
        }
    }
}

// Test API endpoints
echo "<h2>API Endpoint Tests</h2>\n";
$apiTests = [
    'patients_api.php' => 'GET',
    'doctors_api.php' => 'GET',
    'equipment_api.php' => 'GET'
];

foreach ($apiTests as $api => $method) {
    $url = "api/$api";
    if (file_exists($url)) {
        echo "<p style='color: green;'>✅ $url: File exists and ready for testing</p>\n";
    } else {
        echo "<p style='color: red;'>❌ $url: File missing</p>\n";
    }
}

echo "<hr>\n";
echo "<h2>Quick Actions</h2>\n";
echo "<p><a href='quick_fix.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Quick Fix</a></p>\n";
echo "<p><a href='patients.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Patients Page</a></p>\n";
echo "<p><a href='doctors.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Doctors Page</a></p>\n";
echo "<p><a href='equipment.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Equipment Page</a></p>\n";

echo "</body></html>\n";
?>