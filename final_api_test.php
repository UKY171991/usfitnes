<?php
// Test the smart API system
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'list';

ob_start();
include 'api/patients_api_smart.php';
$output = ob_get_clean();

echo "Smart API Test Result:\n";
echo $output . "\n";

$response = json_decode($output, true);
if ($response && $response['success']) {
    echo "\n✅ Smart API working correctly!\n";
    echo "Mode: " . (strpos($response['message'], 'fallback') !== false ? 'Fallback Mode' : 'Database Mode') . "\n";
    echo "Patients returned: " . count($response['data']) . "\n";
} else {
    echo "\n❌ Smart API failed\n";
}
?>
