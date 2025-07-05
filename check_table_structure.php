<?php
require_once 'includes/init.php';

try {
    // Check current patient table structure
    $query = 'DESCRIBE patients';
    $result = $mysqli->query($query);
    
    if ($result) {
        echo "Current patient table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo sprintf("- %s: %s %s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'] == 'NO' ? '(NOT NULL)' : ''
            );
        }
    } else {
        echo "Error describing table: " . $mysqli->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
