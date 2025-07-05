<?php
// Simple test script to check API connectivity
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'API is accessible',
    'data' => [
        [
            'id' => 1,
            'doctor_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'hospital' => 'Sample Hospital',
            'phone' => '123-456-7890',
            'address' => '123 Main St',
            'referral_percentage' => 10.5,
            'specialization' => 'Cardiology',
            'license_number' => 'MD123456',
            'email' => 'john.doe@example.com',
            'status' => 'active'
        ]
    ]
]);
?>
