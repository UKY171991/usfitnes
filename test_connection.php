<?php
require_once 'config.php';

try {
    echo "<h2>Database Connection Test</h2>";
    echo "<p><strong>Environment:</strong> " . getEnvironment() . "</p>";
    
    $conn = getDatabaseConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test patients table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM patients");
    $patientCount = $stmt->fetch()['count'];
    echo "<p>Patients in database: <strong>$patientCount</strong></p>";
    
    // Test doctors table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM doctors");
    $doctorCount = $stmt->fetch()['count'];
    echo "<p>Doctors in database: <strong>$doctorCount</strong></p>";
    
    // Show sample patients
    $stmt = $conn->query("SELECT * FROM patients LIMIT 3");
    $patients = $stmt->fetchAll();
    
    echo "<h3>Sample Patients:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th></tr>";
    foreach ($patients as $patient) {
        echo "<tr>";
        echo "<td>{$patient['id']}</td>";
        echo "<td>{$patient['name']}</td>";
        echo "<td>{$patient['email']}</td>";
        echo "<td>{$patient['phone']}</td>";
        echo "<td>{$patient['gender']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><a href='patients.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Patients Page</a></p>";
    echo "<p><a href='dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
