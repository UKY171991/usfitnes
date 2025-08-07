<?php
// Simple database test
require_once 'config.php';

echo "<h1>Database Connection Test</h1>";

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "<p>✅ PDO Connection: SUCCESS</p>";
        
        // Test query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "<p>✅ Test Query: SUCCESS (Result: " . $result['test'] . ")</p>";
        
        // Check if patients table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'patients'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p>✅ Patients Table: EXISTS</p>";
            
            // Count patients
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
            $count = $stmt->fetch()['count'];
            echo "<p>📊 Patient Records: $count</p>";
            
            // If no patients, add a test patient
            if ($count == 0) {
                echo "<p>🔧 Adding test patient...</p>";
                $stmt = $pdo->prepare("
                    INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute(['John', 'Doe', '555-0123', 'john.doe@example.com', '1990-01-15', 'Male', 'Active']);
                
                if ($result) {
                    echo "<p>✅ Test patient added successfully</p>";
                } else {
                    echo "<p>❌ Failed to add test patient</p>";
                }
            }
        } else {
            echo "<p>❌ Patients Table: NOT FOUND</p>";
        }
        
    } else {
        echo "<p>❌ PDO Connection: FAILED</p>";
        echo "<p>PDO variable type: " . gettype($pdo ?? null) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='patients.php'>Go to Patients Page</a></p>";
?>
