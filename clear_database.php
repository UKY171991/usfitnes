<?php
// Clear duplicate data and reset database
require_once 'config.php';

echo "<h1>Database Cleanup Script</h1>";
echo "<hr>";

try {
    // Clear existing sample data to prevent duplicates
    echo "<h2>Clearing existing sample data...</h2>";
    
    // Delete in reverse order due to foreign key constraints
    $tables_to_clear = [
        'test_results',
        'test_order_items', 
        'test_orders',
        'tests',
        'test_categories',
        'patients',
        'doctors',
        'equipment_maintenance',
        'equipment'
    ];
    
    foreach ($tables_to_clear as $table) {
        try {
            // Check if table exists first
            $checkTable = $pdo->prepare("SHOW TABLES LIKE ?");
            $checkTable->execute([$table]);
            
            if ($checkTable->rowCount() > 0) {
                // Get count before deletion
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `$table`");
                $countStmt->execute();
                $count = $countStmt->fetchColumn();
                
                if ($count > 0) {
                    // Clear the table
                    $pdo->exec("DELETE FROM `$table`");
                    
                    // Reset auto increment
                    $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                    
                    echo "✅ Cleared $count records from `$table`<br>";
                } else {
                    echo "ℹ️ Table `$table` is already empty<br>";
                }
            } else {
                echo "⚠️ Table `$table` does not exist<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error clearing table `$table`: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<hr>";
    echo "<h2>Database Reset Complete!</h2>";
    echo "<p>✅ All sample data has been cleared from the database.</p>";
    echo "<p>📝 Now you can:</p>";
    echo "<ul>";
    echo "<li>Refresh your application to let it recreate sample data</li>";
    echo "<li>Or manually add new data through your application interface</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go back to your login page: <a href='login.php'>login.php</a></li>";
    echo "<li>Or check the database setup: <a href='database_setup.php'>database_setup.php</a></li>";
    echo "<li>The database should now work without constraint violations</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Error occurred:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
