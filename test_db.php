<?php
require_once 'config.php';

try {
    $conn = getDatabaseConnection();
    
    echo "<h2>Database Connection Test</h2>";
    echo "<p><strong>Environment:</strong> " . getEnvironment() . "</p>";
    echo "<p><strong>Database:</strong> " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</p>";
    
    // Check tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Tables:</h3>";
    if (empty($tables)) {
        echo "<p style='color: orange;'>No tables found. Database needs to be set up.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Test API Calls:</h3>";
    echo "<p><a href='api/dashboard_api.php?action=get_counts' target='_blank'>Test Dashboard Counts API</a></p>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
