<?php
/**
 * Test file for patients functionality
 * This file tests the patients API endpoints
 */

// Start session
session_start();

// Set test user session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['user_type'] = 'admin';

// Include database configuration
require_once 'config.php';

echo "<h1>Patients API Test</h1>";

// Test 1: Check if patients table exists and has data
echo "<h2>Test 1: Database Connection and Table Check</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
    $result = $stmt->fetch();
    echo "✅ Patients table exists. Total patients: " . $result['count'] . "<br>";
    
    if ($result['count'] == 0) {
        echo "⚠️ No patients found. Adding test patient...<br>";
        
        // Add a test patient
        $stmt = $pdo->prepare("
            INSERT INTO patients (patient_id, full_name, date_of_birth, gender, phone, email, address, emergency_contact, emergency_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'PAT20240001',
            'John Doe',
            '1990-05-15',
            'Male',
            '555-123-4567',
            'john.doe@example.com',
            '123 Main St, City, State',
            'Jane Doe',
            '555-987-6543'
        ]);
        
        echo "✅ Test patient added successfully<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Test GET request (list patients)
echo "<h2>Test 2: GET Request (List Patients)</h2>";
try {
    $url = 'http://localhost/api/patients_api.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        echo "✅ GET request successful. Found " . count($data['data']) . " patients<br>";
        if (count($data['data']) > 0) {
            $patient = $data['data'][0];
            echo "Sample patient: " . $patient['full_name'] . " (ID: " . $patient['patient_id'] . ")<br>";
        }
    } else {
        echo "❌ GET request failed or returned invalid data<br>";
        echo "Response: " . $response . "<br>";
    }
} catch (Exception $e) {
    echo "❌ GET request error: " . $e->getMessage() . "<br>";
}

// Test 3: Test POST request (add patient)
echo "<h2>Test 3: POST Request (Add Patient)</h2>";
try {
    $testPatient = [
        'full_name' => 'Jane Smith',
        'date_of_birth' => '1985-08-20',
        'gender' => 'Female',
        'phone' => '555-555-1234',
        'email' => 'jane.smith@example.com',
        'address' => '456 Oak Ave, Town, State',
        'emergency_contact' => 'John Smith',
        'emergency_phone' => '555-555-5678'
    ];
    
    $url = 'http://localhost/api/patients_api.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($testPatient)
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ POST request successful. Patient added with ID: " . $data['data']['patient_id'] . "<br>";
    } else {
        echo "❌ POST request failed: " . ($data['message'] ?? 'Unknown error') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ POST request error: " . $e->getMessage() . "<br>";
}

// Test 4: Test DataTables format
echo "<h2>Test 4: DataTables Format</h2>";
try {
    $url = 'http://localhost/api/patients_api.php?draw=1&start=0&length=10';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && isset($data['draw']) && isset($data['recordsTotal'])) {
        echo "✅ DataTables format correct<br>";
        echo "Draw: " . $data['draw'] . "<br>";
        echo "Total records: " . $data['recordsTotal'] . "<br>";
        echo "Filtered records: " . $data['recordsFiltered'] . "<br>";
        echo "Data count: " . count($data['data']) . "<br>";
    } else {
        echo "❌ DataTables format incorrect<br>";
        echo "Response: " . $response . "<br>";
    }
} catch (Exception $e) {
    echo "❌ DataTables test error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests passed, the patients page should be working correctly.</p>";
echo "<p><a href='patients.php'>Go to Patients Page</a></p>";
?> 