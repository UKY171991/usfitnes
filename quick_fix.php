<?php
/**
 * Quick Fix Script for PathLab Pro Issues
 */

require_once 'config.php';

echo "<h2>PathLab Pro - Quick Fix</h2>\n";
echo "<p>Fixing common issues...</p>\n";

try {
    // 1. Fix database schema issues
    echo "<h3>1. Database Schema Fixes:</h3>\n";
    
    // Add missing columns to patients table
    $patientFixes = [
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS blood_group VARCHAR(5) DEFAULT NULL",
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS medical_history TEXT DEFAULT NULL",
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS allergies TEXT DEFAULT NULL"
    ];
    
    foreach ($patientFixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color: green;'>✓ Fixed patients table</span><br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "<span style='color: red;'>✗ " . $e->getMessage() . "</span><br>\n";
            } else {
                echo "<span style='color: blue;'>- Column already exists</span><br>\n";
            }
        }
    }
    
    // Add missing columns to equipment table
    $equipmentFixes = [
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_name VARCHAR(200) NOT NULL DEFAULT 'Unknown Equipment'",
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_code VARCHAR(50) NOT NULL DEFAULT ''",
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_type VARCHAR(100) DEFAULT NULL"
    ];
    
    foreach ($equipmentFixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color: green;'>✓ Fixed equipment table</span><br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "<span style='color: red;'>✗ " . $e->getMessage() . "</span><br>\n";
            } else {
                echo "<span style='color: blue;'>- Column already exists</span><br>\n";
            }
        }
    }
    
    // 2. Update existing records
    echo "<h3>2. Data Fixes:</h3>\n";
    
    // Fix equipment records with missing names
    $updateEquipment = $pdo->prepare("UPDATE equipment SET equipment_name = 'Unknown Equipment' WHERE equipment_name = '' OR equipment_name IS NULL");
    $updateEquipment->execute();
    echo "<span style='color: green;'>✓ Updated " . $updateEquipment->rowCount() . " equipment records</span><br>\n";
    
    // Fix equipment codes
    $updateCodes = $pdo->prepare("UPDATE equipment SET equipment_code = CONCAT('EQP', LPAD(id, 6, '0')) WHERE equipment_code = '' OR equipment_code IS NULL");
    $updateCodes->execute();
    echo "<span style='color: green;'>✓ Updated " . $updateCodes->rowCount() . " equipment codes</span><br>\n";
    
    // 3. Test database connections
    echo "<h3>3. Database Connection Test:</h3>\n";
    
    $tables = ['patients', 'doctors', 'equipment', 'test_orders', 'tests'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "<span style='color: green;'>✓ $table: $count records</span><br>\n";
        } catch (PDOException $e) {
            echo "<span style='color: red;'>✗ $table: " . $e->getMessage() . "</span><br>\n";
        }
    }
    
    // 4. Check API files
    echo "<h3>4. API Files Check:</h3>\n";
    
    $apiFiles = ['patients_api.php', 'doctors_api.php', 'equipment_api.php', 'test_orders_api.php', 'tests_api.php'];
    foreach ($apiFiles as $file) {
        if (file_exists("api/$file")) {
            echo "<span style='color: green;'>✓ api/$file exists</span><br>\n";
        } else {
            echo "<span style='color: red;'>✗ api/$file missing</span><br>\n";
        }
    }
    
    // 5. Check AJAX files
    echo "<h3>5. AJAX Files Check:</h3>\n";
    
    $ajaxFiles = ['patients_datatable.php', 'doctors_datatable.php', 'equipment_datatable.php', 'test_orders_datatable.php'];
    foreach ($ajaxFiles as $file) {
        if (file_exists("ajax/$file")) {
            echo "<span style='color: green;'>✓ ajax/$file exists</span><br>\n";
        } else {
            echo "<span style='color: red;'>✗ ajax/$file missing</span><br>\n";
        }
    }
    
    echo "<hr>\n";
    echo "<h3 style='color: green;'>✅ Quick fix completed!</h3>\n";
    echo "<p><strong>Next steps:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Visit <a href='patients.php'>patients.php</a> to test patient management</li>\n";
    echo "<li>Visit <a href='doctors.php'>doctors.php</a> to test doctor management</li>\n";
    echo "<li>Visit <a href='equipment.php'>equipment.php</a> to test equipment management</li>\n";
    echo "<li>Visit <a href='test-orders.php'>test-orders.php</a> to test order management</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error during fix:</h3>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
}
?>