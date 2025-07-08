<?php
require_once 'config.php';

echo "<h2>Database Tables Check</h2>\n";

// Check if tables exist
$tables = ['patients', 'tests', 'test_orders', 'doctors'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW CREATE TABLE $table");
        $result = $stmt->fetch();
        echo "<h3>Table: $table</h3>\n";
        echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>\n";
        
        // Count records
        $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_stmt->fetch()['count'];
        echo "<p>Records count: $count</p>\n";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking $table: " . $e->getMessage() . "</p>\n";
    }
}

// Sample data for tests table if it doesn't exist or is empty
$tests_count = $pdo->query("SELECT COUNT(*) as count FROM tests")->fetch()['count'];
if ($tests_count == 0) {
    echo "<h3>Inserting sample test data...</h3>\n";
    try {
        $sample_tests = [
            ['Complete Blood Count (CBC)', 'Blood', 50.00, 'Blood analysis including RBC, WBC, platelets'],
            ['Blood Glucose (Fasting)', 'Blood', 25.00, 'Measures blood sugar levels after fasting'],
            ['Lipid Profile', 'Blood', 75.00, 'Cholesterol and triglyceride levels'],
            ['Liver Function Test', 'Blood', 60.00, 'Assesses liver health and function'],
            ['Kidney Function Test', 'Blood', 55.00, 'Evaluates kidney performance'],
            ['Thyroid Function Test', 'Blood', 80.00, 'Measures thyroid hormone levels'],
            ['Urinalysis', 'Urine', 30.00, 'Complete urine examination'],
            ['X-Ray Chest', 'Radiology', 100.00, 'Chest X-ray imaging'],
            ['ECG', 'Cardiology', 40.00, 'Electrocardiogram'],
            ['Hemoglobin A1C', 'Blood', 45.00, 'Long-term blood sugar control']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO tests (name, type, price, description, status) VALUES (?, ?, ?, ?, 'active')");
        foreach ($sample_tests as $test) {
            $stmt->execute($test);
        }
        echo "<p style='color: green;'>Sample test data inserted successfully!</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error inserting test data: " . $e->getMessage() . "</p>\n";
    }
}
?>
