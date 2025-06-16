<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

// Get sample data from payments table
try {
    $stmt = $conn->prepare("SELECT * FROM payments LIMIT 1");
    $stmt->execute();
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Sample Payment Record:\n";
    if ($payment) {
        foreach ($payment as $column => $value) {
            echo $column . ": " . $value . "\n";
        }
    } else {
        echo "No payment records found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
