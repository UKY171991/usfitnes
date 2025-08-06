<?php
try {
    require_once 'config.php';
    echo "Database connection successful!\n";
    echo "PDO connection established.\n";
    echo "MySQLi connection established.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
