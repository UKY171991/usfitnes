<?php
/**
 * PathLab Pro - Secure Database Setup
 * ADMIN ACCESS ONLY - RESTRICTED FILE
 */

// Security check - Only allow admin access
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Access Denied - Database Setup</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
            .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
            h1 { color: #dc3545; margin-bottom: 20px; }
            p { color: #6c757d; margin-bottom: 30px; }
            .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .danger { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #721c24; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">🚫</div>
            <h1>Database Setup - Restricted Access</h1>
            <p>This database setup tool is highly restricted and requires administrator authentication.</p>
            <div class="danger">
                <strong>Security Warning:</strong> Database setup tools contain sensitive operations that can affect system integrity. Access is logged and monitored.
            </div>
            <a href="../login.php" class="btn">Administrator Login Required</a>
        </div>
    </body>
    </html>
    ');
}

require_once '../config.php';

// Additional security: Check if setup is already completed
$setupCompleted = false;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $adminCount = $stmt->fetchColumn();
    if ($adminCount > 0) {
        $setupCompleted = true;
    }
} catch (Exception $e) {
    // Database might not be set up yet
}

// Log the access attempt
logActivity($_SESSION['user_id'], 'Database Setup Access', 'Admin accessed secure database setup tool');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro - Secure Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #007bff; }
        .admin-info { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; border: none; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        h1 { color: #007bff; margin-bottom: 10px; }
        h2 { color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; }
        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .status-card { padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 PathLab Pro - Secure Database Setup</h1>
            <p><strong>Administrator:</strong> <?php echo htmlspecialchars($_SESSION['name'] ?? 'Unknown Admin'); ?></p>
            <p><strong>Access Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="admin-info">
            <strong>🛡️ Security Notice:</strong> This is a restricted administrative tool. All actions are logged and monitored.
        </div>

        <?php if ($setupCompleted): ?>
        <div class="warning">
            <strong>⚠️ Setup Status:</strong> Database appears to be already configured. Proceed with caution when making changes.
        </div>
        <?php endif; ?>

        <h2>Database Operations</h2>
        
        <div class="status-grid">
            <div class="status-card">
                <h3>🔧 Schema Fix</h3>
                <p>Fix missing columns and constraints</p>
                <a href="../fix_database_schema.php" class="btn btn-warning">Run Schema Fix</a>
            </div>
            
            <div class="status-card">
                <h3>⚡ Quick Fix</h3>
                <p>Apply common fixes and updates</p>
                <a href="../quick_fix.php" class="btn btn-success">Run Quick Fix</a>
            </div>
            
            <div class="status-card">
                <h3>📊 System Status</h3>
                <p>Check system health and status</p>
                <a href="../status_check.php" class="btn">Check Status</a>
            </div>
            
            <div class="status-card">
                <h3>🗃️ Backup Database</h3>
                <p>Create database backup</p>
                <button class="btn btn-warning" onclick="createBackup()">Create Backup</button>
            </div>
        </div>

        <h2>Advanced Operations</h2>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> The following operations can affect system integrity. Use with extreme caution.
        </div>

        <div class="status-grid">
            <div class="status-card">
                <h3>🔄 Reset Sample Data</h3>
                <p>Add sample data for testing</p>
                <button class="btn" onclick="resetSampleData()">Add Sample Data</button>
            </div>
            
            <div class="status-card">
                <h3>🧹 Clean Database</h3>
                <p>Remove test data and clean up</p>
                <button class="btn btn-warning" onclick="cleanDatabase()">Clean Database</button>
            </div>
        </div>

        <h2>Security Logs</h2>
        <div id="securityLogs">
            <p>Loading recent access logs...</p>
        </div>

        <div class="error" style="margin-top: 30px;">
            <strong>🚨 Important:</strong> After completing setup, consider moving or removing this file for security.
        </div>
    </div>

    <script>
    function createBackup() {
        if (confirm('Create a database backup? This may take a few minutes.')) {
            // Implementation for backup
            alert('Backup functionality would be implemented here');
        }
    }

    function resetSampleData() {
        if (confirm('Add sample data to the database? This will add test records.')) {
            // Implementation for sample data
            alert('Sample data functionality would be implemented here');
        }
    }

    function cleanDatabase() {
        if (confirm('Clean database? This will remove test data. Are you sure?')) {
            if (confirm('This action cannot be undone. Continue?')) {
                // Implementation for cleaning
                alert('Database cleaning functionality would be implemented here');
            }
        }
    }

    // Load security logs
    fetch('../api/get_security_logs.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('securityLogs').innerHTML = data.logs;
            }
        })
        .catch(error => {
            document.getElementById('securityLogs').innerHTML = '<p>Unable to load security logs.</p>';
        });
    </script>
</body>
</html>