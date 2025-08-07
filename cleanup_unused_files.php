<?php
/**
 * PathLab Pro - Cleanup Unused Files
 * ADMIN ACCESS ONLY
 */

// Secure admin access check
require_once 'admin/secure_access.php';
$secureAccess = SecureAdminAccess::getInstance();
$secureAccess->checkAdminAccess();

// Log the access attempt
logActivity($_SESSION['user_id'], 'File Cleanup Access', 'Admin accessed file cleanup tool');

// Files to be removed (backup and unused files)
$filesToRemove = [
    // Old CRUD operations file
    'js/crud-operations.js',
    
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro - File Cleanup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #dc3545; }
        .admin-info { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { color: green; }
        .error { color: red; }
        .not-found { color: gray; }
        .btn { background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #c82333; }
        h1 { color: #dc3545; margin-bottom: 10px; }
        h2 { color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; }
        .file-list { max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗑️ PathLab Pro - File Cleanup</h1>
            <p><strong>Administrator:</strong> <?php echo htmlspecialchars($_SESSION['name'] ?? 'Unknown Admin'); ?></p>
            <p><strong>Access Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="admin-info">
            <strong>🛡️ Security Notice:</strong> This tool will remove unused backup and duplicate files to clean up the system.
        </div>

        <?php if (isset($_POST['confirm_cleanup'])): ?>
            <h2>Cleanup Results:</h2>
            <div class="file-list">
                <?php
                $removedCount = 0;
                $notFoundCount = 0;

                foreach ($filesToRemove as $file) {
                    if (file_exists($file)) {
                        if (unlink($file)) {
                            echo "<span class='success'>✓ Removed: $file</span><br>\n";
                            $removedCount++;
                        } else {
                            echo "<span class='error'>✗ Failed to remove: $file</span><br>\n";
                        }
                    } else {
                        echo "<span class='not-found'>- Not found: $file</span><br>\n";
                        $notFoundCount++;
                    }
                }
                ?>
            </div>
            
            <h2>Summary:</h2>
            <p><strong>Files removed:</strong> <?php echo $removedCount; ?></p>
            <p><strong>Files not found:</strong> <?php echo $notFoundCount; ?></p>
            <p><strong>Total files processed:</strong> <?php echo count($filesToRemove); ?></p>
            
            <div class="admin-info">
                <strong>✅ Cleanup completed successfully!</strong> The system has been cleaned of unused files.
            </div>
            
        <?php else: ?>
            <h2>Files to be Removed:</h2>
            <div class="file-list">
                <?php foreach ($filesToRemove as $file): ?>
                    <div>
                        <?php if (file_exists($file)): ?>
                            <span class="error">🗑️ <?php echo $file; ?></span>
                        <?php else: ?>
                            <span class="not-found">❌ <?php echo $file; ?> (not found)</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2>Confirm Cleanup</h2>
            <p><strong>Warning:</strong> This action will permanently delete the listed files. Make sure you have backups if needed.</p>
            
            <form method="POST">
                <input type="hidden" name="confirm_cleanup" value="1">
                <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete these files? This action cannot be undone.')">
                    🗑️ Confirm Cleanup
                </button>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">
                🏠 Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>