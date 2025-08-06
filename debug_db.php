<?php
echo "Testing database connection step by step...\n\n";

// Database configuration
$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '4gS>#RKZV~R';

echo "1. Database Configuration:\n";
echo "   Host: $host\n";
echo "   Database: $dbname\n";
echo "   Username: $username\n";
echo "   Password: " . str_repeat('*', strlen($password)) . "\n\n";

echo "2. Testing MySQLi Connection:\n";
$conn = @new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "   ERROR: " . $conn->connect_error . "\n";
    echo "   Error code: " . $conn->connect_errno . "\n\n";
    
    echo "3. Testing connection without database name:\n";
    $conn2 = @new mysqli($host, $username, $password);
    if ($conn2->connect_error) {
        echo "   ERROR: " . $conn2->connect_error . "\n";
        echo "   This suggests credentials or host are wrong\n";
    } else {
        echo "   SUCCESS: Can connect to MySQL server\n";
        echo "   Testing if database exists:\n";
        $result = @mysqli_query($conn2, "SHOW DATABASES LIKE '$dbname'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "   Database '$dbname' exists\n";
        } else {
            echo "   Database '$dbname' does not exist\n";
        }
        mysqli_close($conn2);
    }
} else {
    echo "   SUCCESS: Connected to database\n";
    $conn->set_charset("utf8mb4");
    
    echo "3. Testing table existence:\n";
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'patients'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "   Patients table exists\n";
        
        echo "4. Testing table structure:\n";
        $desc = @mysqli_query($conn, "DESCRIBE patients");
        if ($desc) {
            while ($row = mysqli_fetch_assoc($desc)) {
                echo "   - {$row['Field']} ({$row['Type']})\n";
            }
        }
        
        echo "5. Testing data access:\n";
        $count = @mysqli_query($conn, "SELECT COUNT(*) as count FROM patients");
        if ($count) {
            $row = mysqli_fetch_assoc($count);
            echo "   Total patients: " . $row['count'] . "\n";
        }
    } else {
        echo "   Patients table does not exist\n";
        echo "   Available tables:\n";
        $tables = @mysqli_query($conn, "SHOW TABLES");
        if ($tables) {
            while ($row = mysqli_fetch_array($tables)) {
                echo "   - " . $row[0] . "\n";
            }
        }
    }
    
    mysqli_close($conn);
}
?>
