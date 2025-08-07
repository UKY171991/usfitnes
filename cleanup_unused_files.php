<?php
/**
 * Cleanup Script for PathLab Pro
 * Removes unused files and old backup files
 */

// Files to remove (old/broken/unused files)
$filesToRemove = [
    // Old test files
    'api_test.html',
    'final_api_test.php',
    'final_test.html', 
    'test_config_loading.php',
    'test_connection.php',
    'test_crud_dashboard.html',
    'test_db.php',
    'test_persistent_api.php',
    'test_save_patient.php',
    'test_smart_api.php',
    'test_smart_api_final.php',
    'test_sql_structure.php',
    'test_sql_syntax.php',
    
    // Old config files
    'config_clean.php',
    'config_local.php',
    'config_remote.php',
    'test_config.php',
    
    // Broken/old AdminLTE files
    'convert_to_adminlte.php',
    'patients_adminlte.php',  // Has syntax errors, replaced by patients_ajax.php
    'dashboard_adminlte.php', // Has syntax errors, replaced by dashboard_new.php
    
    // Old debug files
    'check_db.php',
    'check_table.php',
    'debug_db.php',
    'diagnostic.php',
    'fix_showalert.php',
    'view_all_data.php',
    
    // Old migration files (no longer needed)
    'migrate_database.php',
    'migrate_doctors_table.php',
    'migrate_otp.php',
    'setup_patients_db.php',
    'clear_database.php',
    
    // Old sample data files (already imported)
    'create_demo_user.php',
    'insert_sample_data.php'
];

// API backup files to remove
$apiBackupFiles = [
    'api/patients_api_backup.php',
    'api/doctors_api_backup.php', 
    'api/equipment_api_backup.php',
    'api/results_api_backup.php',
    'api/users_api_backup.php'
];

// Old duplicate includes (keep the template system)
$includeFilesToRemove = [
    'includes/adminlte_header.php',  // Replaced by template system
    'includes/adminlte_footer.php'   // Replaced by template system
];

$removedFiles = [];
$errors = [];
$totalFilesRemoved = 0;

// Function to safely remove a file
function removeFile($filePath) {
    global $removedFiles, $errors, $totalFilesRemoved;
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $removedFiles[] = $filePath;
            $totalFilesRemoved++;
            return true;
        } else {
            $errors[] = "Failed to remove: $filePath";
            return false;
        }
    }
    return false; // File doesn't exist
}

echo "<h2>PathLab Pro - File Cleanup Script</h2>\n";
echo "<p>Starting cleanup process...</p>\n";

// Remove main files
echo "<h3>Removing unused main files...</h3>\n";
foreach ($filesToRemove as $file) {
    if (removeFile($file)) {
        echo "<p>✅ Removed: $file</p>\n";
    }
}

// Remove API backup files
echo "<h3>Removing API backup files...</h3>\n";
foreach ($apiBackupFiles as $file) {
    if (removeFile($file)) {
        echo "<p>✅ Removed: $file</p>\n";
    }
}

// Remove old include files
echo "<h3>Removing old include files...</h3>\n";
foreach ($includeFilesToRemove as $file) {
    if (removeFile($file)) {
        echo "<p>✅ Removed: $file</p>\n";
    }
}

// Summary
echo "<hr>\n";
echo "<h3>Cleanup Summary</h3>\n";
echo "<p><strong>Total files removed:</strong> $totalFilesRemoved</p>\n";

if (!empty($errors)) {
    echo "<h4>Errors:</h4>\n";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>❌ $error</p>\n";
    }
}

echo "<h4>Files that should remain:</h4>\n";
$importantFiles = [
    '✅ config.php - Main configuration',
    '✅ config_working.php - Working configuration backup', 
    '✅ patients_ajax.php - Modern patients management',
    '✅ doctors_ajax.php - Modern doctors management',
    '✅ equipment_ajax.php - Modern equipment management',
    '✅ dashboard_new.php - Modern dashboard',
    '✅ includes/adminlte_template.php - Template system',
    '✅ includes/adminlte_template_header.php - Header template',
    '✅ includes/adminlte_template_footer.php - Footer template',
    '✅ includes/adminlte_sidebar.php - Sidebar navigation',
    '✅ api/patients_api.php - Patients API endpoint',
    '✅ api/doctors_api.php - Doctors API endpoint', 
    '✅ api/equipment_api.php - Equipment API endpoint'
];

foreach ($importantFiles as $file) {
    echo "<p>$file</p>\n";
}

echo "<p><strong>Cleanup completed successfully!</strong></p>\n";
echo "<p>The application now uses the modern AJAX-based system with toaster notifications.</p>\n";
?>
