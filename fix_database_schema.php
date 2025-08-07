<?php
require_once 'config.php';

echo "<h2>PathLab Pro - Database Schema Fix</h2>\n";
echo "<p>Fixing database schema issues...</p>\n";

try {
    // Fix patients table - add missing columns
    echo "<h3>Fixing Patients Table:</h3>\n";
    
    $alterPatients = [
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS blood_group VARCHAR(5) DEFAULT NULL",
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS medical_history TEXT DEFAULT NULL",
        "ALTER TABLE patients ADD COLUMN IF NOT EXISTS allergies TEXT DEFAULT NULL"
    ];
    
    foreach ($alterPatients as $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color: green;'>✓ " . substr($sql, 0, 50) . "...</span><br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<span style='color: blue;'>- Column already exists</span><br>\n";
            } else {
                echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br>\n";
            }
        }
    }
    
    // Fix equipment table - add missing columns
    echo "<h3>Fixing Equipment Table:</h3>\n";
    
    $alterEquipment = [
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_name VARCHAR(200) NOT NULL DEFAULT ''",
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_type VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS equipment_code VARCHAR(50) NOT NULL DEFAULT ''",
        "ALTER TABLE equipment ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT NULL"
    ];
    
    foreach ($alterEquipment as $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color: green;'>✓ " . substr($sql, 0, 50) . "...</span><br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<span style='color: blue;'>- Column already exists</span><br>\n";
            } else {
                echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br>\n";
            }
        }
    }
    
    // Add unique constraints if they don't exist
    echo "<h3>Adding Unique Constraints:</h3>\n";
    
    $constraints = [
        "ALTER TABLE equipment ADD UNIQUE KEY equipment_code_unique (equipment_code)",
        "ALTER TABLE patients ADD UNIQUE KEY patient_id_unique (patient_id)",
        "ALTER TABLE doctors ADD UNIQUE KEY doctor_id_unique (doctor_id)"
    ];
    
    foreach ($constraints as $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color: green;'>✓ Added unique constraint</span><br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "<span style='color: blue;'>- Constraint already exists</span><br>\n";
            } else {
                echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br>\n";
            }
        }
    }
    
    // Update existing equipment records to have equipment_name if empty
    echo "<h3>Updating Existing Records:</h3>\n";
    
    $updateEquipment = $pdo->prepare("UPDATE equipment SET equipment_name = COALESCE(NULLIF(equipment_name, ''), 'Unknown Equipment') WHERE equipment_name = '' OR equipment_name IS NULL");
    $updateEquipment->execute();
    echo "<span style='color: green;'>✓ Updated " . $updateEquipment->rowCount() . " equipment records</span><br>\n";
    
    $updateEquipmentCode = $pdo->prepare("UPDATE equipment SET equipment_code = CONCAT('EQP', LPAD(id, 6, '0')) WHERE equipment_code = '' OR equipment_code IS NULL");
    $updateEquipmentCode->execute();
    echo "<span style='color: green;'>✓ Updated " . $updateEquipmentCode->rowCount() . " equipment codes</span><br>\n";
    
    // Check table structures
    echo "<h3>Verifying Table Structures:</h3>\n";
    
    $tables = ['patients', 'doctors', 'equipment', 'test_orders'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<strong>$table:</strong> " . count($columns) . " columns (" . implode(', ', array_slice($columns, 0, 5)) . "...)<br>\n";
    }
    
    echo "<hr>\n";
    echo "<h3 style='color: green;'>✅ Database schema fix completed successfully!</h3>\n";
    echo "<p>You can now use the application without database errors.</p>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error fixing database schema:</h3>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
}
?>