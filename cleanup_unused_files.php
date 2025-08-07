<?php
/**
 * PathLab Pro - Cleanup Unused Files
 * This script removes unused backup and duplicate files
 */

// Files to be removed (backup and unused files)
$filesToRemove = [
    // Backup files
    'patients_backup.php',
    'patients_clean.php', 
    'patients_modern.php',
    'patients_new.php',
    'doctors_backup.php',
    'doctors_clean.php',
    'doctors_modern.php',
    'test-orders_backup.php',
    'test-orders_modern.php',
    'equipment_backup.php',
    'equipment_clean.php',
    
    // Old AJAX files (replaced with new ones)
    'patients_ajax.php',
    'doctors_ajax.php',
    'equipment_ajax.php',
    'test-orders_ajax.php',
    
    // Duplicate API files
    'api/patients_api_backup.php',
    'api/patients_api_clean.php',
    'api/patients_api_fixed.php',
    'api/patients_api_improved.php',
    'api/patients_api_persistent.php',
    'api/patients_api_simple.php',
    'api/patients_api_smart.php',
    'api/doctors_api_fixed.php',
    'api/doctors_api_improved.php',
    'api/equipment_api_fixed.php',
    'api/equipment_api_improved.php',
    'api/test_orders_api_backup.php',
    'api/test_orders_api_improved.php',
    'api/results_api_fixed.php',
    'api/users_api_fixed.php',
    
    // Debug files
    'api/debug_api.php',
    'test_db_connection.php',
    'check_results_structure.php',
    
    // Setup files (keep database_setup.php)
    'setup_database.php',
    
    // Old template files
    'includes/adminlte_template_header_modern.php'
];

echo "<h2>PathLab Pro - File Cleanup</h2>\n";
echo "<p>Removing unused backup and duplicate files...</p>\n";

$removedCount = 0;
$notFoundCount = 0;

foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span style='color: green;'>✓ Removed: $file</span><br>\n";
            $removedCount++;
        } else {
            echo "<span style='color: red;'>✗ Failed to remove: $file</span><br>\n";
        }
    } else {
        echo "<span style='color: gray;'>- Not found: $file</span><br>\n";
        $notFoundCount++;
    }
}

echo "<hr>\n";
echo "<h3>Summary:</h3>\n";
echo "<p>Files removed: <strong>$removedCount</strong></p>\n";
echo "<p>Files not found: <strong>$notFoundCount</strong></p>\n";
echo "<p>Total files processed: <strong>" . count($filesToRemove) . "</strong></p>\n";

echo "<hr>\n";
echo "<h3>Remaining Core Files:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Main Pages:</strong> patients.php, doctors.php, test-orders.php, equipment.php, dashboard.php</li>\n";
echo "<li><strong>API Files:</strong> api/patients_api.php, api/doctors_api.php, api/test_orders_api.php, api/equipment_api.php</li>\n";
echo "<li><strong>AJAX Files:</strong> ajax/patients_datatable.php, ajax/doctors_datatable.php, ajax/test_orders_datatable.php, ajax/equipment_datatable.php</li>\n";
echo "<li><strong>Config:</strong> config.php, api.txt</li>\n";
echo "</ul>\n";

echo "<p><strong>Cleanup completed successfully!</strong></p>\n";
?>