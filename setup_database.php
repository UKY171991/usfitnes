<?php
require_once 'config.php';

echo "<h2>PathLab Pro Database Setup</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px;'>";

try {
    // Read and execute the SQL schema
    $sql = file_get_contents('database_schema.sql');
    
    if (!$sql) {
        throw new Exception('Could not read database_schema.sql file');
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos(trim($statement), '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "<p style='color: green;'>✓ Executed successfully: " . substr(trim($statement), 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p style='color: #666; margin-left: 20px;'>Statement: " . htmlspecialchars(substr(trim($statement), 0, 100)) . "...</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Summary:</h3>";
    echo "<p><strong>Successful statements:</strong> $successCount</p>";
    echo "<p><strong>Failed statements:</strong> $errorCount</p>";
    
    // Verify tables exist
    echo "<h3>Created Tables:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "<p>• <strong>$table</strong>: $count records</p>";
    }
    
    if ($errorCount == 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-top: 20px;'>";
        echo "<h4>🎉 Database setup completed successfully!</h4>";
        echo "<p>All tables have been created and sample data has been inserted.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
    echo "<h4>❌ Error during setup:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
echo "<br><a href='patients.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Patients Page</a>";
?>
