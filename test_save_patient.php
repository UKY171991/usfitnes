<?php
echo "Testing patient save functionality...\n\n";

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

// Convert to POST data format
$post_string = http_build_query($patient_data);

// Create a POST request context
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_string
    ]
]);

echo "1. Testing Smart API (patients_api_smart.php):\n";
echo "   POST data: " . $post_string . "\n";

// Test the smart API
$url = 'http://localhost/api/patients_api_smart.php';
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "   ERROR: Could not connect to smart API\n";
    echo "   Trying direct file execution...\n";
    
    // Try direct execution
    $_POST = $patient_data;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    ob_start();
    include 'api/patients_api_smart.php';
    $response = ob_get_clean();
}

echo "   Response: " . $response . "\n\n";

// Test database connection directly
echo "2. Testing database connection:\n";
require_once 'api/safe_config.php';
list($db_available, $conn) = tryDatabaseConnection();

if ($db_available) {
    echo "   Database connection: AVAILABLE\n";
    
    // Test if patients table exists
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'patients'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "   Patients table: EXISTS\n";
        
        // Check table structure
        $desc = @mysqli_query($conn, "DESCRIBE patients");
        if ($desc) {
            echo "   Table structure:\n";
            while ($row = mysqli_fetch_assoc($desc)) {
                echo "     - {$row['Field']} ({$row['Type']})\n";
            }
        }
    } else {
        echo "   Patients table: MISSING\n";
    }
} else {
    echo "   Database connection: FAILED\n";
    echo "   Operating in fallback mode\n";
}

echo "\n3. Testing form data validation:\n";
foreach ($patient_data as $field => $value) {
    if ($field != 'action') {
        $status = empty($value) ? 'EMPTY' : 'OK';
        echo "   $field: $status ($value)\n";
    }
}
?>
