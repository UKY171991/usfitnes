<?php
require_once 'config.php';

// Simple environment status page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Status - USFitness Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .status-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .environment-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .local { background-color: #28a745; }
        .live { background-color: #dc3545; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #666; }
        .value { color: #333; }
        .server-info { font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <h1>Environment Status</h1>
    
    <div class="status-card">
        <div class="environment-badge <?php echo strtolower($environment); ?>">
            <?php echo $environment; ?> MODE
        </div>
        
        <div class="info-row">
            <span class="label">Environment:</span>
            <span class="value"><?php echo getEnvironment(); ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Is Local:</span>
            <span class="value"><?php echo isLocal() ? 'Yes' : 'No'; ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Database Host:</span>
            <span class="value"><?php echo $host; ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Database Name:</span>
            <span class="value"><?php echo $dbname; ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Database User:</span>
            <span class="value"><?php echo $username; ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Debug Mode:</span>
            <span class="value"><?php echo getConfig('debug_mode') ? 'Enabled' : 'Disabled'; ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Base URL:</span>
            <span class="value"><?php echo getConfig('base_url'); ?></span>
        </div>
    </div>
    
    <div class="status-card">
        <h3>Server Information</h3>
        <div class="server-info">
            <div class="info-row">
                <span class="label">Server Name:</span>
                <span class="value"><?php echo $_SERVER['SERVER_NAME'] ?? 'Not set'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="label">HTTP Host:</span>
                <span class="value"><?php echo $_SERVER['HTTP_HOST'] ?? 'Not set'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="label">Server Address:</span>
                <span class="value"><?php echo $_SERVER['SERVER_ADDR'] ?? 'Not set'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="label">Document Root:</span>
                <span class="value"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="label">Script Path:</span>
                <span class="value"><?php echo __FILE__; ?></span>
            </div>
        </div>
    </div>
    
    <div class="status-card">
        <h3>Database Connection Test</h3>
        <?php
        try {
            // Test database connection
            $test_pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            echo '<div style="color: green;">✓ Database connection successful</div>';
            echo '<div class="server-info">Connected to: ' . $dbname . ' on ' . $host . '</div>';
        } catch (PDOException $e) {
            echo '<div style="color: red;">✗ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <div class="status-card">
        <h3>Environment Detection Logic</h3>
        <div class="server-info">
            <p>The system automatically detects the environment based on:</p>
            <ul>
                <li>Server hostname (localhost detection)</li>
                <li>Server IP addresses (127.0.0.1, ::1)</li>
                <li>Document root paths (xampp, wamp, htdocs)</li>
                <li>Development domains (.local, .dev)</li>
                <li>Database accessibility with root user</li>
            </ul>
        </div>
    </div>
    
    <p><a href="index.php">← Back to Application</a></p>
</body>
</html>
