<?php
// Simple configuration test page
try {
    require_once 'config.php';
    
    echo "<h1>Configuration Test</h1>";
    echo "<hr>";
    
    echo "<h2>✅ Configuration loaded successfully!</h2>";
    echo "<p>Database connection is working.</p>";
    
    // Test database tables
    echo "<h3>Database Tables:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($tables)) {
        echo "<ul>";
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "<li><strong>$table</strong> - $count records</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tables found.</p>";
    }
    
    echo "<hr>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Status: Ready!</h3>";
    echo "<p>Your configuration is working properly. You can now:</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>Go to Login Page</a></li>";
    echo "<li><a href='dashboard.php'>Access Dashboard</a></li>";
    echo "<li><a href='clear_database.php'>Clear Database (if needed)</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h1>Configuration Error</h1>";
    echo "<hr>";
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Error Details:</h3>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Check your database credentials in config.php</li>";
    echo "<li>Make sure MySQL/MariaDB is running</li>";
    echo "<li>Verify the database exists and is accessible</li>";
    echo "<li>Run <a href='clear_database.php'>clear_database.php</a> to remove duplicate data</li>";
    echo "</ul>";
    echo "</div>";
}
?>
