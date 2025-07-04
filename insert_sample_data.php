<?php
require_once 'config.php';

try {
    echo "Adding sample data...\n\n";
    
    // Add sample patients
    $patients = [
        ['John Doe', '25', 'Male', '123-456-7890', 'john@email.com'],
        ['Jane Smith', '30', 'Female', '123-456-7891', 'jane@email.com'],
        ['Mike Johnson', '35', 'Male', '123-456-7892', 'mike@email.com']
    ];
    
    foreach ($patients as $patient) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO patients (patient_id, full_name, date_of_birth, gender, phone, email) 
            VALUES (?, ?, DATE_SUB(CURDATE(), INTERVAL ? YEAR), ?, ?, ?)
        ");
        $patient_id = 'PAT' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $stmt->execute([$patient_id, $patient['0'], $patient['1'], $patient['2'], $patient['3'], $patient['4']]);
    }
    echo "✓ Sample patients added\n";
    
    // Add sample doctors
    $doctors = [
        ['Dr. Sarah', 'Wilson', 'Pathology', 'LIC001', '555-0001', 'sarah@clinic.com'],
        ['Dr. James', 'Brown', 'Hematology', 'LIC002', '555-0002', 'james@clinic.com'],
        ['Dr. Emily', 'Davis', 'Biochemistry', 'LIC003', '555-0003', 'emily@clinic.com']
    ];
    
    foreach ($doctors as $doctor) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO doctors (doctor_id, first_name, last_name, specialization, license_number, phone, email, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $doctor_id = 'DOC' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        $stmt->execute(array_merge([$doctor_id], $doctor));
    }
    echo "✓ Sample doctors added\n";
    
    // Add test categories
    $categories = [
        ['Blood Tests', 'Blood analysis and related tests'],
        ['Urine Tests', 'Urine analysis and related tests'],
        ['Biochemistry', 'Biochemical analysis tests']
    ];
    
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO test_categories (category_name, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    echo "✓ Test categories added\n";
    
    // Add sample tests
    $tests = [
        ['CBC', 'Complete Blood Count', 1, 'Blood', '25.00', '4.5-11.0 x10^9/L'],
        ['URN', 'Urine Analysis', 2, 'Urine', '15.00', 'Normal'],
        ['LFT', 'Liver Function Test', 3, 'Blood', '45.00', 'Normal ranges'],
        ['BGL', 'Blood Glucose', 3, 'Blood', '20.00', '70-110 mg/dL']
    ];
    
    foreach ($tests as $test) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO tests (test_code, test_name, category_id, sample_type, price, normal_range) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($test);
    }
    echo "✓ Sample tests added\n";
    
    echo "\nSample data insertion complete!\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
