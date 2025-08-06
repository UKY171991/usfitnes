<?php
// Safe config wrapper that won't die on database errors
function tryDatabaseConnection() {
    $db_available = false;
    $conn = null;
    
    // Database configuration from original config
    $host = 'localhost';
    $dbname = 'u902379465_fitness';
    $username = 'u902379465_fitness';
    $password = '4gS>#RKZV~R';
    
    try {
        // Try to connect using MySQLi
        $conn = @new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            return [false, null];
        }
        $conn->set_charset("utf8mb4");
        
        // Test with a simple query
        $test_query = @mysqli_query($conn, "SELECT 1");
        if ($test_query) {
            $db_available = true;
        }
        
    } catch (Exception $e) {
        $db_available = false;
        $conn = null;
    }
    
    return [$db_available, $conn];
}
?>
