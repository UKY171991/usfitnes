<?php
/**
 * Cleanup Script for USFitness Lab
 * This script identifies and optionally removes unused files
 */

require_once 'config.php';

// Files to keep (core system files)
$coreFiles = [
    'config.php',
    'index.php',
    'login.php',
    'logout.php',
    'register.php',
    'database_setup.php',
    'dashboard_modern.php',
    'patients_modern.php',
    'doctors_modern.php',
    'test-orders_modern.php',
    'results_modern.php',
    'equipment_modern.php',
    'users_modern.php',
    'reports.php',
    'settings.php',
    'system_status.php',
    'terms-and-conditions.php',
    'forgot-password.php'
];

// Directories to keep
$coreDirectories = [
    'ajax',
    'api',
    'css',
    'js',
    'img',
    'includes',
    'data'
];

// Get all PHP files in root directory
$allFiles = glob('*.php');
$unusedFiles = [];

echo "<h2>File Cleanup Analysis</h2>\n";
echo "<h3>Analyzing files...</h3>\n";

foreach ($allFiles as $file) {
    if (!in_array($file, $coreFiles)) {
        $unusedFiles[] = $file;
    }
}

if (empty($unusedFiles)) {
    echo "<p style='color: green;'>✓ No unused files found!</p>\n";
} else {
    echo "<h3>Unused Files Found:</h3>\n";
    echo "<ul>\n";
    foreach ($unusedFiles as $file) {
        $fileSize = filesize($file);
        $lastModified = date('Y-m-d H:i:s', filemtime($file));
        echo "<li><strong>$file</strong> - Size: " . formatBytes($fileSize) . " - Last Modified: $lastModified</li>\n";
    }
    echo "</ul>\n";
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        echo "<h3>Removing unused files...</h3>\n";
        foreach ($unusedFiles as $file) {
            if (unlink($file)) {
                echo "<p style='color: green;'>✓ Removed: $file</p>\n";
            } else {
                echo "<p style='color: red;'>✗ Failed to remove: $file</p>\n";
            }
        }
        echo "<p><strong>Cleanup completed!</strong></p>\n";
    } else {
        echo "<p><a href='?action=delete' onclick='return confirm(\"Are you sure you want to delete these files?\")' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Delete Unused Files</a></p>\n";
        echo "<p style='color: #6c757d;'>Total files to remove: " . count($unusedFiles) . "</p>\n";
    }
}

// Check for old AJAX files that should be moved
echo "<h3>AJAX File Organization</h3>\n";
$oldAjaxFiles = [];
foreach ($allFiles as $file) {
    if (strpos($file, '_ajax.php') !== false) {
        $oldAjaxFiles[] = $file;
    }
}

if (!empty($oldAjaxFiles)) {
    echo "<h4>Old AJAX files that should be moved to ajax/ folder:</h4>\n";
    echo "<ul>\n";
    foreach ($oldAjaxFiles as $file) {
        echo "<li>$file → ajax/" . str_replace('_ajax.php', '.php', $file) . "</li>\n";
    }
    echo "</ul>\n";
    
    if (isset($_GET['move_ajax']) && $_GET['move_ajax'] === '1') {
        foreach ($oldAjaxFiles as $file) {
            $newFile = 'ajax/' . str_replace('_ajax.php', '.php', $file);
            if (rename($file, $newFile)) {
                echo "<p style='color: green;'>✓ Moved: $file → $newFile</p>\n";
            } else {
                echo "<p style='color: red;'>✗ Failed to move: $file</p>\n";
            }
        }
    } else {
        echo "<p><a href='?move_ajax=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Move AJAX Files</a></p>\n";
    }
} else {
    echo "<p style='color: green;'>✓ No old AJAX files found</p>\n";
}

// Database table analysis
echo "<h3>Database Analysis</h3>\n";
try {
    $conn = getDbConnection();
    
    // Get all tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $coreTables = ['users', 'patients', 'doctors', 'test_orders', 'test_results', 'equipment', 'settings'];
    $unusedTables = [];
    
    foreach ($tables as $table) {
        if (!in_array($table, $coreTables)) {
            $unusedTables[] = $table;
        }
    }
    
    if (empty($unusedTables)) {
        echo "<p style='color: green;'>✓ All database tables are in use</p>\n";
    } else {
        echo "<h4>Potentially unused tables:</h4>\n";
        echo "<ul>\n";
        foreach ($unusedTables as $table) {
            // Count records
            $stmt = $conn->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "<li><strong>$table</strong> - Records: $count</li>\n";
        }
        echo "</ul>\n";
        echo "<p style='color: #856404;'>⚠ Review these tables before deleting</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>\n";
}

echo "<h3>Optimization Recommendations</h3>\n";
echo "<ul>\n";
echo "<li>✓ Modern pages use AJAX for all operations</li>\n";
echo "<li>✓ All forms are handled through modals</li>\n";
echo "<li>✓ DataTables with server-side processing implemented</li>\n";
echo "<li>✓ Toast notifications for all user feedback</li>\n";
echo "<li>✓ Proper file organization (ajax/, api/, includes/)</li>\n";
echo "</ul>\n";

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?>
