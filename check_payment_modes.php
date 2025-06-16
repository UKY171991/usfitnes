<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

try {
    // Get the payment_mode values from the payments table
    $stmt = $conn->prepare("SELECT id, invoice_no, payment_mode FROM payments LIMIT 5");
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Payment Records with payment_mode values:\n";
    foreach ($payments as $payment) {
        echo "ID: " . $payment['id'] . ", Invoice: " . $payment['invoice_no'] . ", Payment Mode: " . $payment['payment_mode'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
