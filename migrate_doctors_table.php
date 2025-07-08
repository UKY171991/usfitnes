<?php
// Database migration script for doctors table
require_once 'config.php';

try {
    echo "Starting database migration for doctors table...\n";
    
    // Check if hospital column exists
    $result = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'hospital'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE doctors ADD COLUMN hospital varchar(200) DEFAULT NULL AFTER address");
        echo "✅ Added hospital column\n";
    } else {
        echo "ℹ️ Hospital column already exists\n";
    }
    
    // Check if referral_percentage column exists
    $result = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'referral_percentage'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE doctors ADD COLUMN referral_percentage decimal(5,2) DEFAULT '0.00' AFTER hospital");
        echo "✅ Added referral_percentage column\n";
    } else {
        echo "ℹ️ Referral_percentage column already exists\n";
    }
    
    // Make license_number nullable if it's not
    $result = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'license_number'");
    $column = $result->fetch();
    if ($column && $column['Null'] === 'NO') {
        $pdo->exec("ALTER TABLE doctors MODIFY COLUMN license_number varchar(50) DEFAULT NULL");
        echo "✅ Made license_number nullable\n";
    } else {
        echo "ℹ️ License_number is already nullable\n";
    }
    
    echo "\n✅ Database migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
?>
