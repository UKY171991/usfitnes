<?php
/**
 * PathLab Pro - File Cleanup Script
 * Identifies and removes unused, backup, and temporary files
 */

// Files to be removed (backup/temp/unused files)
$files_to_remove = [
    // Backup and test files
    'quick_login.php',
    'quick_fix.php', 
    'check_table_structure.php',
    'check_results_structure.php',
    'test_db_connection.php',
    'config_working.php',
    
    // API backup files
    'api/test_orders_api_backup.php',
    'api/patients_api_simple.php',
    'api/patients_api_smart.php',
    'api/patients_api_persistent.php',
    
    // Duplicate/old files
    'patients_ajax.php',
    'doctors_ajax.php', 
    'equipment_ajax.php',
    'results_ajax.php',
    
    // Status/debug files that are no longer needed
    'system_summary.php'
];

// Files to be renamed/updated (new versions)
$files_to_rename = [
    'patients_new.php' => 'patients.php',
    'doctors_new.php' => 'doctors.php',
    'dashboard_new.php' => 'dashboard.php',
    'js/patients_new.js' => 'js/patients.js',
    'js/doctors_new.js' => 'js/doctors.js', 
    'js/dashboard_new.js' => 'js/dashboard.js',
    'ajax/patients_datatable_new.php' => 'ajax/patients_datatable.php',
    'ajax/doctors_datatable_new.php' => 'ajax/doctors_datatable.php',
    'api/patients_api_improved.php' => 'api/patients_api.php',
    'api/doctors_api_improved.php' => 'api/doctors_api.php'
];

$cleanup_report = [];
$renamed_count = 0;
$removed_count = 0;

echo "<h2>PathLab Pro - File Cleanup Report</h2>\n";
echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>\n";

// First, rename new files to replace old ones
echo "<h3>Step 1: Updating Files</h3>\n";
foreach ($files_to_rename as $old_name => $new_name) {
    if (file_exists($old_name)) {
        // Backup existing file if it exists
        if (file_exists($new_name)) {
            $backup_name = $new_name . '.backup.' . date('YmdHis');
            rename($new_name, $backup_name);
            echo "<p>✓ Backed up existing $new_name to $backup_name</p>\n";
        }
        
        // Rename new file
        if (rename($old_name, $new_name)) {
            echo "<p>✓ Updated: $old_name → $new_name</p>\n";
            $renamed_count++;
        } else {
            echo "<p>✗ Failed to update: $old_name</p>\n";
        }
    } else {
        echo "<p>- File not found: $old_name</p>\n";
    }
}

// Remove unused files
echo "<h3>Step 2: Removing Unused Files</h3>\n";
foreach ($files_to_remove as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<p>✓ Removed: $file</p>\n";
            $removed_count++;
        } else {
            echo "<p>✗ Failed to remove: $file</p>\n";
        }
    } else {
        echo "<p>- File not found: $file</p>\n";
    }
}

// Clean up backup files older than 7 days
echo "<h3>Step 3: Cleaning Old Backup Files</h3>\n";
$backup_pattern = '*.backup.*';
$backup_files = glob($backup_pattern);
$backup_cleaned = 0;

foreach ($backup_files as $backup_file) {
    if (filemtime($backup_file) < strtotime('-7 days')) {
        if (unlink($backup_file)) {
            echo "<p>✓ Removed old backup: $backup_file</p>\n";
            $backup_cleaned++;
        }
    }
}

// Summary
echo "<h3>Cleanup Summary</h3>\n";
echo "<ul>\n";
echo "<li>Files updated: $renamed_count</li>\n";
echo "<li>Files removed: $removed_count</li>\n"; 
echo "<li>Old backups cleaned: $backup_cleaned</li>\n";
echo "</ul>\n";

// Recommendations
echo "<h3>Recommendations</h3>\n";
echo "<ol>\n";
echo "<li>Update all links/references to use the new file names</li>\n";
echo "<li>Test all functionality after cleanup</li>\n";
echo "<li>Consider implementing automated backup strategy</li>\n";
echo "<li>Use version control (Git) for better file management</li>\n";
echo "</ol>\n";

// Check for any remaining issues
echo "<h3>Files That May Need Attention</h3>\n";
$potential_issues = [
    'config.php' => 'Database configuration',
    'includes/layout.php' => 'Main layout template', 
    'css/global.css' => 'Global stylesheet',
    'js/global.js' => 'Global JavaScript functions'
];

foreach ($potential_issues as $file => $description) {
    if (!file_exists($file)) {
        echo "<p>⚠️ Missing important file: $file ($description)</p>\n";
    } else {
        echo "<p>✓ Found: $file</p>\n";
    }
}

echo "<p><strong>Cleanup completed on " . date('Y-m-d H:i:s') . "</strong></p>\n";
?>
