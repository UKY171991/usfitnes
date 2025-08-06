<?php
echo "Testing new persistent API...\n\n";

// Test data for a new patient
$patient_data = [
    'first_name' => 'Test',
    'last_name' => 'Patient',
    'phone' => '+1234567999',
    'email' => 'test.patient@email.com',
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'action' => 'create'
];

// Test creating a patient
echo "1. Testing CREATE patient:\n";
$_POST = $patient_data;
$_SERVER['REQUEST_METHOD'] = 'POST';

ob_start();
include 'api/patients_api_persistent.php';
$response = ob_get_clean();

echo "   Response: " . $response . "\n\n";

// Test listing patients  
echo "2. Testing LIST patients:\n";
$_POST = [];
$_GET = ['action' => 'list'];
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
include 'api/patients_api_persistent.php';
$response = ob_get_clean();

echo "   Response: " . $response . "\n\n";

// Check if data file was created
$data_file = 'data/patients.json';
if (file_exists($data_file)) {
    echo "3. Data file created successfully:\n";
    echo "   File: " . $data_file . "\n";
    echo "   Content: " . file_get_contents($data_file) . "\n";
} else {
    echo "3. ERROR: Data file not created\n";
}
?>
