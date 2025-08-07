<?php
if (file_exists('config_working.php')) {
    require_once 'config_working.php';
} else {
    require_once 'config.php';
}

try {
    $stmt = $pdo->prepare('DESCRIBE test_results');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "test_results table structure:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . ' (' . $column['Type'] . ')' . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
