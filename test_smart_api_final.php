<?php
// Simulate a GET request to the smart API
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'list';

// Capture output
ob_start();
include 'api/patients_api_smart.php';
$output = ob_get_clean();

echo "Smart API Response:\n";
echo $output . "\n";

// Parse and validate the JSON response
$response = json_decode($output, true);
if ($response && $response['success']) {
    echo "\n✅ Smart API is working properly!\n";
    echo "Data count: " . (isset($response['data']) ? count($response['data']) : 0) . " patients\n";
} else {
    echo "\n❌ Smart API failed\n";
}
?>
