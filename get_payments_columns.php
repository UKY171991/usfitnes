<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

// Get the structure of the payments table
try {
    $stmt = $conn->prepare("DESCRIBE payments");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Payments Table Structure:\n";
    foreach ($columns as $column) {
        echo "Field: " . $column['Field'] . ", Type: " . $column['Type'] . ", Null: " . $column['Null'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
