<?php
$_GET['action'] = 'list';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Capture output
ob_start();
include 'api/patients_api_smart.php';
$output = ob_get_clean();

echo "Output: " . $output . "\n";
?>
