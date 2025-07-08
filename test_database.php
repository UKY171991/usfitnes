<?php
require_once 'config.php';

echo "Testing database connection...<br>";

try {
    // Check if users table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "Users in database: " . $userCount . "<br>";
    
    // Show all users
    $stmt = $pdo->query("SELECT id, username, name, user_type FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Users:</h3>";
    foreach($users as $user) {
        echo "ID: " . $user['id'] . ", Username: " . $user['username'] . ", Name: " . $user['name'] . ", Type: " . $user['user_type'] . "<br>";
    }
    
    // Check if patients table exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
    $patientCount = $stmt->fetch()['count'];
    echo "<br>Patients in database: " . $patientCount . "<br>";
    
    // Show table structure
    echo "<h3>Patients Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE patients");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . "<br>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
